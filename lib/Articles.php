<?php

class Articles extends Collection {
	function get() {
		if(isset($_GET['term'])) {
			$service = new ESearch;
			$result = $service->get($_GET['term']);

			return array(
				'totalResults' => $result->Count,
				'links' => array(
					'first' => $this->build_url(BASE . 'articles/', array('history' => $result->WebEnv . '|' . $result->QueryKey)),
				),
			);
			//return $this->fetch($result->WebEnv . '|' . $result->QueryKey, (int) $result->Count);
		}
		else if (isset($_GET['history'])) {
			return $this->fetch($_GET['history']);
		}
		else if (isset($_GET['id'])) {
			$service = new EFetchPubMed;
			return $service->getIds($_GET['id']);
		}
		else if (isset($_GET['related'])) {
			$n = isset($_GET['n']) ? (int) $_GET['n'] : 20;
			$n = min($n, 100);

			$service = new ELink;
			$result = $service->get($_GET['related']);

			$ids = array();
			foreach ($result->LinkSet[0]->LinkSetDb[0]->Link as $link) {
				$ids[] = $link->Id->{'_'};
			}

			$ids = array_slice($ids, 0, $n);

			$service = new EFetchPubMed;
			$result = $service->getIds(implode(',', $ids));

			return array(
				'totalResults' => count($ids),
				'items' => $result->PubmedArticleSet->PubmedArticle,
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

		$service = new EFetchPubMed;
		$result = $service->getHistory($history, $offset, $n);

		$data = array(
			'startIndex' => $offset,
			'itemsPerPage' => $n,
			'items' => is_object($result) ? $result->PubmedArticleSet->PubmedArticle : array(),
			'links' => array(),
		);

		if (count($data['items']) == $n) {
			$data['links']['next'] = $this->build_url(BASE . 'articles/', array('history' => $history, 'n' => $n, 'offset' => $offset + $n));
		}

		return $data;
	}
}