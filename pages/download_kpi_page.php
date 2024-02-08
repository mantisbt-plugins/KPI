<?php
require_once( 'core.php' );
require_once( config_get( 'plugin_path' ) . 'KPI' . DIRECTORY_SEPARATOR . 'KPI_api.php' );  

auth_ensure_user_authenticated();
access_ensure_project_level( plugin_config_get( 'kpi_threshold' ) );
$t_export_title = "Export_kpi_to_excel";
//$t_export_title = ereg_replace( '[\/:*?"<>|]', '', $t_export_title );
# Make sure that IE can download the attachments under https.
header( 'Pragma: public' );
header( 'Content-Type: application/vnd.ms-excel' );
header( 'Content-Disposition: attachment; filename="' . $t_export_title . '.xls"' );
$day_from = $_GET['day_from'];
$month_from = $_GET['month_from'];
$year_from = $_GET['year_from'];
$day_to = $_GET['day_to'];
$month_to = $_GET['month_to'];
$year_to = $_GET['year_to'];
$limit = $_GET['limit'];
$stat1 = $_GET['status1'];
$stat2 = $_GET['status2'];
$uom = $_GET['uom'];
$working = $_GET['working'];
$status_enum_string         = lang_get( 'status_enum_string' );
$status_1 = MantisEnum::getLabel( $status_enum_string, $stat1 ) ;
$status_2 = MantisEnum::getLabel( $status_enum_string, $stat2 ) ;

?>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">
<style id="Classeur1_16681_Styles">
</style>
<div id="Classeur1_16681" align=center x:publishsource="Excel">
<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style='border-collapse:collapse'>
<tr><td><b><?php echo lang_get('selection');?></b></td></tr>
<tr><td>
<?php 
echo lang_get('useworking');?>
</td><td> 
<?php 
if ( $working == 1 ){
	echo lang_get('yes');
} else {
	echo lang_get('no');	
}
?>
</td></tr>
<tr><td><?php echo lang_get('limitdays');?></td><td> <?php echo $limit;?></td></tr>
<tr><td><?php echo lang_get('uom');?></td><td> <?php echo $uom;?></td></tr>
<tr><td><?php echo lang_get('print_statistics_from');?></td><td> <?php echo $day_from . "-". $month_from . "-". $year_from;?></td></tr>
<tr><td><?php echo lang_get('print_statistics_to');?></td><td> <?php echo $day_to . "-". $month_to . "-". $year_to;?></td></tr>
<tr><td><?php echo lang_get('status1');?></td><td> <?php echo $status_1 ?></td></tr>
<tr><td><?php echo lang_get('status2');?></td><td> <?php echo $status_2?></td></tr>
<tr></tr>



<tr>
  <td><?php echo lang_get('val1');?></td>
  <td><?php echo lang_get('val2');?></td>
  <td><?php echo lang_get('val3');?></td>
  <td><?php echo lang_get('val4');?></td>
	<td><?php echo $status_1;?></td>
  <td><b><?php echo lang_get('val6');?></b></td>
	<td><?php echo $status_2;?></td>
  <td><?php echo lang_get('val8');?></td>
  <td><?php echo lang_get('val9');?></td>
  <td><?php echo lang_get('val10');?></td>
  <td><?php echo lang_get('val11');?></td>
  <td><?php echo lang_get('project');?></td>
</tr>
<?php


// Build & execute queries
$day_to ++;
$countdat1 = mktime(0, 0, 0, $month_from, $day_from, $year_from);
$countdat2 = mktime(0, 0, 0, $month_to, $day_to, $year_to);

$t_project_id       = helper_get_current_project( );
// First select the issues to be measured
$query1 = " select bug_id,summary, date_submitted,date_modified,handler_id, project_id, category_id from {bug} b" ;
$query1 .= " left join {bug_history} h ON b.id = h.bug_id where ";
$query1 .= " h.id = ( SELECT MAX({bug_history}.id) FROM {bug_history} WHERE bug_id = b.id and new_value = " . $stat2;
$query1	.= " and field_name = 'status' ";
$query1	.= " and date_modified <= " .$countdat2 ;
$query1 .= " and  date_modified >= ".$countdat1;
$query1 .=	")";

// make sure we only select the correct project
$filter =  false ;
if ( $t_project_id!=0 ) {
	$filter = true;
} 

$result1= db_query($query1);
$num_records1 = db_num_rows( $result1 );

// now scroll through all those issues and create overview like
// bug_id, Summary, date status1,	days 1, date status2,	days 2, date status3,	 days3, OnTime
// days1 is days between status1 and status2
// days2 is days between status2 and status3
// days3 is days between status1 and status3
for( $i=0; $i < $num_records1; $i++ ) {
	$t_row = db_fetch_array( $result1 );
		if ( ( $filter ) and ($t_row['project_id'] <> $t_project_id ) ) {
		continue;
	}
	// we already have the bug_idfilter out those issues already resolved
	$val1=$t_row["bug_id"] ;
	$val2=substr($t_row["summary"],0,50) ;
	$val3=date("Y-m-d",$t_row["date_submitted"]) ;
	$val7=date("Y-m-d",$t_row["date_modified"]);
	$val8= workdays(round((strtotime($val7)-strtotime($val3))/86400), $uom, $working);
	// now retrieve last date of status 2
$query2 = " select date_modified from {bug_history} where bug_id=";
	$query2 .= $val1;
	$query2 .= " and new_value = ".$stat1;
	$query2 .=" AND field_name = 'status'";	
	$query2 .= " order by date_modified DESC ";
	$result2= db_query($query2);
	$num_records2 = db_num_rows( $result2 );
	// in case this status is not found, it will be set to date of assignment or if not possible date of submittal
	If ($num_records2 == 0){
		// now retrieve  date of assigning
		$query3 = " select date_modified from {bug_history} where bug_id=";
		$query3 .= $val1;
		$query3 .= " and new_value = ";
		$query3 .= 50 ;
		$query3 .=" AND field_name = 'status'";	
		$query3 .= " order by date_modified DESC";
		$result3= db_query($query3);
		$num_records3 = db_num_rows( $result3 );
		If ($num_records3 == 0){
			$val5= 	date("Y-m-d",$t_row["date_submitted"] );
		} else{
			$t_row3 = db_fetch_array( $result3 );
			$val5= 	date("Y-m-d",$t_row3["date_modified"] );
		}
	} else{
		$t_row2 = db_fetch_array( $result2 );
		$val5= 	date("Y-m-d",$t_row2["date_modified"] );
	}
	$val4 = workdays(round((strtotime($val5)-strtotime($val3))/86400), $uom, $working);
	$val6 = workdays(round((strtotime($val7)-strtotime($val5))/86400), $uom, $working);
	If ($val6<= $limit){
		$val9 ="Y";
	}else{
		$val9 ="N";
	}
	$val10	= category_get_name( $t_row['category_id'] );
	$pname	= project_get_name( $t_row['project_id'] );
	$val11	= user_get_realname( $t_row['handler_id'] );
?>
<tr>  
  <td><?php echo $val1;?></td>
  <td><?php echo $val2;?></td>
  <td><?php echo $val3;?></td>
  <td><?php echo $val4;?></td>
  <td><?php echo $val5;?></td>
  <td><b><?php echo $val6;?></b></td>
  <td><?php echo $val7;?></td>
  <td><?php echo $val8;?></td>
  <td><?php echo $val9;?></td>
  <td><?php echo $val10;?></td>
  <td><?php echo $val11;?></td>
  <td><?php echo $pname;?></td>
</tr>
<?php
}
?>
</table>
</div>