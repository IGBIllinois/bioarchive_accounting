<?php
	require_once 'includes/header.inc.php';
	
	if (!$login_user->is_admin()) {
		exit;
	}
	if ( isset($_GET['start_date']) && isset($_GET['end_date']) ) {
		$start_date = $_GET['start_date'];
		$end_date = $_GET['end_date'];
	} else {
		$start_date = date('Ym') . "01";
		$end_date = date('Ymd',strtotime('-1 second',strtotime('+1 month',strtotime($start_date))));
	}
	
	$month_name = date('F',strtotime($start_date));
	$month = date('m',strtotime($start_date));
	$year = date('Y',strtotime($start_date));
	$url_navigation = html::get_url_navigation($_SERVER['PHP_SELF'],$start_date,$end_date);
	
	$data_bill = data_functions::get_data_bill($db,$month,$year);
	$data_html = "";
	foreach($data_bill as $value) {
// 		if ($value['Billed Cost'] > 0) {
			$data_html .= "<tr>";
			$data_html .= "<td>".$value['Username']."</td>";
			$data_html .= "<td>".ARCHIVE_DIR.$value['Directory']."</td>";
			$data_html .= "<td>".number_format($value['Usage'],4)." TB</td>";
			$data_html .= "<td>".number_format($value['Previous_Usage'],4)." TB</td>";
			$data_html .= "<td>$".number_format($value['Cost'],2)."</td>";
			$data_html .= "<td>$".number_format($value['Billed_Cost'],2)."</td>";
			$data_html .= "<td>".$value['CFOP']."</td>";
			$data_html .= "<td>".$value['Activity_Code']."</td>";
			$data_html .= "</tr>";
// 		}
	}
	$statistics = new statistics($db);	
?>
<h3>Data Billing Monthly Report - <?php echo $month_name." ".$year; ?></h3>
<ul class='pager'>
        <li class='previous'><a href='<?php echo $url_navigation['back_url']; ?>'>Previous Month</a></li>
        
        <?php   
                $next_month = strtotime('+1 day', strtotime($end_date));
                $today = mktime(0,0,0,date('m'),date('d'),date('y'));
                if ($next_month > $today) {
                        echo "<li class='next disabled'><a href='#'>Next Month</a></li>";
                }
                else {
                        echo "<li class='next'><a href='" . $url_navigation['forward_url'] . "'>Next Month</a></li>";
                }
        ?>
</ul>

<table class='table table-striped table-condensed table-bordered'>
    <thead>
        <tr>
	        <th>Username</th>
            <th>Directory</th>
            <th>Usage</th>
            <th>Previous Usage</th>
            <th>Cost</th>
            <th>Billed Cost</th>
            <th>CFOP</th>
            <th>Activity Code</th>
        </tr>
    </thead>
    <?php echo $data_html; ?>
    <tr>
        <td colspan="4">Total Cost:</td>
        <td>$<?php echo $statistics->get_total_cost($start_date,$end_date,1); ?></td>
        <td>$<?php echo $statistics->get_total_billed_cost($start_date,$end_date,1); ?></td>
        <td colspan="2"></td>
	</tr>
</table>
<form class="form-inline" method="post" action="report.php">
	<input type="hidden" name="month" value="<?php echo $month; ?>"/>
	<input type="hidden" name="year" value="<?php echo $year; ?>"/>
	<select name="report_type" class="form-control">
		<option value="xlsx">Excel 2007</option>
		<option value="csv">CSV</option>
	</select>
	<input class="btn btn-primary" type="submit" name="create_data_report" value="Download Usage Report"/>
</form>

<?php
	require_once 'includes/footer.inc.php';
?>
