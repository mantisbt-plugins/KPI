<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
$f_kpi_treshold		= gpc_get_int('kpi_treshold',70);
$f_status1		= gpc_get_int('status1',40);
$f_status2		= gpc_get_int('status2',80);
$f_limitdays		= gpc_get_int('limitdays',2);

plugin_config_set('kpi_treshold'			, $f_kpi_treshold);		
plugin_config_set('status1'			, $f_status1);	
plugin_config_set('status2'			, $f_status2);	
plugin_config_set('limitdays'			, $f_limitdays);	
	
print_successful_redirect( plugin_page( 'config',TRUE ) );