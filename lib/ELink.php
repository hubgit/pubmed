<?php

class ELink extends EUtils {
	protected $url = 'elink.fcgi';

	function get($id, $db = 'pubmed', $offset = 0, $n = 0) {
		$params = array(
			'dbfrom' => $db,
			'cmd' => 'neighbor',
			'linkname' => 'pubmed_pubmed',
			'id' => $id,
		);

		return parent::get($params);
	}
}
