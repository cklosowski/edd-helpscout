<?php

namespace EDD_HelpScout;

class RequestListener {

	/**
	 * @var bool
	 */
	private $greedy = false;

	/**
	 * @var string
	 */
	private $endpoint = '/edd-hs-api';

	private $action_classmap = array(
		'get_customer_data' => 'CustomerData',
		'resend_receipt' => 'ResendReceipt',
		'deactivate_site' => 'DeactivateSite'
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->greedy = (bool) get_option( 'edd_hs_greedy_listening', 1 );

		$this->route();

	}

	/**
	 * @return bool|string
	 */
	protected function route() {

		if( ! $this->is_api_request() ) {
			return false;
		}

		$action = ( isset( $_GET['action'] ) ) ? sanitize_text_field( $_GET['action'] ) : 'get_customer_data';

		// whitelist given action
		if( ! array_key_exists( $action, $this->action_classmap ) ) {
			return false;
		}

		$class = 'EDD_HelpScout\\Actions\\' . $this->action_classmap[ $action ];

		if( class_exists( $class ) ) {
			new $class();
		}

		return true;
	}

	/**
	 * Is this a request we should respond to?
	 *
	 * @return bool
	 */
	private function is_api_request() {

		$signature_given = isset( $_SERVER['HTTP_X_HELPSCOUT_SIGNATURE'] );

		if( $this->greedy && $signature_given ) {
			$is_helpscout_request = true;
		} else {
			$is_helpscout_request = stristr( $_SERVER['REQUEST_URI'], $this->endpoint ) !== false;
		}

		return $is_helpscout_request;
	}



}