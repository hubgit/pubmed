<?php

class EFetch {
	protected $base = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi';

	public function __construct($tool, $email, $db = 'pubmed') {
		$this->defaults = array(
			'db' => $db,
			'tool' => $tool,
			'email' => $email,
			'usehistory' => 'y',
			'rettype' => 'xml',
			'retstart' => 0,
			'retmax' => 20,
		);
	}

	protected function get($params) {
		$nlmfile = tempnam(sys_get_temp_dir(), 'med-');

		$url = $this->base . '?' . http_build_query($params + $this->defaults);
		copy($url, $nlmfile);

		return $nlmfile;
	}

	public function getHistory($history, $offset = 0, $n = 20) {
		list($webEnv, $queryKey) = explode('|', $history);

		$params = array(
			'db' => 'pubmed',
      		'webenv' => $webEnv,
      		'query_key' => $queryKey,
			'retstart' => $offset,
			'retmax' => $n,
		);

		return $this->get($params);
	}

	public function getIds($ids) {
		$params = array(
			'db' => 'pubmed',
			'id' => $ids,
		);

		return $this->get($params);
	}
}
