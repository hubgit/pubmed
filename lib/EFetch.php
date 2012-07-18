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

		parent::__construct();
	}

	protected function get($params) {
		$infile = tempnam(sys_get_temp_dir(), 'med-');

		$url = $this->base . '?' . http_build_query($params + $this->defaults);
		copy($url, $infile);

		return MODS::fromNLM($infile); // deletes $infile
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

	public function getIds($id) {
		$params = array(
			'db' => 'pubmed',
			'id' => $id,
		);

		return $this->get($params);
	}
}
