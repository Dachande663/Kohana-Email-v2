<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Email Transport e.g. mail(), SMTP, postmarkapp etc
 *
 * @package default
 */
abstract class Kohana_Email_Transport {
	

	/**
	 * @var Transport Application Identifier
	 **/
	protected $transport = 'Kohana/Email';


	/**
	 * @var array Configuration array
	 **/
	protected $config;


	/**
	 * @var array Email Addresses
	 **/
	protected $from = array();
	protected $reply = array();
	protected $to = array();
	protected $cc = array();
	protected $bcc = array();


	/**
	 * @var array Header properties
	 **/
	protected $headers = array();


	/**
	 * @var array Message bodies, including type
	 **/
	protected $body = array();


	/**
	 * @var string Subject line
	 **/
	protected $subject;


	/**
	 * @var Multipart mime boundary
	 **/
	protected $mime_boundary;


	/**
	 * Constructor
	 *
	 * @return void
	 **/
	public function __construct(array $config) {
		$this->config = $config;
	} // end func: __construct



	/**
	 * Send the email!
	 *   This must be overridden for each Transport
	 *
	 * @return bool
	 **/
	abstract public function send();



	/**
	 * Add a to address
	 *   Address can be in the format:
	 *   - 'john@smith.com'
	 *   - array('John Smith', 'john@smith.com')
	 *
	 * @param mixed To address
	 * @return self
	 **/
	public function to($address = null) {
		if($address === null) return implode(', ', $this->to);
		$this->to[] = $this->format_email($address);
		return $this;
	} // end func: to



	/**
	 * CC an address
	 *   Address can be in the format:
	 *   - 'john@smith.com'
	 *   - array('John Smith', 'john@smith.com')
	 *
	 * @param mixed To address
	 * @return self
	 **/
	public function cc($address = null) {
		if($address === null) return implode(', ', $this->cc);
		$this->cc[] = $this->format_email($address);
		return $this;
	} // end func: cc



	/**
	 * BCC an address
	 *   Address can be in the format:
	 *   - 'john@smith.com'
	 *   - array('John Smith', 'john@smith.com')
	 *
	 * @param mixed To address
	 * @return self
	 **/
	public function bcc($address = null) {
		if($address === null) return implode(', ', $this->bcc);
		$this->bcc[] = $this->format_email($address);
		return $this;
	} // end func: bcc



	/**
	 * Add a From address
	 *   Address can be in the format:
	 *   - 'john@smith.com'
	 *   - array('John Smith', 'john@smith.com')
	 *
	 * @param mixed From address
	 * @return self
	 **/
	public function from($address = null) {
		if($address === null) return implode(', ', $this->from);
		$this->from[] = $this->format_email($address);
		return $this;
	} // end func: from



	/**
	 * Add a ReplyTo address
	 *   Address can be in the format:
	 *   - 'john@smith.com'
	 *   - array('John Smith', 'john@smith.com')
	 *
	 * @param mixed From address
	 * @return self
	 **/
	public function reply($address = null) {
		if($address === null) return implode(', ', $this->reply);
		$this->reply[] = $this->format_email($address);
		return $this;
	} // end func: reply



	/**
	 * Add a Subject line
	 *
	 * @param string Subject line
	 * @return self
	 **/
	public function subject($subject = null) {
		if($subject === null) return $this->subject;
		$this->subject = $subject;
		return $this;
	} // end func: subject



	/**
	 * Set an Email header
	 *
	 * @param string Header key
	 * @param string Header value
	 * @return self/array
	 **/
	public function header($header_key = null, $header_value = null) {
		if($header_key === null) return $this->headers;
		if($header_value === null) return Arr::get($this->headers, $header_key);
		$this->headers[$header_key] = $header_value;
		return $this;
	} // end func: header



	/**
	 * Add message body
	 *
	 * @param string Message body
	 * @param string Message type: text or html
	 * @return self
	 **/
	public function body($body = null, $type = 'text') {
		if($body === null) return $this->body;
		if(!in_array($type, array('text', 'html'))) $type = 'text';
		$this->body[$type] = (string) $body;
		return $this;
	} // end func: body



	/**
	 * Format an email address into the expected format
	 *
	 * @param mixed Address
	 * @return string
	 **/
	protected function format_email($address) {
		if(is_string($address)) return $address;
		$address = array_pad(array_values($address), 2, null);
		return "{$address[0]} <{$address[1]}>";
	} // end func: format_email



