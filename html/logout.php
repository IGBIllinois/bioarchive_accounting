<?php
//////////////////////////////////////////
//					
//	logout.php			
//
//	Logs user out
//
//	By: David Slater
//	Date: May 2009
//
//////////////////////////////////////////

include 'includes/main.inc.php';
$session = new session(SESSION_NAME);
$session->destroy_session();
header("Location: login.php")

?>
