<?php

class EFetchPubMed extends EUtils {
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
		$dir = sys_get_temp_dir();

		$infile = tempnam($dir, 'med-');
		$outfile = tempnam($dir, 'mods-');

		$input = $this->build_url($this->base . $this->url, $params + $this->defaults);
		copy($input, $infile);

		$command = sprintf('/usr/local/bin/med2xml -i utf8 --unicode-no-bom %s > %s',
			escapeshellarg($infile), escapeshellarg($outfile));
		exec($command);

		$data = Zend_Json::fromXml(file_get_contents($outfile), false);
		$data = Zend_Json::decode($data, Zend_Json::TYPE_OBJECT);

		unlink($infile);
		unlink($outfile);

		return $data->modsCollection->mods;
	}

}