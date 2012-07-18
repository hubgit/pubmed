<?php

ob_start();

//ini_set('display_errors', true);
//error_reporting(E_ERROR | E_PARSE);

set_include_path(__DIR__ . '/lib/' . PATH_SEPARATOR . get_include_path());

function __autoload($name) {
    include $name . '.php';
}

header('Access-Control-Allow-Origin: *');

try {
	switch ($_SERVER['REQUEST_METHOD']) {
		case 'OPTIONS':
			break;

		case 'GET':
			if(!isset($_GET['history'])) throw new WebException(404);

			$config = parse_ini_file(__DIR__ . '/../config.ini');

			$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
			$offset = max(0, $offset);

			$n = isset($_GET['n']) ? (int) $_GET['n'] : 20;
			$n = min($n, 100);

			$service = new EFetch($config['tool'], $config['email']);
			$result = $service->getHistory($history, $offset, $n);

			$data = array(
				'startIndex' => $offset,
				'itemsPerPage' => $n,
				'items' => MODS::toJSON($result, $config['bibutils']),
				'links' => array(),
			);

			$nextOffset = $offset + $n;

			if ($nextOffset ) {
				$params = array('history' => $history, 'n' => $n, 'offset' => $offset + $n);
				$data['links']['next'] = $config['base_uri'] . 'articles/' . '?' . http_build_query($params);
			}

			$response = new Response(200);
			$response->setBody($data);
			break;

		default:
			throw new WebException(405);
	}
}
catch (WebException $e) {
	$response = new Response($e->getCode(), $e->getMessage());
}
catch (Exception $e) {
	$response = new Response(500, $e->getMessage());
}

try {
	if($response instanceof Response) $response->output();
}
catch (Exception $e) {
	//print $e->getMessage();
}
