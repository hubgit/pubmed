<?php

class ESearch extends EUtils {
	protected $url = 'esearch.fcgi';

	function get($term, $offset = 0, $n = 0) {
		$params = array(
			'retstart' => $offset,
			'retmax' => $n,
			'term' => $term,
		);

		return parent::get($params);
	}
}
