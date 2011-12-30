<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Email wrapper
 *
 * @package default
 **/
class Kohana_Email {
	

	/**
	 * @var string Default config group to load
	 **/
	public static $default = 'default';


	/**
	 * Create a new Email
	 *
	 * @return void
	 **/
	public static function compose($config_group = null) {
		
		# Load Config
		if($config_group === null) $config_group = Email::$default;
		$all_config = Kohana::$config->load('email')->as_array();
		if(!isset($all_config[$config_group])) {
			throw new Email_Exception('Failed to load Email config group: :group', array(':group' => $config_group));
		}
		$config = $all_config[$config_group];

		# Load Transport
		$transport = Arr::get($config, 'transport');
		$transport_class = 'Email_'.$transport;
		if($transport == null or !class_exists($transport_class)) {
			throw new Email_Exception('Failed to load Email Transport: :transport', array(':transport' => $transport));
		}

		return new $transport_class($config);
	} // end func: compose


	/**
	 * Disallow instantiation
	 *
	 * @return void
	 **/
	protected function __construct() {
	} // end func: __construct


} // end class: Kohana_Email