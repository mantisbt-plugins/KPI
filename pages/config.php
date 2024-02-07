<?php
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );
layout_page_header( lang_get( 'kpi_title' ) );
layout_page_begin( 'config_page.php' );
print_manage_menu();
?>
<div class="col-md-12 col-xs-12">
<div class="space-10"></div>
<div class="form-container" > 
<br/>
<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-text-width"></i>
		<?php echo  lang_get( 'plugin_format_config' )?>
	</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive"> 
<table class="table table-bordered table-condensed table-striped"> 

<br/>
<form action="<?php echo plugin_page( 'config_edit' ) ?>" method="post">
<table align="center" class="width75" cellspacing="1">
<tr>
	<td class="form-title" colspan="3">
		<?php echo lang_get( 'kpi_title' ) . ': ' . lang_get( 'kpi_config' ) ?>
	</td>
</tr>


<tr  >
	<td class="category">
		<?php echo lang_get( 'access_level' ) ?>
	</td>
	<td>
		<select name="kpi_threshold">
			<?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'kpi_treshold' ) ) ?>
		</select>
	</td>
</tr> 

<tr  >
	<td class="category">
		<?php echo lang_get( 'status1' ) ?>
	</td>
	<td>
		<select name="status1">
			<?php print_enum_string_option_list( 'status', plugin_config_get( 'status1' ) ) ?>
		</select>
	</td>
</tr> 

<tr  >
	<td class="category">
		<?php echo lang_get( 'status2' ) ?>
	</td>
	<td>
		<select name="status2">
			<?php print_enum_string_option_list( 'status', plugin_config_get( 'status2' ) ) ?>
		</select>
	</td>
</tr> 

<tr  >
	<td class="category">
		<?php echo lang_get( 'limitdays' ) ?>
	</td>
	<td >
		<label><input type="text" name="limitdays" size="5" value="<?php echo plugin_config_get( 'limitdays' ) ?>"/> </label>
	</td>
</tr>


<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'kpi_update_config' ) ?>" />
	</td>
</tr>

</table>
<form>

<?php
layout_page_end();