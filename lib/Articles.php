<?php

class Articles extends Collection {
	function __construct() {
		$this->config = parse_ini_file(__DIR__ . '/../config.ini');
	}

	function get() {
		if(isset($_GET['term'])) {
			$service = new ESearch($this->config['tool'], $this->config['email']);
			$result = $service->get($_GET['term']);

			return array(
				'totalResults' => (int) $result->Count,
				'links' => array(
					'first' => $this->build_url($this->config['base_uri'] . 'articles/', array('history' => (string) $result->WebEnv . '|' . (string) $result->QueryKey)),
				),
			);
			//return $this->fetch($result->WebEnv . '|' . $result->QueryKey, (int) $result->Count);
		}
		else if (isset($_GET['history'])) {
			return $this->fetch($_GET['history']);
		}
		else if (isset($_GET['id'])) {
			$service = new EFetch($this->config['tool'], $this->config['email']);
			return $service->getIds($_GET['id']);
		}
		else if (isset($_GET['related'])) {
			$n = isset($_GET['n']) ? (int) $_GET['n'] : 20;
			$n = min($n, 100);

			$service = new ELink($this->config['tool'], $this->config['email']);
			$result = $service->get($_GET['related']);

			$ids = array();
			foreach ($result->LinkSet->LinkSetDb->Link as $link) {
				$ids[] = (int) $link->Id;
			}

			$ids = array_slice($ids, 0, $n);

			$service = new EFetch($this->config['tool'], $this->config['email']);
			$result = $service->getIds(implode(',', $ids));

			return array(
				'totalResults' => count($ids),
				'items' => MODS::toJSON($result),
			);
		}
		else {
			throw new WebException(404);
		}
	}

	function fetch($history) {
		$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
		$offset = max(0, $offset);

		$n = isset($_GET['n']) ? (int) $_GET['n'] : 20;
		$n = min($n, 100);

		$service = new EFetch($this->config['tool'], $this->config['email']);
		$result = $service->getHistory($history, $offset, $n);

		$data = array(
			'startIndex' => $offset,
			'itemsPerPage' => $n,
			'items' => MODS::toJSON($result),
			'links' => array(),
		);

		if (count($data['items']) == $n) {
			$data['links']['next'] = $this->build_url($this->config['base_uri'] . 'articles/', array('history' => $history, 'n' => $n, 'offset' => $offset + $n));
		}

		return $data;
	}
}
