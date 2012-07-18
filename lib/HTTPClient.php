<?php

class HTTPClient {
	public $base;
	public $curl;

	public $types = array(
		'json' => 'application/json',
		'html' => 'text/html',
		'xml' => 'application/xml',
		'text' => 'text/plain',
	);

	function __construct($base) {
		$this->base = $base;
		$this->curl_init();
	}

	function absolute($url) {
		return preg_match('/^https?:/', $url) ? $url : $this->base . $url;
	}

	/**
	 * HTTP methods
	 */
	function get($url, $params = array(), $type = null) {
		if($params) $url .= '?' . http_build_query($params);

		$this->curl_setopt_array(array(
			CURLOPT_URL => $this->absolute($url),
			CURLOPT_HTTPGET => true,
		));

		if($type) {
			if(isset($this->types[$type])) {
				$type = $this->types[$type];
			}
			$this->set_accept_header($type);
		}

		return $this->curl_exec($type);
	}

	function post($url, $params = array(), $type = null) {
		$this->curl_setopt_array(array(
			CURLOPT_URL => $this->absolute($url),
			CURLOPT_POSTFIELDS => http_build_query($params),
		));

		return $this->curl_exec($type);
	}

	/**
	 * cURL methods
	 */
	function set_cookie_file($file = null) {
		if(!$file) $file = tempnam(sys_get_temp_dir(), 'cookies-');

		$this->curl_setopt_array(array(
		    CURLOPT_COOKIEJAR => $file,
		    CURLOPT_COOKIEFILE => $file,
		    CURLOPT_COOKIESESSION => true,
		));
	}

	function set_accept_header($type) {
		$header = sprintf('Accept: %s;q=1,*/*;q=0.1', $this->types[$type]);

		$this->curl_setopt_array(array(
		    CURLOPT_HTTPHEADER => array($header)
		));
	}

	function curl_setopt_array($options = array()) {
		curl_setopt_array($this->curl, $options);
	}

	function curl_getinfo($field) {
		return curl_getinfo($this->curl, $field);
	}

	private function curl_init() {
		$this->curl = curl_init();

		$this->curl_setopt_array(array(
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER => true,
			//CURLOPT_VERBOSE => true,
		));
	}

	function curl_exec($type = null) {
		return new HTTPResponse($this, curl_exec($this->curl), $type);
	}
}

class HTTPResponse {
	public $request;
	public $code;
	public $type;
	public $charset;
	public $headers;
	public $body;
	public $data;
	public $dom;
	public $xpath;

	function __construct($request, $response, $type = null) {
		$this->request = $request;

		$this->code = $request->curl_getinfo(CURLINFO_HTTP_CODE);

		list($this->type, $this->charset) = array_map('trim', explode(';', $request->curl_getinfo(CURLINFO_CONTENT_TYPE), 2));
		if ($type) $this->type = $type; // manual override

		$header_size = $request->curl_getinfo(CURLINFO_HEADER_SIZE);
		$this->headers = $this->parse_headers(mb_substr($response, 0, $header_size));
		$this->body = mb_substr($response, $header_size);

		if($type && $this->body) $this->parse($this->body, $this->type); //if (substr($this->code, 0, 1) == 2)
	}

	function parse_headers($input) {
		$items = array();

		foreach(explode("\n", $input) as $row) {
			list($name, $value) = array_map('trim', explode(':', $row, 2));
			if($value) $items[strtolower($name)][] = $value;
		}

		return $items;
	}

	function parse($content, $type) {
		switch ($type) {
			case 'text/html':
				$this->dom = new DOMDocument;
				$this->dom->preserveWhiteSpace = false;
				$this->dom->documentURI = $this->request->curl_getinfo(CURLINFO_EFFECTIVE_URL);

				$useErrors = libxml_use_internal_errors(true); // silence errors loading HTML
				$this->dom->loadHTML($content);
				libxml_use_internal_errors($useErrors); // restore

				$this->xpath = new DOMXPath($this->dom);
				break;

			case 'text/xml':
			case 'application/xml':
				$this->dom = new DOMDocument;
				$this->dom->preserveWhiteSpace = false;
				$this->dom->documentURI = $this->request->curl_getinfo(CURLINFO_EFFECTIVE_URL);

				$this->dom->loadXML($content, LIBXML_DTDLOAD | LIBXML_NOENT | LIBXML_NOCDATA);
				$this->xpath = new DOMXPath($this->dom);
				break;

			case 'application/json':
				$this->data = json_decode($content, true);
				break;

			default:
				break;
		}
	}
}