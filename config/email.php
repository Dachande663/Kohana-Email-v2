<?php defined('SYSPATH') or die('No direct script access.');
return array(

	'charset' => 'utf-8',
	'eol'     => "\n",
    
	'default' => array(
		'transport' => 'mail',
	),

	'postmark' => array(
		'transport' => 'postmark',
		'api_key'   => '1234',
		'ssl'       => true,
	),

);