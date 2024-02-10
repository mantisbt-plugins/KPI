<?php
	########################################################
	# Mantis Bugtracker PluginAdd-On
	#
	#  KPI base upon workflow
	#  Measure performance on 2 levels
	#  - How much time is needed to be able to handle the issue (NEW (10) -> CONFIRMED (40)
	#  - How much time is used to finish the issue CONFIRMED(40) -> RESOLVED(80)
	#
	#	These 2 statusses can be configured 
	#
	#	We only measure those issues that have reached the second status in a given period
	#
	# by Cas Nuy
	# www.NUY.info 2009 - 2024
	# 
	########################################################

require_once( 'core.php' );
require_once( config_get( 'plugin_path' ) . 'KPI' . DIRECTORY_SEPARATOR . 'KPI_api.php' );  
auth_ensure_user_authenticated();
access_ensure_project_level( plugin_config_get( 'kpi_threshold' ) );
$initial	= plugin_config_get( 'initial' );
$status_enum_string         = lang_get( 'status_enum_string' );
$project_id                 = helper_get_current_project();
$specific_where             = helper_project_specific_where( $project_id );

layout_page_header( lang_get( 'summary_link' ) );
layout_page_begin( );

$current_date = explode ("-", date("Y-m-d"));
if (isset($_REQUEST['status1'])){
	$stat1 =  $_REQUEST["status1"];
} else {
	$stat1 = plugin_config_get('status1');
}
if (isset($_REQUEST['status2'])){
	$stat2 =  $_REQUEST["status2"];
} else {
	$stat2 = plugin_config_get('status2');
}
if (isset($_REQUEST['day_from'])){
	$day_from =  $_REQUEST["day_from"];
} else {
	if ( $initial = "D"){
		$day_from = $current_date[2] - 1;
	} else {
		$day_from = $current_date[2];
	}
}
if (isset($_REQUEST['month_from'])){
	$month_from = $_REQUEST["month_from"] ;
} else {
	if ( $initial = "M"){
		$month_from = $current_date[1] - 1;
	} else {
		$month_from = $current_date[1];
	}
}
if (isset($_REQUEST['year_from'])){
	$year_from = $_REQUEST["year_from"] ;
} else {
	if ( $initial = "Y"){
		$year_from = $current_date[0] - 1;
	} else {
		$month_from = $current_date[0];
	}
}
if (isset($_REQUEST['day_to'])){
	$day_to =  $_REQUEST["day_to"];
} else {
	$day_to = $current_date[2];
}
if (isset($_REQUEST['month_to'])){
	$month_to = $_REQUEST["month_to"] ;
} else {
	$month_to = $current_date[1];
}
if (isset($_REQUEST['year_to'])){
	$year_to = $_REQUEST["year_to"] ;
} else {
	$year_to = $current_date[0];
}
if ( isset($_REQUEST['limitdays']) ) {
	$limit = $_REQUEST['limitdays'];
} else {
	$limit = plugin_config_get( 'limitdays' );
}
if ( isset($_REQUEST['uom']) ) {
	$uom = strtoupper( $_REQUEST['uom'] );
} else {
	$uom =plugin_config_get( 'UOM' );
}
if ( isset($_REQUEST['working']) ) {
	$working = $_REQUEST['working'];
} else {
	$working = plugin_config_get( 'working' );
}
$status_1 = MantisEnum::getLabel( $status_enum_string, $stat1 ) ;
$status_2 = MantisEnum::getLabel( $status_enum_string, $stat2 ) ;
$t_project_id       = helper_get_current_project( );
$no = 0;
$yes = 0;
?>
<form method="post" action="<?php echo plugin_page( 'print_kpi_page' );?>">
<div class="table-responsive"> 
<table class="table table-bordered table-condensed table-striped"> 
<tr>
<td>
<a href="plugin.php?page=KPI/download_kpi_page.php&day_from=<?php echo $day_from; ?>&month_from=<?php echo $month_from;?>&year_from=<?php echo $year_from;?>&day_to=<?php echo $day_to; ?>&month_to=<?php echo $month_to;?>&year_to=<?php echo $year_to;?>&limit=<?php echo $limit; ?>&status1=<?php echo $stat1 ?>&status2=<?php echo $stat2 ?>&uom=<?php echo $uom ?>&working=<?php echo $working ?>"><img src="<?php echo plugin_file( 'excelicon.gif' ); ?>" width='20' height='20'  ></a>
<div>
</td>
<td>
<?php echo lang_get( 'uom' ) ?>
<label><input type="text" name="uom" size="1" value="<?php echo $uom ?>"/> </label>
</td>
	<td><?php echo lang_get( 'status1' ) ?>
		<select name="status1">
			<?php print_enum_string_option_list( 'status', $stat1 ) ?>
		</select>
	</td>
	<td><?php echo lang_get( 'status2' ) ?>
		<select name="status2">
			<?php print_enum_string_option_list( 'status', $stat2 ) ?>
		</select>
	</td>
<td>
<?php echo lang_get( 'useworking' ) ?>
		
			<label><input type="radio" name='working' value="1" <?php echo( ON == $working ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'yes' )?></label>
			<label><input type="radio" name='working' value="0" <?php echo( OFF == $working )? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'no' )?></label>
			
</td>	
	

