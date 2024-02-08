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
<br>
<form action="<?php echo plugin_page( 'config_edit' ) ?>" method="post">

<div class="widget-box widget-color-blue2">
<div class="widget-header widget-header-small">
	<h4 class="widget-title lighter">
		<i class="ace-icon fa fa-text-width"></i>
		<?php echo lang_get( 'kpi_title') . ': ' . lang_get( 'kpi_config' )?>
	</h4>
</div>
<div class="widget-body">
<div class="widget-main no-padding">
<div class="table-responsive"> 
<table class="table table-bordered table-condensed table-striped"> 


	<?php echo form_security_field( 'plugin_KPI_config_update' ) ?>


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

<tr  >
	<td class="category">
		<?php echo lang_get( 'uom' ) ?>
	</td>
	<td >
		<label><input type="text" name="uom" size="1" value="<?php echo plugin_config_get( 'UOM' ) ?>"/> </label>
	</td>
</tr>
<tr  >
	<td class="category">
		<?php echo lang_get( 'initial' ) ?>
	</td>
	<td >
		<label><input type="text" name="initial" size="1" value="<?php echo plugin_config_get( 'initial' ) ?>"/> </label>
	</td>
</tr>
<tr >
			<td class="category">
				<?php echo lang_get( 'working' ) ?>
			</td>
			<td class="left">
			<label><input type="radio" name='working' value="1" <?php echo( ON == plugin_config_get( 'working' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'yes' )?></label>
			<label><input type="radio" name='working' value="0" <?php echo( OFF == plugin_config_get( 'working' ) )? 'checked="checked" ' : ''?>/>
			<?php echo lang_get( 'no' )?></label>
			</td>
		</tr>

</div>
</div>
<div class="widget-toolbox padding-8 clearfix">
	<input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'change_configuration' )?>" />
</div>
	</table>
</div>
</div>
</form>
</div>
</div>
<?php
layout_page_end();