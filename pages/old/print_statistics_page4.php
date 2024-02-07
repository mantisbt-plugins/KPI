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
	########################################################
require_once( 'core.php' );
require_once( config_get( 'plugin_path' ) . 'KPI' . DIRECTORY_SEPARATOR . 'KPI_api.php' );  
$s_print_statistics_from = 'From';
$s_print_statistics_to = 'To';
$s_print_statistics_update = 'Query';
$s_print_statistics_totals = 'Totals';
$s_summary_statistics_link = 'Submissiosn' ;
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
<form method="post" action="<?php echo plugin_page( 'print_statistics_page4' ); ?>">
<table class="width100" cellpadding="2px">
<tr>
  <td>
    <?php 
    $current_date = explode ("-", date("Y-m-d"));
	$t_icon_path = config_get( 'icon_path' );
	$t_icons = array(
    array( plugin_page('print_statistics_page4_excel'), 'excel', '', 'excelicon.gif', 'Excel 2003' ) );
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
<form method="post" action="<?php echo plugin_page( 'print_statistics_page4.php' );?>">
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
  <td>Submitted</td>
 </tr>
<?PHP
// First select the issues to be measured
$query1 = " select $t_bug_table.id,summary, date_submitted,$t_cat_table.name as category,realname,username from $t_bug_table,$t_user_table,$t_cat_table where  $t_bug_table.handler_id = $t_user_table.id and $t_bug_table.category_id = $t_cat_table.id";
$query1 .= " and substring(date_submitted,1,10) <=".$countdat2 ;
$query1 .= " and substring(date_submitted,1,10) >= ".$countdat1;

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
	$val1= '<a href="view.php?id='.$val1.'">'.$val1.'</a>';
	$val2 = $t_row["realname"] ;
	if (empty($val2)){
		$val2=$t_row["username"];
	}	
	$val3=substr($t_row["summary"],0,40) ;
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
<br />
</form>
<?php
 layout_page_end();