<div>
</div>
</tr>
<tr>
<td>
<input type="submit" name="Send" value="<?php echo lang_get( 'print_statistics_update' ) ?>" />
</td>
  <td><?php echo lang_get( 'print_statistics_from' ) ?>
     <select tabindex="1" name="day_from"><option value="0">-</option>
      <?php print_day_option_list( $day_from ) ?>
    </select>
    <select tabindex="2" name="month_from"><option value="0">-</option>
      <?php print_month_option_list( $month_from ) ?>
    </select>
    <select tabindex="3" name="year_from"><option value="0">-</option>
      <?php print_year_option_list( $year_from) ?>
    </select>
	</td><td>

    <?php echo lang_get( 'print_statistics_to' ) ?>
    <select tabindex="4" name="day_to"><option value="0">-</option>
      <?php print_day_option_list( $day_to ) ?>
    </select>
    <select tabindex="5" name="month_to"><option value="0">-</option>
      <?php print_month_option_list( $month_to ) ?>
    </select>
    <select tabindex="6" name="year_to"><option value="0">-</option>
      <?php print_year_option_list( $year_to ) ?>
    </select>
</td><td>
<?php echo lang_get( 'limitdays' ) ?>
<label><input type="text" name="limitdays" size="5" value="<?php echo $limit ?>"/> </label>
</td><td>
<input type="submit" name="Send" value="<?php echo lang_get( 'print_statistics_update' ) ?>" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<a href="plugin.php?page=KPI/download_kpi_page2.php&day_from=<?php echo $day_from; ?>&month_from=<?php echo $month_from;?>&year_from=<?php echo $year_from;?>&day_to=<?php echo $day_to; ?>&month_to=<?php echo $month_to;?>&year_to=<?php echo $year_to;?>&limit=<?php echo $limit; ?>&status1=<?php echo $stat1 ?>&status2=<?php echo $stat2 ?>&uom=<?php echo $uom ?>&working=<?php echo $working ?>"><img src="<?php echo plugin_file( 'csv.svg' ); ?>" width='25' height='25'  ></a>

</td>
</tr>
</table>
</form>
</div>
<form method="post" action="<?php echo plugin_page( 'print_kpi_page' );?>">
<div class="table-responsive"> 
<table class="table table-bordered table-condensed table-striped"> 
<?php
// Build & execute queries
$day_to ++;
$countdat1 = mktime(0, 0, 0, $month_from, $day_from, $year_from);
$countdat2 = mktime(0, 0, 0, $month_to, $day_to, $year_to);
?>
<tr>  
<td><?php echo lang_get('val1') ;?></td>
<td><?php echo lang_get('val2');?></td>
<td><?php echo lang_get('val3');?></td>
<td><?php echo lang_get('val4');?></td>
<td><?php echo $status_1;?></td>
<td><b><?php echo lang_get('val6');?></b></td>
<td><?php echo $status_2;?></td>
<td><?php echo lang_get('val8');?></td>
<td><?php echo lang_get('val9');?></td>
<td><?php echo lang_get('val10');?></td>
<td><?php echo lang_get('val11') ;?></td>
<td><?php echo lang_get('project');?></td>
</tr>
<?PHP
$query1 = " select bug_id,summary, date_submitted,date_modified,handler_id, project_id, category_id from {bug} b" ;
$query1 .= " left join {bug_history} h ON b.id = h.bug_id where ";
$query1 .= $specific_where ;
$query1 .= " and h.id = ( SELECT MAX({bug_history}.id) FROM {bug_history} WHERE bug_id = b.id and new_value = " . $stat2;
$query1	.= " and field_name = 'status' ";
$query1	.= " and date_modified <= " .$countdat2 ;
$query1 .= " and date_modified >= ".$countdat1;
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
	// we already have the bug_id, filter out those issues already resolved
	$val1=$t_row["bug_id"] ;
	$val2=substr($t_row["summary"],0,50) ;
	$val3=date("Y-m-d",$t_row["date_submitted"]) ;
	$val7=date("Y-m-d",$t_row["date_modified"]);
	$val8= workdays(round((strtotime($val7)-strtotime($val3))/86400), $uom, $working);
	// now retrieve last date of status 2
	$query2  = " select date_modified from {bug_history} where bug_id=";
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
	} else {
		$t_row2 = db_fetch_array( $result2 );
		$val5= 	date("Y-m-d",$t_row2["date_modified"] );
	}
	$val4 = workdays(round((strtotime($val5)-strtotime($val3))/86400), $uom, $working);
	$val6 = workdays(round((strtotime($val7)-strtotime($val5))/86400), $uom, $working);
	if ( $val6 > $limit ){
		$val9 = "N";
		$no ++;
	}else{
		$val9 = "Y";
		$yes ++;
	}
	$val10	= category_get_name( $t_row['category_id'] );
	$pname	= project_get_name( $t_row['project_id'] );
	$val11	= user_get_realname( $t_row['handler_id'] );
	?>
	<tr>  
	<td><?php echo $val1;?></td>

	<td><?php echo $val2;?></td>
	<td align="center"><?php echo $val3;?></td>
	<td align="center"><?php echo $val4;?></td>
	<td align="center"><?php echo $val5;?></td>
	<td align="center"><b><?php echo $val6;?></b></td>
	<td align="center"><?php echo $val7;?></td>
	<td align="center"><?php echo $val8;?></td>
	<td align="center"><?php echo $val9;?></td>
	<td align="center"><?php echo $val10;?></td>
	<td align="center"><?php echo $val11;?></td>
	<td><?php echo $pname;?></td>
	</tr>
	<?php
}

$total = $no + $yes;
if ( $total > 0 ) {
	$okprc = $yes/$total*100;
} else {
	$okprc = 0;
}
?>
<tr><td><b>Score :</b></td><td><b><?php echo round($okprc,2) ;?>&nbsp;% </b></td></tr>
</form>
</table>
</div>
<?php
layout_page_end();