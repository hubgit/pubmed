<?php

class EFetchPubMed extends EUtils {
	protected $wsdl = 'http://www.ncbi.nlm.nih.gov/entrez/eutils/soap/v2.0/efetch_pubmed.wsdl';

	function getHistory($history, $offset = 0, $n = 20) {
		list($webenv, $querykey) = explode('|', $history);

		$params = array(
			'db' => 'pubmed',
      		'WebEnv' => $webenv,
      		'query_key' => $querykey,
			'retstart' => $offset,
			'retmax' => $n,
		);

		return $this->call('run_eFetch', $params);
	}

	function getIds($id) {
		$params = array(
			'db' => 'pubmed',
			'id' => $id,
		);

		return $this->call('run_eFetch', $params);
	}
}