<?php

class AdaptivePaymentChained {

	public $api_user = "OliReyLikesYouMain_api1.GMail.com";
	public $api_pass = "R8DVSTUSEXUYVCUM";
	public $api_sig = "AFcWxV21C7fd0v3bYYYRCpSSRl31ApHiVkB0dKziNblhKOoGbR8X4t0c";
	public $app_id = "APP-80W284485P519543T";
	public $apiUrl = 'https://svcs.sandbox.paypal.com/AdaptivePayments/';
	public $paypalUrl = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_ap-payment&paykey=";
	public $headers;

	public $returnUrl = "http://localhost/wip/clientItems/campusRooms/rooms/room/90";

	public $campusRooms = "OliReyLikesYouMain@GMail.com";
	public $landlord = "OliReyLikesYouLandlord1@GMail.com";

	// Always and Forever
	function __construct() {
		$this -> headers = array(
			"X-PAYPAL-SECURITY-USERID: " . $this -> api_user,
			"X-PAYPAL-SECURITY-PASSWORD: " . $this -> api_pass,
			"X-PAYPAL-SECURITY-SIGNATURE: " . $this -> api_sig,
			"X-PAYPAL-REQUEST-DATA-FORMAT: JSON",
			"X-PAYPAL-RESPONSE-DATA-FORMAT: JSON",
			"X-PAYPAL-APPLICATION-ID: " . $this -> app_id
			);

		$this -> envelope = array(
			"errorLanguage" => "en_us",
			"detailLevel" => "returnAll"
			);
	}

	// Wrapper for getting payment details
	function getPaymentOptions($paykey) {
		$packet = array(
			"requestEnvelope" => $this -> envelope,
			"payKey" => $paykey
			);

		return $this -> _paypalSend($packet, "GetPaymentOptions");
	}

	// Set payment details
	function setPaymentOptions() {

	}

	// curl wrapper for sending things to Paypal
	function _paypalSend($data, $call) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this -> apiUrl.$call);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this -> headers);

		ini_set('max_execution_time', 300); //300 seconds = 5 minutes

		return json_decode(curl_exec($ch), TRUE);
	}

	function setPaymentDetails() {
		// Set payment details
		return array(
			"requestEnvelope" => $this -> envelope,
			"payKey" => $this -> payKey,
			"receiverOptions" => array(
				array(
					"receiver" => array("email" => $this -> campusRooms),
					"invoiveData" => array(
						"item" => array(
							array(
								"name" => "10% Rent Commission",
								"price" => "7.00",
								"identifier" => "rent_commission"
								)
							)
						)
					),
				array(
					"receiver" => array("email" => $this -> landlord),
					"invoiveData" => array(
						"item" => array(
							array(
								"name" => "Rent",
								"price" => "70.00",
								"identifier" => "rent"
								)
							)
						)
					)
				)
			);
	}

	// Work out
	function splitPay() {
		// create the pay request
		$createPacket = array(
			"actionType" => "PAY",
			"currencyCode" => "USD",
			"receiverList" => array(
				"receiver" => array(
					array(
						"amount" => "700.00",
						"email" => $this -> campusRooms,
						"primary" => 'false'
						),
					array(
						"amount" => "7000.00",
						"email" => $this -> landlord,
						"primary" => 'true'
						),
					)
				),
			"returnUrl" => $this -> returnUrl,
			"cancelUrl" => "http://localhost/wip/clientItems/campusRooms/rooms/room/86",
			"requestEnvelope" => $this -> envelope
			);

		$response = $this -> _paypalSend($createPacket, "Pay");
		$this -> payKey = $response['payKey'];

		echo '<pre />';
		print_r($response);

		$response = $this -> _paypalSend($this -> setPaymentDetails(), "SetPaymentOptions");

		echo '<pre />';
		print_r($response);

		$dets = $this -> getPaymentOptions($this -> payKey);

		echo '<pre />';
		print_r($dets);

		// Head over to Paypal
		header("Location: " . $this -> paypalUrl . $this -> payKey);
		die();
	}
}