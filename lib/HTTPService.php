<?php

class HTTPService {
	protected $base;
	protected $defaults = array();
	protected $client;

	function __construct() {
		$this->client = new HTTPClient($this->base);
		$this->client->parse = false;
	}

	function build_url($url, $params = array()) {
		if ($params) $url .= '?' . http_build_query($params);
		return $url;
	}
}