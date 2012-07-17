<?php

class Response {
	private $status;
	private $body;

	/**
	 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
	 * @var array
	 */
	private $messages = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported'
	);

	function __construct($status = 200) {
		if($status) $this->setStatus($status);
	}

	function setStatus($status) {
		$this->status = $status;
	}

	function setBody($body) {
		$this->body = $body;
	}

	function setTemplate($template) {
		$this->template = __DIR__ . sprintf('/../templates/%s', $template);
	}

	function outputHeader() {
		header(sprintf('HTTP/1.1 %d %s', $this->status, $this->messages[$this->status]));
		header('Content-Type: application/json; charset=utf-8');
	}

	function outputBody() {
		print json_encode($this->body);
	}

	function output() {
		$this->outputHeader();
		$this->outputBody();
		ob_end_flush(); // output everything
	}
}