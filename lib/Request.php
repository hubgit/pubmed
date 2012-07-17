<?php

class Request {
	private $collection;
	private $id;

	function __construct() {
		$this->parsePath();
	}

	function parsePath() {
		list($this->collection, $this->id) = array_filter(explode('/', trim($_GET['_path'], '/')));
	}

	function getCollection() {
		return (string) $this->collection;
	}

	function getId() {
		return (string) $this->id;
	}

	function hasId() {
		return (bool) $this->id;
	}

}