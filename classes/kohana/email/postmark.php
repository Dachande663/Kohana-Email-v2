<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Email Postmark (postmarkapp.com)
 *
 * @package default
 */
class Kohana_Email_Postmark extends Email_Transport {
	

	/**
	 * @var string API Endpoint URL
	 **/
	protected static $api_endpoint = 'http://api.postmarkapp.com/email';
	protected static $api_endpoint_secure = 'https://api.postmarkapp.com/email';


	/**
	 * Send email using postmark API
	 *
	 * @return bool
	 **/
	public function send() {

		$api_key = Arr::get($this->config, 'api_key');
		if(empty($api_key)) throw new Email_Exception('Could not read Postmark API Key');
		$use_ssl = (bool) Arr::get($this->config, 'ssl', true);
		$api_url = $use_ssl ? Email_Postmark::$api_endpoint_secure : Email_Postmark::$api_endpoint;

		$body = $this->body();

		$curl_headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
			'X-Postmark-Server-Token: '.$api_key
		);

		$json = array(
			'From'        => $this->from(),
			'To'          => $this->to(),
			'Cc'          => $this->cc(),
			'Bcc'         => $this->bcc(),
			'ReplyTo'     => $this->reply(),
			'Subject'     => $this->subject(),
			'HtmlBody'    => Arr::get($body, 'html'),
			'TextBody'    => Arr::get($body, 'text'),
			'Headers'     => array(),
			'Attachments' => array(),
		);

		$ignore_headers = array('From', 'To', 'Cc', 'Bcc', 'Reply-To', 'Subject', 'Content-Type', 'Date');
		$message_headers = $this->generate_headers(true, $ignore_headers);
		foreach($message_headers as $field => $value) {
			$json['Headers'][] = array('Name' => $field, 'Value' => $value);
		}

		$attachments = $this->generate_attachments();
		if($attachments) {
			foreach($attachments as $attachment) {
				$json['Attachments'][] = array(
					'Name' => $attachment['filename'],
					'ContentType' => $attachment['filetype'],
					'Content' => $attachment['content'],
				);
			}
		}

		$json = json_encode(array_filter($json));

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_CAINFO, Kohana::find_file('resources', 'email/ca-bundle', 'pem'));

		$result = curl_exec($ch);
		$error  = curl_error($ch);
		$code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if(!$result) throw new Email_Exception('Could not connect to Postmark server'); # @todo $error
		$result = json_decode($result, true);
		if($code != 200) throw new Email_Exception(Arr::get($result, 'Message', 'An unknown error occurred while communicating with Postmark'), null, $code);
		return true;
	} // end func: send



} // end class: Kohana_Email_Postmark