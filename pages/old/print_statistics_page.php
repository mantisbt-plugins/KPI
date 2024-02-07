<?php
	########################################################
	# Mantis Bugtracker Add-On
	#
	#                    Statistics
	#
	# by cas Nuy
	# 
	########################################################

require_once( 'core.php' );
$t_core_path = config_get( 'core_path' );
require_once( $t_core_path.'current_user_api.php' );
require_once( $t_core_path.'bug_api.php' );
require_once( $t_core_path.'date_api.php' );
require_once( $t_core_path.'icon_api.php' );
require_once( $t_core_path.'string_api.php' );
require_once( $t_core_path.'columns_api.php' );
require_once( config_get( 'plugin_path' ) . 'KPI' . DIRECTORY_SEPARATOR . 'KPI_api.php' );  

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
auth_ensure_user_authenticated();
access_ensure_project_level( plugin_config_get( 'kpi_threshold' ) );

$t_bug_table		= db_get_table( 'bug' );
$t_his_table		= db_get_table( 'bug_history' );
$t_project_table	= db_get_table( 'project' );
$t_hierarchy_table	= db_get_table( 'project_hierarchy' );

layout_page_header( lang_get( 'summary_link' ) );
layout_page_begin();
print_kpi_menu() ;

$t_project_id       = helper_get_current_project();

?>
<br>
<br>
<form method="post" action="<?php echo plugin_page( 'print_statistics_page' );?>">
<table class="width100" cellpadding="2px">

<tr>
  <td>

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

    <input  tabindex="7" type="submit" name="Send" value="<?php echo lang_get( 'print_statistics_update' ) ?>" />
  </td>
</tr>
</table>
</form>
<br>

<table class="width100" cellspacing="1" cellpadding="2px">
<?php
// Build & execute queries
$day_to ++;
$countdat1 = mktime(0, 0, 0, $month_from, $day_from, $year_from);
$countdat2 = mktime(0, 0, 0, $month_to, $day_to, $year_to);

// does the selected project have childs
$kiddies=0;
if ($t_project_id!=0) {
	$stat0_query  = "SELECT * FROM  $t_hierarchy_table  ";
	$stat0_query .= " WHERE parent_id =".$t_project_id ;
	$result_records = db_query($stat0_query);
	$kiddies = db_num_rows( $result_records );
}

$stat1_query  = "SELECT bug_id, date_modified, new_value,type FROM $t_his_table a, $t_bug_table b ";
if ($t_project_id!=0) {
	if ($kiddies!=0) {
		$stat1_query  .= ", $t_hierarchy_table c ";
	}
}
$stat1_query .= "WHERE a.bug_id = b.id and (field_name = 'status' or type=1) " ;
if ( !empty($day_to) &&  !empty($month_to)  && !empty($year_to)) {
	$stat1_query .= " AND a.date_modified  <=".$countdat2;
	$stat1_query .= " and b.date_submitted >=".$countdat1 ;
	$stat1_query .= " AND b.date_submitted <=".$countdat2;
}
if ($t_project_id!=0) {
	if ($kiddies!=0) {
		$stat1_query .=" and c.child_id=b.project_id and parent_id=".$t_project_id;
	} else{
		$stat1_query .=" and b.project_id =".$t_project_id;
	}
} 
$stat1_query .= " ORDER BY bug_id, a.date_modified desc, type  ";


$stat2_query  = "SELECT * FROM  $t_bug_table  ";
if ($t_project_id!=0) {
	if ($kiddies!=0) {
		$stat2_query  .= ", $t_hierarchy_table  ";
	}
}
if (!empty($day_from) && !empty($day_to) && !empty($month_from) && !empty($month_to) && !empty($year_from) && !empty($year_to)) {
	$stat2_query .= " WHERE " ;
	$stat2_query .= " date_submitted >=".$countdat1 ;
	$stat2_query .= " AND date_submitted <=".$countdat2;
}
if ($t_project_id!=0) {
	if ($kiddies!=0) {
		$stat2_query .=" and $t_hierarchy_table.child_id=$t_bug_table.project_id and parent_id=".$t_project_id;
	} else{
		$stat2_query .=" and $t_bug_table.project_id =".$t_project_id;
	}
} 

$result_records = db_query($stat2_query);
$num_records = db_num_rows( $result_records );
$started = $num_records ;

$stats=array();
$artel=-1;
$stat10=0;	  
$stat20=0;	  
$stat30=0;	  
$stat40=0;	  
$stat50=0;
$stat100=0;

