<?php	########################################################	# Mantis Bugtracker Add-On	#	#                    KPI's	#	# by cas Nuy	# 	########################################################require_once( 'core.php' );require_once( config_get( 'plugin_path' ) . 'KPI' . DIRECTORY_SEPARATOR . 'KPI_api.php' );  auth_ensure_user_authenticated();layout_page_header( lang_get( 'kpi_link' ) );layout_page_begin();?><br><br><center><?php echo lang_get( 'workflow_text' ) ?><br><br><?php echo lang_get( 'statistics_text' ) ?><br><br><?php echo lang_get( 'submitted_text' ) ?><br><br><?php echo lang_get( 'status_text' ) ?><br><br><br><br><?phpprint_kpi_menu( ) ;layout_page_end();