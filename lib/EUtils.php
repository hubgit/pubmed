<?php

class EUtils extends SoapService {
	protected $wsdl = 'http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/eutils.wsdl';

	function __construct($tool, $email, $db = 'pubmed') {
		$this->defaults = array(
			'db' => $db,
			'tool' => $tool,
			'email' => $email,
			'usehistory' => 'y',
			'rettype' => 'xml',
			'RetStart' => 0,
			'RetMax' => 20,
		);

		parent::__construct();
	}
}