if ( !empty($day_to) &&  !empty($month_to)  && !empty($year_to)) {	  
	$result_records = db_query($stat1_query);
	$num_records = db_num_rows( $result_records );
}else{
	$num_records=0 ;
}

$id = 0;
for( $i=0; $i < $num_records; $i++ ) {
	$next= false ;
	$t_row = db_fetch_array( $result_records );
	// filter out those issues already resolved
	$val=$t_row["new_value"] ;
	$typ=$t_row["type"] ;
	if ($id<>0) {
		if ($t_row["bug_id"] == $id){
			$next = true;
		}else{
			$id=0;
		}
	}
	if (!$next){
		if ($val=="80" or $val =="90" ){
			// skip issue
			$id=$t_row["bug_id"] ;
            $next=true;
		}else{
			// add to final result set
			// check if not already exists if so skip the other records of this issue
			$summary = isset($t_row["summary"]);
			$bugid1 = $t_row["bug_id"];
			if (in_array($bugid1, $stats)) {
	            $next=true;
			} 
			if (!$next){
				$artel ++ ;
				$stats[$artel] = $bugid1;
				$stat100 ++;
				if ($typ=="1"){
					$val = "10";
				}
				switch ($val) {
					case "10":
						$stat10 ++;
						break;
					case "20":
						$stat20 ++;
						break;
					case "30":
						$stat30 ++;
						break;
					case "40":
						$stat40 ++;
						break;
					case "50":
						$stat50 ++;
						break;
				}
			}
		}
	}
}

?>
<tr>
  <td class="form-title" colspan="2">
    <?php 
	echo lang_get( 'viewing_bugs_title' ) ;
	echo " ";
	echo lang_get( 'print_statistics_from' ) ;
	echo " ";
	echo date("Y-m-d",$countdat1 );
	echo " ";
	echo lang_get( 'print_statistics_to' ) ;
	echo " ";
	echo date("Y-m-d",$countdat2 );
	?>    
  </td>  
</tr>

<tr class="row-category">
  <td><?php echo lang_get( 'status' ) ?></td>
  <td><?php echo lang_get( 'print_statistics_totals' ) ?></td>
</tr>
<tr>
  <td class="spacer" colspan="9">&nbsp;</td>
</tr>

<tr>  
  <td>New Issues Submitted</td>
  <td><?php echo $started ?></td>
</tr>
<tr>  
  <td>---------------------</td>
  <td>---------------------</td>
</tr>
<tr bgcolor="<?php echo isset($status_color) ?>" border="1">  
  <td>Status Not assigned</td>
  <td><?php echo $stat10 ?></td>
</tr>
<tr bgcolor="<?php echo isset($status_color) ?>" border="1">  
  <td>Status Feedback</td>
  <td><?php echo $stat20 ?></td>
</tr>
<tr bgcolor="<?php echo isset($status_color) ?>" border="1">  
  <td>Status On Hold</td>
  <td><?php echo $stat30 ?></td>
</tr>
<tr bgcolor="<?php echo isset($status_color) ?>" border="1">  
  <td>Status Confirmed</td>
  <td><?php echo $stat40 ?></td>
</tr>
<tr bgcolor="<?php echo isset($status_color) ?>" border="1">  
  <td>Status Assigned</td>
  <td><?php echo $stat50 ?></td>
</tr>
<tr>  
  <td>---------------------</td>
  <td>---------------------</td>
</tr>
<tr >  
  <td>Total open issues</td>
  <td><?php echo $stat100 ?></td>
</tr>
<tr>
  <td class="form-title" colspan="2"><center>======================================================</center></td>  
</tr>
<tr>
  <td colspan="2"><center>
    <?php 
//	echo "Dates used : ";
//	echo $countdat1." = ".date("Y-m-d",$countdat1 );
//	echo " <<==>> ";
//	echo $countdat2." = ".date("Y-m-d",$countdat2 );
	?>    
  </td>  
</tr>
<tr>
  <td colspan="2"><center>
    <?php 
//	echo $stat1_query ;
	?>    
  </td>  
</tr>
<tr>
  <td colspan="2"><center>
    <?php 
//	echo $stat2_query ;
	?>    
  </td>  
</tr>

<tr>
  <td class="form-title" colspan="2"><center>======================================================</center></td>  
</tr>

<input type="hidden" name="show_flag" value="1" />

</table>

<br />

<?php
layout_page_end();
