<?php

require_once 'Zend/Json.php';

class MODS {
	protected $bibutils;
	protected $modsfile;

	function __construct($bibutils = '.') {
		$this->bibutils = rtrim($bibutils, '/');
		if(!file_exists($this->bibutils . '/med2xml')) throw new WebException(500, 'bibutils not found');
		$this->modsfile = tempnam(sys_get_temp_dir(), 'mods-');
	}

	function __destruct() {
       if(file_exists($this->modsfile)) unlink($this->modsfile);
	}

	public function fromNLM($nlmfile) {
		if(!file_exists($nlmfile)) throw new WebException(500, 'nlm file not found');

		$command = sprintf($this->bibutils . '/med2xml -i utf8 --unicode-no-bom %s > %s', escapeshellarg($nlmfile), escapeshellarg($this->modsfile));
		exec($command);

		unlink($nlmfile);
	}

	public function toJSON() {
		$json = Zend_Json::fromXml(file_get_contents($this->modsfile), false);
		$data = Zend_Json::decode($json, Zend_Json::TYPE_OBJECT);
		return array_map(array($this, 'fix'), $this->ensureArray($data->modsCollection->mods));
	}

	public function toBibTeX() {
		$command = sprintf($this->bibutils . '/xml2bib -i utf8 --unicode-no-bom %s', escapeshellarg($this->modsfile));
		//print $command;
		passthru($command);
	}

	public function toRIS() {
		$command = sprintf($this->bibutils . '/xml2ris -i utf8 --unicode-no-bom %s', escapeshellarg($this->modsfile));
		//print $command;
		passthru($command);
	}

	protected function ensureArray($item) {
		if(!$item) return array();
		return is_array($item) ? $item : array($item);
	}

	protected function fix($data) {
		// title
		$data->titleInfo->title = rtrim($data->titleInfo->title, '.');

		// names
		$items = array();

		foreach($this->ensureArray($data->{'name'}) as $item) {
			if(!is_array($item->{'namePart'})) continue;

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
		$date = preg_replace('/\/\w+/', '', $date);
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

		return $data;
	}
}