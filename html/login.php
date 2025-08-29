<?php
///////////////////////////////////
//
//	login.php
//
//
//	David Slater
//	May 2009
//
///////////////////////////////////

include_once 'includes/main.inc.php';

$session = new session(SESSION_NAME);
$message = "";
$webpage = $dir = dirname($_SERVER['PHP_SELF']) . "/index.php";
if ($session->get_var('webpage') != "") {
	$webpage = $session->get_var('webpage');
}

if (isset($_POST['login'])) {

	$username = trim(rtrim($_POST['username']));
	$password = $_POST['password'];

	$error = false;
	if ($username == "") {
		$error = true;
		$message .= html::error_message("Please enter your username.");
	}
	if ($password == "") {
		$error = true;
		$message .= html::error_message("Please enter your password.");
	}
	if ($error == false) {
		$ldap = new \IGBIllinois\ldap(LDAP_HOST,LDAP_BASE_DN,LDAP_PORT,LDAP_SSL,LDAP_TLS);
		$login_user = new user($db,$ldap,0,$username);
		$success = $login_user->authenticate($password);
		if ($success) {
			$session_vars = array('login'=>true,
                        'username'=>$username,
                        'timeout'=>time(),
                        'ipaddress'=>$_SERVER['REMOTE_ADDR']
                	);
	                $session->set_session($session_vars);


        	        $location = "http://" . $_SERVER['SERVER_NAME'] . $webpage;
                	header("Location: " . $location);

		}
		else {
			$message .= html::error_message("Invalid username or password. Please try again.");
		}
	}
}



?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo TITLE; ?></title>
		<link rel="stylesheet" href="includes/bootstrap/css/bootstrap.min.css" type="text/css">
		<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	</head>
	<body OnLoad="document.login.username.focus();">
		<nav class="navbar navbar-inverse navbar-static-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<div class="navbar-brand">
						<?php echo TITLE; ?>
					</div>
				</div>
			</div>
		</nav>
		
		<div class="container-fluid">

			<div class='row'>
				<div class='col-md-4 col-md-offset-4'>
					<form action='login.php' method='post' name='login'>
						<div class="form-group">
							<label for="username">Username: </label>
							<div class="input-group">
								<input class='form-control' type='text' name='username' id="username" tabindex='1' placeholder='Username' value='<?php if (isset($username)) { echo $username; } ?>'> 
								<span class="input-group-addon"><span class='glyphicon glyphicon-user'></span></span>
							</div>
						</div>
						<div class="form-group">
							<label>Password: </label>
							<div class="input-group">
								<input class='form-control' type='password' name='password' placeholder='Password' tabindex='2'>
								<span class="input-group-addon"><span class='glyphicon glyphicon-lock'></span></span>
							</div>
						</div>
						<button type='submit' name='login' class='btn btn-primary'>Login</button>
					</form>
	
					<br>	
					<?php if (isset($message)) { 
						echo $message;
						} ?>
				</div>
			</div>
			<footer class='page-footer mt-auto'>
			<hr>
			<div class='container-fluid'>
				<p class='text-center'>
				<span class='text-muted'>	
				<br><em>Computer & Network Resource Group - Carl R. Woese Institute for Genomic Biology</em>
				<br><em>If you have any questions, please email us at <a href='mailto:<?php echo ADMIN_EMAIL; ?>'><?php echo ADMIN_EMAIL; ?></a></em>
				<br><em><a target='_blank' href='https://www.igb.illinois.edu'>Carl R. Woese Institute for Genomic Biology Home Page</a></em>
				<br><em><a target='_blank' href='https://www.vpaa.uillinois.edu/resources/web_privacy'>University of Illinois System Web Privacy Notice</a> </em>
				<em>&copy; 2015-<?php echo date('Y'); ?>  University of Illinois Board of Trustees</em>
				</span>
			</div>
			</footer>
		</div>
	
