<?php

/**
 * HTTP status code and message body
 */
class WebException extends Exception {
	/**
	 * @param $code
	 * @param null $message
	 * @param Exception|null $previous
	 */
	function __construct($code, $message = null, Exception $previous = null) {
		//ob_end_clean(); // clear all previous output
		parent::__construct($message, $code, $previous);

		$this->code = $code;
		$this->message = $message;
	}
}