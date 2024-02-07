<?php
Function workdays($days){
	$no_full_weeks = floor($days / 7);    
	$no_remaining_days = fmod($days, 7);    
	$workingDays = $no_full_weeks * 5;    
	if ($no_remaining_days > 0 )    {      
		$workingDays += $no_remaining_days;    
	}    
	return $workingDays;
}

Function print_kpi_menu() {
	return;
	$link=plugin_page('main_kpi_page.php');
	$link1=plugin_page('print_kpi_page.php');
	$link2=plugin_page('print_statistics_page.php');
	$link3=plugin_page('print_statistics_page4.php');
	$link4=plugin_page('print_statistics_page3.php');
	?>
	<center>
	<a href="<?php echo $link ?>"><?php echo lang_get( 'KPI_main' ) ?></a> | 
	<a href="<?php echo $link1 ?>"><?php echo lang_get( 'KPI_workflow' ) ?></a> | 
	<a href="<?php echo $link2 ?>"><?php echo lang_get( 'KPI_statistics' ) ?></a> | 
	<a href="<?php echo $link3 ?>"><?php echo lang_get( 'KPI_submitted' ) ?></a> | 
	<a href="<?php echo $link4 ?>"><?php echo lang_get( 'KPI_status' ) ?></a>
	<br><br>
<?php
}