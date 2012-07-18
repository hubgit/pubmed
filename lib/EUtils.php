<?php

class EUtils extends HTTPService {
	protected $base = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/';

	function __construct($tool, $email, $db = 'pubmed') {
		$this->defaults = array(
			'db' => $db,
			'tool' => $tool,
			'email' => $email,
			'usehistory' => 'y',
			'rettype' => 'xml',
			'retstart' => 0,
			'retmax' => 20,
		);

		parent::__construct();
	}

	function get($params) {
		$response = $this->client->get($this->url, $params + $this->defaults, 'xml');
		return simplexml_import_dom($response->dom);
	}
}