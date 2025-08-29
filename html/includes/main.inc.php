<?php

ini_set('display_errors',1);

$include_paths = array(__DIR__ . '/../../libs');
set_include_path(get_include_path() . ":" . implode(':',$include_paths));

require_once __DIR__ . '/../../conf/app.inc.php';
require_once __DIR__ . '/../../conf/settings.inc.php';
require_once __DIR__ . '/../../vendor/autoload.php';

function my_autoloader($class_name) {
	if(file_exists("../libs/" . $class_name . ".class.inc.php")) {
		require_once $class_name . '.class.inc.php';
	}
}

spl_autoload_register('my_autoloader');

$db = new db(MYSQL_HOST,MYSQL_DATABASE,MYSQL_USER,MYSQL_PASSWORD);
$ldap = new \IGBIllinois\ldap(LDAP_HOST,LDAP_BASE_DN,LDAP_PORT,LDAP_SSL,LDAP_TLS);

$settings = new settings($db);
?>
