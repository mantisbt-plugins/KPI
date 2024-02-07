<?php
  require_once( 'core.php' );
  require_once( config_get( 'plugin_path' ) . 'KPI' . DIRECTORY_SEPARATOR . 'KPI_api.php' );  
  auth_ensure_user_authenticated();
  access_ensure_project_level( plugin_config_get( 'kpi_threshold' ) );
  $t_cookie_value_id = gpc_get_cookie( config_get( 'view_all_cookie' ), '' );
  $t_cookie_value = filter_db_get_filter( $t_cookie_value_id );
  $f_type_page  = gpc_get_string( 'type_page', 'excel' );
  # excel or html export
  $t_export_title = "Export_submissions_to_excel";
//$t_export_title = ereg_replace( '[\/:*?"<>|]', '', $t_export_title );
    # Make sure that IE can download the attachments under https.
    header( 'Pragma: public' );
    header( 'Content-Type: application/vnd.ms-excel' );
    header( 'Content-Disposition: attachment; filename="' . $t_export_title . '.xls"' );
$month_from = $_REQUEST['month_from'];
$year_from = $_REQUEST['year_from'];
$day_to = $_REQUEST['day_to'];
$month_to = $_REQUEST['month_to'];
$year_to = $_REQUEST['year_to'];
$t_bug_table		= db_get_table( 'mantis_bug_table' );
$t_his_table		= db_get_table( 'mantis_bug_history_table' );
$t_user_table		= db_get_table( 'mantis_user_table' );
$t_cat_table		= db_get_table( 'mantis_category_table' );
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<style id="Classeur1_16681_Styles">
</style>
<div id="Classeur1_16681" align=center x:publishsource="Excel">
<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style='border-collapse:collapse'>
<tr>
  <td>Issue</td>
  <td>Assignee</td>
  <td>Category</td>
  <td>Summary</td>
  <td>Submitted</td>
 </tr>
<?PHP
if (empty($day_from)) {
	$day_from = $current_date[2];
}
if (empty($day_to)) {
	$day_to = $current_date[2];
}
if (empty($month_from)) {
	$month_from = $current_date[1];
}
if (empty($month_to)) {
	$month_to = $current_date[1];
}
if (empty($year_from)) {
	$year_from = $current_date[0];
}
if (empty($year_to)) {
	$year_to = $current_date[0];
}
// Build & execute queries

$day_to ++;
$countdat1 = mktime(0, 0, 0, $month_from, $day_from, $year_from);
$countdat2 = mktime(0, 0, 0, $month_to, $day_to, $year_to);
$t_project_id       = helper_get_current_project( );

// First select the issues to be measured
$query1 = " select $t_bug_table.id,summary, date_submitted,$t_cat_table.name as category,realname,username from $t_bug_table,$t_user_table,$t_cat_table where  $t_bug_table.handler_id = $t_user_table.id and $t_bug_table.category_id = $t_cat_table.id";
$query1 .= " and date_submitted <=".$countdat2 ;
$query1 .= " and  date_submitted >= ".$countdat1;

// make sure we only select the correct project
if ($t_project_id!=0) {
	$query1 .=" and $t_bug_table.project_id =".$t_project_id;
} 


$result1= db_query($query1);
$num_records1 = db_num_rows( $result1 );
for( $i=0; $i < $num_records1; $i++ ) {
	$t_row = db_fetch_array( $result1 );
	// we already have the bug_id filter out those issues already resolved
	$val1=$t_row["id"] ;
	$val2 = $t_row["realname"] ;
	if (empty($val2)){
		$val2=$t_row["username"];
	}	
	$val3=substr($t_row["summary"],0,50) ;
	$val4=$t_row["date_submitted"];
  $val5 = $t_row["category"];
?>

<tr>  
  <td><?php echo $val1;?></td>
  <td><?php echo $val2;?></td>
  <td><?php echo $val5;?></td>
  <td><?php echo $val3;?></td>
  <td><?php echo date("Y-m-d",$val4 );?></td>
</tr>
<?php
}
?>
</table>
</div>