	/**
	 * Returns a MIME boundary, generates one if necessary
	 *
	 * @return string
	 **/
	protected function mime_boundary() {
		if($this->mime_boundary === null) $this->mime_boundary = '==MULTIPART_BOUNDARY_'.md5('kohana-email-transport'.time());
		return $this->mime_boundary;
	} // end func: mime_boundary



	/**
	 * Return full email headers
	 *
	 * @param bool If true, headers are returned as array
	 * @param array List of headers to NOT return
	 * @return string/array
	 **/
	public function generate_headers($return_as_array = false, array $ignore_headers = null) {

		$headers = $this->header();

		if(!isset($headers['To'])) $headers['To'] = $this->to();
		if(!isset($headers['From'])) $headers['From'] = $this->from();
		if(!isset($headers['Reply-To'])) $headers['Reply-To'] = $this->reply();
		if(!isset($headers['Cc'])) $headers['Cc'] = $this->cc();
		if(!isset($headers['Bcc'])) $headers['Bcc'] = $this->bcc();
		if(!isset($headers['Date'])) $headers['Date'] = date('r');
		if(!isset($headers['X-Mailer'])) $headers['X-Mailer'] = $this->transport;
		if(!isset($headers['Subject'])) $headers['Subject'] = $this->subject();

		if(isset($this->body['html'])) {
			if(isset($this->body['text'])) {
				$headers['MIME-Version'] = '1.0';
				$headers['Content-Type'] = 'multipart/alternative; boundary="'.$this->mime_boundary().'"';
			} else {
				$headers['Content-Type'] = 'text/html';
			}
		}

		$headers = array_filter($headers);

		if($ignore_headers) foreach($ignore_headers as $ignore) unset($headers[$ignore]);

		if($return_as_array == true) return $headers;

		$output = array();
		foreach($headers as $field => $value) $output[] = "$field: $value";
		return implode("\r\n", $output);
	} // end func: generate_headers



	/**
	 * Returns the full email including Headers and Body
	 *    Used by Email_Mail and self::spam_score
	 *
	 * @param bool If true, headers are included in return
	 * @return string
	 **/
	public function generate_body($include_headers = false) {

		$body_text = (isset($this->body['text']) and !empty($this->body['text'])) ? $this->body['text'] : false;
		$body_html = (isset($this->body['html']) and !empty($this->body['html'])) ? $this->body['html'] : false;

		$output = null;

		if($body_text and $body_html) { // Multi-part
			$boundary = $this->mime_boundary();
$output = "--$boundary
Content-Type: text/plain; charset=us-ascii
Content-Transfer-Encoding: 7bit

$body_text

--$boundary
Content-Type: text/html; charset=us-ascii
Content-Transfer-Encoding: 7bit

$body_html

--$boundary--";
			
		} elseif($body_html) { // HTML
			$output = $this->body['html'];

		} elseif($body_text) { // Plain
			$output = $this->body['text'];
		}

		
		if($include_headers) $output = $this->generate_headers() . "\r\n\r\n" . $output;
		return $output;
	} // end func: generate_body



	/**
	 * Return a SpamAssassin score/report for this Email
	 *
	 * @param bool Return verbose Report
	 * @return int/array Score or Report
	 * @uses http://spamcheck.postmarkapp.com/
	 **/
	public function spam_score($verbose = false) {

		$email_body = $this->generate_body(true);

		$spam_check_url = 'http://spamcheck.postmarkapp.com/filter';

		$curl_headers = array(
			'Accept: application/json',
			'Content-Type: application/json',
		);

		$json = array(
			'options' => ($verbose) ? 'long' : 'short',
			'email' => $email_body,
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $spam_check_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);

		$result = curl_exec($ch);
		$error  = curl_error($ch);
		$code   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if(!$result) throw new Email_Exception('cURL Error: '.$error);
		$result = json_decode($result, true);
		if(!isset($result['success']) or !$result['success']) throw new Email_Exception('Postmark Error: '.Arr::get($result, 'message', 'unknown'));

		if($verbose === false) return (float) $result['score'];
		$result['email'] = $email_body;
		return $result;
	} // end func: spam_score



} // end class: Kohana_Email_Transport