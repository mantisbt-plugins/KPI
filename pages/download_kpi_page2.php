<?php
require_once( 'core.php' );
require_once( config_get( 'plugin_path' ) . 'KPI' . DIRECTORY_SEPARATOR . 'KPI_api.php' );  
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
$project_id                 = helper_get_current_project();
$specific_where             = helper_project_specific_where( $project_id ); 

$prjlength = strlen ( $specific_where) ;
$prjlist = substr ( $specific_where, 0, -1);
$prjlength = $prjlength - 17;
$prjlist = substr ( $prjlist, 16,$prjlength);
$list = explode(",", $prjlist);

$content	= '';

$content	.= lang_get('selection');
$content	.= "\r\n";
$content	.= lang_get('useworking');
$content	.= "|" ;

if ( $working == 1 ){
	$content.= lang_get('yes');
} else {
	$content.= lang_get('no');	
}
$content	.= "\r\n";

$content	.= lang_get('limitdays');
$content	.= "|" ;
$content	.= $limit;
$content	.= "\r\n";

$content	.= lang_get('uom');
$content	.= "|" ;
$content	.= $uom;
$content	.= "\r\n";

$content	.= lang_get('print_statistics_from');
$content	.= "|" ;
$content	.= $day_from . "-". $month_from . "-". $year_from;
$content	.= "\r\n";

$content	.= lang_get('print_statistics_to');
$content	.= "|" ;
$content	.= $day_to . "-". $month_to . "-". $year_to;
$content	.= "\r\n";

$content	.= lang_get('status1');
$content	.= "|" ;
$content	.= $status_1;
$content	.= "\r\n";

$content	.= lang_get('status2');
$content	.= "|" ;
$content	.= $status_2;
$content	.= "\r\n";

$content	.= lang_get('projects_checked');
$content	.= "|" ;
for($i = 0, $size = count($list); $i < $size; ++$i) {
	if ($i >0 ) {
		$content .= ", ";
	}
    $content .=  project_get_name(  $list[$i] );
}
$content	.= "\r\n";


$content	.= "\r\n";

$content	.= lang_get('val1');
$content	.= "|" ;
$content	.= lang_get('val2');
$content	.= "|" ;
$content	.= lang_get('val3');
$content	.= "|" ;
$content	.= lang_get('val4');
$content	.= "|" ;
$content	.= $status_1;
$content	.= "|" ;
$content	.= lang_get('val6');
$content	.= "|" ;
$content	.= $status_2;
$content	.= "|" ;
$content	.= lang_get('val8');
$content	.= "|" ;
$content	.= lang_get('val9');
$content	.= "|" ;
$content	.= lang_get('val10');
$content	.= "|" ;
$content	.= lang_get('val11');
$content	.= "|" ;
$content	.= lang_get('project');
$content	.= "\r\n";

// Build & execute queries
$day_to ++;
$countdat1 = mktime(0, 0, 0, $month_from, $day_from, $year_from);
$countdat2 = mktime(0, 0, 0, $month_to, $day_to, $year_to);

$t_project_id       = helper_get_current_project( );
// First select the issues to be measured
$query1 = " select bug_id,summary, date_submitted,date_modified,handler_id, project_id, category_id from {bug} b" ;
$query1 .= " left join {bug_history} h ON b.id = h.bug_id where ";
$query1 .= $specific_where;
$query1 .= " and h.id = ( SELECT MAX({bug_history}.id) FROM {bug_history} WHERE bug_id = b.id and new_value = " . $stat2;
$query1	.= " and field_name = 'status' ";
$query1	.= " and date_modified <= " .$countdat2 ;
$query1 .= " and  date_modified >= ".$countdat1;
$query1 .=	")";

$result1= db_query($query1);
$num_records1 = db_num_rows( $result1 );

// now scroll through all those issues and create overview like
// bug_id, Summary, date status1,	days 1, date status2,	days 2, date status3,	 days3, OnTime
// days1 is days between status1 and status2
// days2 is days between status2 and status3
// days3 is days between status1 and status3
for( $i=0; $i < $num_records1; $i++ ) {
	$t_row = db_fetch_array( $result1 );
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
	
	$content	.= $val1;
	$content	.= "|" ;
	$content	.= $val2;
	$content	.= "|" ;
	$content	.= $val3;
	$content	.= "|" ;
	$content	.= $val4;
	$content	.= "|" ;
	$content	.= $val5;
	$content	.= "|" ;
	$content	.= $val6;
	$content	.= "|" ;
	$content	.= $val7;
	$content	.= "|" ;
	$content	.= $val8;
	$content	.= "|" ;
	$content	.= $val9;
	$content	.= "|" ;
	$content	.= $val1o;
	$content	.= "|" ;
	$content	.= $val11;
	$content	.= "|" ;
	$content	.= $pname;
	$content	.= "\r\n";
}
# Dowload results as CSV
header('Content-type: text/enriched');
header("Content-Disposition: attachment; filename=Export_KPI_to_csv.csv");
echo $content;
exit;
return;
