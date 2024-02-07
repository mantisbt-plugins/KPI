<?php
	########################################################
	# Mantis Bugtracker Add-On
	#
	#  KPI base upon workflow
	#  Measure performance on 2 levels
	#  - How much time is needed to be able to handle the issue (NEW (10) -> CONFIRMED (40)
	#  - How much time is used to finish the issue CONFIRMED(40) -> RESOLVED(80)
	#
	#	These 3 statusses can be configured (once programmed as Plugin)
	#
	#	We only measure those issues that have reached the third status in a given period
	#
	# by Cas Nuy
	# www.NUY.info 2009
	# 
	# modified by JWvanGastel 20090505
	# column Category added in output
	# $val8 introduced to contain the Category information
	# identical modifications made to the file print_statistics_page3_excel.php
	# 
	########################################################
require_once( 'core.php' );
require_once( config_get( 'plugin_path' ) . 'KPI' . DIRECTORY_SEPARATOR . 'KPI_api.php' );  
$s_print_statistics_from = 'From';
$s_print_statistics_to = 'To';
$s_print_statistics_update = 'Query';
$s_print_statistics_totals = 'Totals';
$s_summary_statistics_link = 'Statistics' ;
//  
auth_ensure_user_authenticated();
access_ensure_project_level( plugin_config_get( 'kpi_threshold' ) );

$t_bug_table		= db_get_table( 'bug' );
$t_his_table		= db_get_table( 'bug_history' );
$t_user_table		= db_get_table( 'user' );
$t_cat_table		= db_get_table( 'category' );
layout_page_header( lang_get( 'summary_link' ) );
layout_page_begin();
print_kpi_menu() ;

$t_project_id       = helper_get_current_project();
$current_date = explode ("-", date("Y-m-d"));
if (isset($_REQUEST['day_from'])){
	$day_from =  $_REQUEST["day_from"];
} else {
	$day_from = $current_date[2];
}
if (isset($_REQUEST['month_from'])){
	$month_from = $_REQUEST["month_from"] ;
} else {
	$month_from = $current_date[1];
}
if (isset($_REQUEST['year_from'])){
	$year_from = $_REQUEST["year_from"] ;
} else {
	$year_from = $current_date[0];
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
$t_project_id       = helper_get_current_project( );
?>
<form method="post" action="<?php echo plugin_page( 'print_statistics_page3' ); ?>">
<table class="width100" cellpadding="2px">
<tr>
  <td>
    <?php 
    $current_date = explode ("-", date("Y-m-d"));
	$t_icon_path = config_get( 'icon_path' );
	$t_icons = array(
    array( plugin_page('print_statistics_page3_excel'), 'excel', '', 'excelicon.gif', 'Excel 2003' ) );
	foreach ( $t_icons as $t_icon ) {
		echo '<a href="' . $t_icon[0] . '.php' .
        "?day_from=$day_from" .
        "&amp;month_from=$month_from" .
        "&amp;year_from=$year_from" .
        "&amp;day_to=$day_to" .
        "&amp;month_to=$month_to" .
        "&amp;year_to=$year_to" .
        '" ' . $t_icon[2] . '>' .
        '<img src="' . $t_icon_path . $t_icon[3] . '" border="0" align="absmiddle" alt="' . $t_icon[4] . '"></a> ';
    }
    ?>
   <?php echo lang_get( 'print_statistics_from' ) ?>
    <?php echo lang_get( 'print_statistics_from' ) ?>
     <select tabindex="1" name="day_from"><option value="0">-</option>
      <?php print_day_option_list( $day_from ) ?>
    </select>
    <select tabindex="2" name="month_from"><option value="0">-</option>
      <?php print_month_option_list( $month_from ) ?>
    </select>
    <select tabindex="3" name="year_from"><option value="0">-</option>
      <?php print_year_option_list( $year_from ) ?>
    </select>
    &nbsp;&nbsp;
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

    <input type="submit" name="Send" value="<?php echo lang_get( 'print_statistics_update' ) ?>" />
  </td>
</tr>
</table>
</form>
<br />
<form method="post" action="<?php echo plugin_page( 'print_statistics_page3' ); ?>">
<table class="width100" cellspacing="1" cellpadding="2px">
<?php

// Build & execute queries
$day_to ++;
$countdat1 = mktime(0, 0, 0, $month_from, $day_from, $year_from);
$countdat2 = mktime(0, 0, 0, $month_to, $day_to, $year_to);
?>
<tr>  
  <td>Issue</td>
  <td>Assignee</td>
  <td>Category</td>
  <td>Summary</td>
  <td>Change Date</td>
  <td>Old Status</td>
  <td>Changed by</td>
  <td>New Status</td>
 </tr>
<?PHP
// First select the issues to be measured
$query1 = " select bug_id,summary, date_modified,$t_cat_table.name as category,realname,username,old_value,new_value,$t_his_table.user_id from $t_bug_table,$t_his_table,$t_user_table,$t_cat_table where $t_bug_table.id = $t_his_table.bug_id and $t_bug_table.category_id = $t_cat_table.id and $t_bug_table.handler_id = $t_user_table.id";
$query1 .= " and date_modified <=".$countdat2 ;
$query1 .= " and  date_modified >= ".$countdat1;
$query1 .=" AND field_name = 'status'";
// make sure we only select the correct project
if ($t_project_id!=0) {
	$query1 .=" and $t_bug_table.project_id =".$t_project_id;
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
	// we already have the bug_id filter out those issues already resolved
	$val1=$t_row["bug_id"] ;
	$val1= '<a href="view.php?id='.$val1.'">'.$val1.'</a>';
	$val2 = $t_row["realname"] ;
	if (empty($val2)){
		$val2=$t_row["username"];
	}	
	$val3=substr($t_row["summary"],0,40) ;
	$val4=$t_row["date_modified"];
	$val5 = get_enum_element( 'status', $t_row["old_value"] ) ;
	$val7 = get_enum_element( 'status', $t_row["new_value"] ) ;
  $val8 = $t_row["category"];
	// who did it
	$query2 = " select username,realname from $t_user_table where id=";
	$query2 .= $t_row["user_id"];
	$result2= db_query($query2);
	$t_row2 = db_fetch_array( $result2 );
	$val6 = $t_row2["realname"] ;
	if (empty($val6)){
		$val6=$t_row2["username"];
	}	
?>

<tr>  
  <td><?php echo $val1;?></td>
  <td><?php echo $val2;?></td>
  <td><?php echo $val8;?></td>
  <td><?php echo $val3;?></td>
  <td><?php echo date("Y-m-d",$val4 );?></td>
  <td><?php echo $val5;?></td>
  <td><?php echo $val6;?></td>
  <td><?php echo $val7;?></td>
</tr>
<?php
}
?>
<br />
</form>
<?php
 layout_page_end();