<?php

ini_set('display_errors',1);
set_include_path(get_include_path().":../libs:includes/PHPExcel-1.8.2/Classes");
include_once('../conf/settings.inc.php');
function my_autoloader($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

spl_autoload_register('my_autoloader');

$db = new db(MYSQL_HOST,MYSQL_DATABASE,MYSQL_USER,MYSQL_PASSWORD);
$ldap = new ldap(LDAP_HOST,LDAP_SSL,LDAP_PORT,LDAP_BASE_DN);
$settings = new settings($db);
?>
