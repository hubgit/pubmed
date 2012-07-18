<?php

require_once 'Zend/Json.php';

class MODS {
	static function fromNLM($infile) {
		$config = parse_ini_file(__DIR__ . '/../config.ini');
		$outfile = tempnam(sys_get_temp_dir(), 'mods-');

		$command = sprintf($config['bibutils'] . 'med2xml -i utf8 --unicode-no-bom %s > %s', escapeshellarg($infile), escapeshellarg($outfile));
		//print $command;
		exec($command);

		$xml = file_get_contents($outfile);

		unlink($infile);
		unlink($outfile);

		return Zend_Json::fromXml($xml, false);
	}

	static function toJSON($data) {
		$data = Zend_Json::decode($data, Zend_Json::TYPE_OBJECT);
		return array_map(array('self', 'fix'), self::ensureArray($data->modsCollection->mods));
	}

	static function fix($data) {
		// title
		$data->titleInfo->title = rtrim($data->titleInfo->title, '.');

		// names
		$items = array();

		foreach(self::ensureArray($data->{'name'}) as $item) {
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

		foreach(self::ensureArray($data->{'relatedItem'}->{'titleInfo'}) as $item) {
			$type = $item->{'@attributes'}->{'type'};
			if(!$type) $type = 'full';
			$items[$type] = rtrim($item->{'title'}, '.');
		}

		if($items) $data->{'relatedItem'}->{'titleInfo'} = $items;

		// host identifiers
		$items = array();

		foreach(self::ensureArray($data->{'relatedItem'}->{'identifier'}) as $item) {
			$items[$item->{'@attributes'}->{'type'}] = $item->{'@text'};
		}

		if($items) $data->{'relatedItem'}->{'identifier'} = $items;

		// host details
		$items = array();

		foreach(self::ensureArray($data->{'relatedItem'}->{'part'}->{'detail'}) as $item) {
			$items[$item->{'@attributes'}->{'type'}] = $item->{'number'};
		}

		if($items) $data->{'relatedItem'}->{'part'}->{'detail'} = $items;

		// pages
		$items = array();

		foreach(self::ensureArray($data->{'relatedItem'}->{'part'}->{'extent'}) as $item) {
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

		foreach(self::ensureArray($data->{'identifier'}) as $item) {
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

	static function ensureArray($item) {
		if(!$item) return array();
		return is_array($item) ? $item : array($item);
	}
}