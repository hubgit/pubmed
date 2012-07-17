<?php

class SoapService {
	protected $defaults = array();
	protected $client;

	function __construct() {
		$this->client = new SOAPClient($this->wsdl, array(
			'features' => SOAP_SINGLE_ELEMENT_ARRAYS + SOAP_USE_XSI_ARRAY_TYPE,
          	'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
          	'cache_wsdl' => WSDL_CACHE_MEMORY,
			//'trace' => true,
		));
	}

	function call($method, $params){
		try{
			return call_user_func(array($this->client, $method), array_merge($this->defaults, $params));
		}
		catch (SoapFault $exception) {
			//print_r($this->client->__getLastRequest());
			//print_r($this->client->__getLastResponse());
		}
	}
}