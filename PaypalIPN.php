<?php

class PaypalIPN {

	# @var String $url The Paypal cURL calls
	private $_url;

	# Live or sandbox
	# @param String $mode 'live' or 'sandbox'
	public function __construct($mode = 'live') {
		if ($mode == 'live')
			$this -> _url = "https://www.paypal.com/cgi-bin/webscr";

		else
			$this -> _url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
	}

	public function run() {

		$postFields = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value) {
			$postFields .= "&$key=" . urlencode($value);
		}

		$ch = curl_init();

		curl_setopt_array($ch, array(
			CURLOPT_URL => $this -> _url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $postFields,
			// CURLOPT_SSL_VERIFYHOST => 2,
			// CURLOPT_FORBID_REUSE => 1,
			// CURLOPT_HTTPHEADER => array('Connection: Close')
			));

		$result = curl_exec($ch);
		curl_close($ch);

		echo $result;

   		// Insert your actions here

		$file = "pay_result.txt";

		if(!file_exists(dirname($file)))
			mkdir(dirname($file), 0777, true);

		$f = fopen ($file, 'w');
		fwrite($f, $result . ' .... ' . $postFields);
		fclose($f);
	}
}