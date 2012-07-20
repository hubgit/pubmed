<?php

class App {
	protected $service;

	public $params = array(
		'format' => 'application/json',
		'history' => null,
		'id' => null,
		'total' => 1000,
		'offset' => 0,
		'n' => 20,
	);

	function __construct($config) {
		$this->service = new EFetch($config['tool'], $config['email']);
	}

	function parse() {
		foreach ($this->params as $name => $value) {
			if (isset($_GET[$name])) {
				$this->params[$name] = $_GET[$name];
			}
		}

		$this->params['offset'] = max(0, (int) $this->params['offset']);
		$this->params['n'] = min(100, (int) $this->params['n']);
		$this->params['total'] = (int) $this->params['total'];
	}

	function fetch() {
		if ($this->params['history']) return $this->history();
		if ($this->params['id']) return $this->ids();
		throw new WebException(404);
	}

	function history() {
		return $this->service->getHistory($this->params['history'], $this->params['offset'], $this->params['n']);
	}

	function ids() {
		return $this->service->getIds($this->params['id']);
	}

	function data($data, $url) {
		$params = $this->params;

		$data = array(
			'startIndex' => $params['offset'],
			'itemsPerPage' => $params['n'],
			'items' => $data,
			'links' => array(),
		);

		$nextOffset = $params['offset'] + $params['n'];

		if($nextOffset <= $params['total']) {
			$params = array(
				'history' => $params['history'],
				'total' => $params['total'],
				'n' => $params['n'],
				'offset' => $nextOffset,
			);

			$data['links']['next'] = $url . 'articles/' . '?' . http_build_query($params);
		}

		return $data;
	}
}