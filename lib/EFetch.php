<?php

class EFetch extends EUtils {
	protected $url = 'efetch.fcgi';

	function getHistory($history, $offset = 0, $n = 20) {
		list($webenv, $querykey) = explode('|', $history);

		$params = array(
			'db' => 'pubmed',
      		'webenv' => $webenv,
      		'query_key' => $querykey,
			'retstart' => $offset,
			'retmax' => $n,
		);

		return $this->get($params);
	}

	function getIds($id) {
		$params = array(
			'db' => 'pubmed',
			'id' => $id,
		);

		return $this->get($params);
	}

	function get($params) {
		$input = $this->build_url($this->base . $this->url, $params + $this->defaults);
		$infile = tempnam(sys_get_temp_dir(), 'med-');
		copy($input, $infile);

		return MODS::fromNLM($infile); // deletes $infile
	}
}
