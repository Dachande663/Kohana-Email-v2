<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Email Mail
 *
 * @package default
 */
class Kohana_Email_Mail extends Email_Transport {
	

	/**
	 * Send email using mail()
	 *   A return value of true only means PHP think mail() has succeeded
	 *
	 * @return bool
	 **/
	public function send() {
		$headers = $this->generate_headers(false, array('To', 'Subject'));
		return mail($this->to(), $this->subject(), $this->generate_body(), $headers);
	} // end func: send



} // end class: Kohana_Email_Mail