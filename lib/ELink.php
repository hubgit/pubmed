<?php

class ELink extends EUtils {
	function get($id, $offset = 0, $n = 0) {
		$params = array(
			'dbfrom' => $this->db,
			'cmd' => 'neighbor',
			'linkname' => 'pubmed_pubmed',
			'id' => $id,
		);

		return $this->call('run_eLink', $params);
	}
}
