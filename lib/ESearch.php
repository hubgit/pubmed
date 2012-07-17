<?php

class ESearch extends EUtils {
	function get($term, $offset = 0, $n = 0) {
		$params = array(
			'RetStart' => $offset,
			'RetMax' => $n,
			'term' => $term,
		);

		return $this->call('run_eSearch', $params);
	}
}
