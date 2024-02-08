<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
form_security_validate( 'plugin_KPI_config_update' );
$f_kpi_treshold	= gpc_get_int('kpi_treshold',70);
$f_status1		= gpc_get_int('status1',40);
$f_status2		= gpc_get_int('status2',80);
$f_limitdays	= gpc_get_int('limitdays',2);
$f_uom			= gpc_get_string('uom', 'D');
$f_initial		= gpc_get_string('initial', 'Y');
$f_working		= gpc_get_int('working', 1);

plugin_config_set('kpi_treshold', $f_kpi_treshold);		
plugin_config_set('status1'		, $f_status1);	
plugin_config_set('status2'		, $f_status2);	
plugin_config_set('limitdays'	, $f_limitdays);
plugin_config_set('UOM'			, $f_uom);	
plugin_config_set('initial'		, $f_initial);	
plugin_config_set('working'		, $f_working);		

form_security_purge( 'plugin_KPI_config_update' );
print_successful_redirect( plugin_page( 'config',TRUE ) );