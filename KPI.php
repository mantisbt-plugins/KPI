<?php
class KPIPlugin extends MantisPlugin {
 
	function register() {
		$this->name        = 'KPI';
		$this->description = 'Allows for downloading/viewing KPI data.';
		$this->version     = '2.22';
		$this->requires    = array('MantisCore'       => '2.0.0',);
		$this->author      = 'Cas Nuy';
		$this->contact     = 'Cas-at-nuy.info';
		$this->url         = 'http://www.nuy.info';
		$this->page			= 'config';
	}
 

 	/**
	 * Default plugin configuration.
	 */
	function config() {
		return array(
			'kpi_threshold'	=> 70,
			'status1'		=> 40,
			'status2'		=> 80,
			'limitdays'		=> 2,
			'UOM'			=> 'D',
			'initial'		=> 'Y',
			'working'		=> 1,
			);
	}

	function init() { 
		plugin_event_hook( 'EVENT_MENU_MAIN', 'menu' );
	}
	
 public function menu()
  {
    $links = array();
    $links[] = array(
      'title' => plugin_lang_get("kpi_page"),
      'url' => plugin_page("print_kpi_page.php", true),
	  'icon' => 'fa-stack-overflow'
    );
    return $links;
  }


}