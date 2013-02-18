<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Email Mailer
 *
 * @package    Email
 * @author     Luke Lanchester
 * @copyright  (c) 2012 Luke Lanchester
 * @see https://github.com/Dachande663/Kohana-Email-v2
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