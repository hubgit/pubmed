<?php

class Articles extends Collection {
	function get() {
		if(isset($_GET['term'])) {
			$service = new ESearch;
			$result = $service->get($_GET['term']);

			return array(
				'totalResults' => (int) $result->Count,
				'links' => array(
					'first' => $this->build_url(BASE . 'articles/', array('history' => (string) $result->WebEnv . '|' . (string) $result->QueryKey)),
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
			foreach ($result->LinkSet->LinkSetDb->Link as $link) {
				$ids[] = (int) $link->Id;
			}

			$ids = array_slice($ids, 0, $n);

			$service = new EFetchPubMed;
			$result = $service->getIds(implode(',', $ids));
			$result = $this->ensureArray($result);

			array_walk($result, array($this, 'fix'));

			return array(
				'totalResults' => count($ids),
				'items' => $result ? $result : array(),
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
		$result = $this->ensureArray($result);

		array_walk($result, array($this, 'fix'));

		$data = array(
			'startIndex' => $offset,
			'itemsPerPage' => $n,
			'items' => $result ? $result : array(),
			'links' => array(),
		);

		if (count($data['items']) == $n) {
			$data['links']['next'] = $this->build_url(BASE . 'articles/', array('history' => $history, 'n' => $n, 'offset' => $offset + $n));
		}

		return $data;
	}

	function fix(&$data) {
		// title
		$data->titleInfo->title = rtrim($data->titleInfo->title, '.');

		// names
		$items = array();

		foreach($this->ensureArray($data->{'name'}) as $item) {
			$parts = array(
				'type' => $item->{'@attributes'}->{'type'},
			);

			foreach($item->{'namePart'} as $part) {
				$parts[$part->{'@attributes'}->{'type'}][] = $part->{'@text'};
			}

			$items[] = $parts;
		}

		if($items) $data->{'name'} = $items;

		// host title
		$items = array();

		foreach($this->ensureArray($data->{'relatedItem'}->{'titleInfo'}) as $item) {
			$type = $item->{'@attributes'}->{'type'};
			if(!$type) $type = 'full';
			$items[$type] = rtrim($item->{'title'}, '.');
		}

		if($items) $data->{'relatedItem'}->{'titleInfo'} = $items;

		// host identifiers
		$items = array();

		foreach($this->ensureArray($data->{'relatedItem'}->{'identifier'}) as $item) {
			$items[$item->{'@attributes'}->{'type'}] = $item->{'@text'};
		}

		if($items) $data->{'relatedItem'}->{'identifier'} = $items;

		// host details
		$items = array();

		foreach($this->ensureArray($data->{'relatedItem'}->{'part'}->{'detail'}) as $item) {
			$items[$item->{'@attributes'}->{'type'}] = $item->{'number'};
		}

		if($items) $data->{'relatedItem'}->{'part'}->{'detail'} = $items;

		// pages
		$items = array();

		foreach($this->ensureArray($data->{'relatedItem'}->{'part'}->{'extent'}) as $item) {
			$items[$item->{'@attributes'}->{'unit'}] = array(
				'start' => $item->start,
				'end' => $item->end,
			);
		}

		if($items) $data->{'relatedItem'}->{'part'}->{'extent'} = $items;
		if($items['page']['start']) {
			$page = $items['page']['start'];
			if($items['page']['end']) $page .= '-' . $items['page']['end'];
			$data->{'relatedItem'}->{'part'}->{'detail'}['page'] = $page;
		}

		// identifiers
		$items = array();

		foreach($this->ensureArray($data->{'identifier'}) as $item) {
			$items[$item->{'@attributes'}->{'type'}] = $item->{'@text'};
		}

		if($items) $data->{'identifier'} = $items;

		// date
		$date = $data->{'relatedItem'}->part->date;
		$date = preg_replace('/\/\w+/g', '');
		$parts = explode('-', $date);

		if($parts) {
			try {
				$date = new DateTime($date);

				$format = implode(' ', array_slice(array('Y', 'M', 'd'), 0, count($parts)));
				$data->{'relatedItem'}->part->date = $date->format($format);
			}
			catch(Exception $e) {

			}
		}
	}

	function ensureArray($item) {
		if(!$item) return array();
		return is_array($item) ? $item : array($item);
	}
}