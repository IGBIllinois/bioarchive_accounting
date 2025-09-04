#!/usr/bin/env php
<?php
ini_set("display_errors",1);
chdir(dirname(__FILE__));
set_include_path(get_include_path() . ':../libs');
function __autoload($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

require_once '../conf/settings.inc.php';

$log = new \IGBIllinois\log(ENABLE_LOG,LOG_FILE);

$sapi_type = php_sapi_name();
// If run from command line
if ($sapi_type != 'cli') {
	echo "Error: This script can only be run from the command line.\n";
}
else {
	// Connect to database
	$db = new \IGBIllinois\db(MYSQL_HOST,MYSQL_DATABASE,MYSQL_USER,MYSQL_PASSWORD);
	$ldap = new \IGBIllinois\ldap(LDAP_HOST,LDAP_BASE_DN,LDAP_PORT,LDAP_SSL,LDAP_TLS);
	$settings = new settings($db);

	// Get archive directories from database
	$rows = $db->query("select directory,directories.id from directories left join users on users.id=directories.user_id where directories.is_enabled=1 and users.is_enabled=1 and directory is not null and directory!=''");
	$data_usage = new data_usage($db);
	$arch_file = new archive_file($db);
	$dir = new archive_directory($db);
	$prevmonth = date('n')-1;
	$prevyear = date('Y');
	if($prevmonth==0){
		$prevmonth = 12;
		$prevyear-=1;
	}
	$email_users = array();
	foreach ( $rows as $key=>$row ){
	    if(USE_BUCKETS){
	        echo sprintf("Bucket '%s'...", $row['directory']);
            // Gather usage info
            // Total Usage in MB
            $usage = exec("./get_bucket_size.py --bucket=".$row['directory']);
            preg_match("/^[^\t]*\\t(.*)/u", $usage, $matches);
            $usage = $matches[1]/1024;
            echo $usage . ' MB... ';

            $numsmallfiles = 0;

            // Store usage data in database
            $data_usage->create($row['id'], $usage, $numsmallfiles);

            // Set previous month's usage to not pending
            $latestUsage = data_usage::usage_from_month($db, $row['id'], $prevmonth, $prevyear);
            if ($latestUsage->get_pending() == 1) {
                $latestUsage->set_pending(0);
                $dir->load_by_id($latestUsage->get_directory_id());
                if ($latestUsage->get_cost() > 0 && !in_array($dir->get_user_id(), $email_users)) {
                    array_push($email_users, $dir->get_user_id());
                }
            }

            echo "Done.\n";
            $log->send_log("Scanned bucket " . $row['directory'] . ': ' . $usage . ' MB.');
        } else {
            if (file_exists(ARCHIVE_DIR . $row['directory'])) {
                echo ARCHIVE_DIR . $row['directory'] . "... ";
                // Gather usage info
                // Total Usage in MB
                $usage = exec("du -sm " . ARCHIVE_DIR . $row['directory']);
                preg_match("/^(.*)\\t/u", $usage, $matches);
                $usage = $matches[1];
                echo $usage . ' MB... ';

                // # of small files
                unset($allfiles);
                exec(
                    "find " . ARCHIVE_DIR . $row['directory'] . " -type f -size -" . $settings->get_setting(
                        'small_file_size'
                    ) . "k | wc -l",
                    $allfiles
                );
                $numsmallfiles = trim($allfiles[0]);
                echo $numsmallfiles . ' small files... ';
                // Store usage data in database
                $data_usage->create($row['id'], $usage, $numsmallfiles);

                // Set previous month's usage to not pending
                $latestUsage = data_usage::usage_from_month($db, $row['id'], $prevmonth, $prevyear);
                if ($latestUsage->get_pending() == 1) {
                    $latestUsage->set_pending(0);
                    $dir->load_by_id($latestUsage->get_directory_id());
                    if ($latestUsage->get_cost() > 0 && !in_array($dir->get_user_id(), $email_users)) {
                        array_push($email_users, $dir->get_user_id());
                    }
                }

                echo "Done.\n";
                $log->send_log("Scanned " . ARCHIVE_DIR . $row['directory'] . ': ' . $usage . ' MB.');
            } else {
                $log->send_log("Directory " . ARCHIVE_DIR . $row['directory'] . ' does not exist.');
            }
        }
	}
}
