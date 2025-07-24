<?php
include_once 'includes/main.inc.php';
include_once 'includes/session.inc.php';

if(isset($_POST['create_data_report'])){
	$month = $_POST['month'];
	$year = $_POST['year'];
	$type = $_POST['report_type'];
	$data = data_functions::get_data_bill($db,$month,$year);
	$filename = "activearchive-".$month."-".$year.".".$type;
}
else {
	exit;
}
switch($type){
	case 'csv':
		report::create_csv_report($data,$filename);
		break;
	case 'xlsx':
		report::create_xlsx_report($data,$filename);
		break;
}
?>
