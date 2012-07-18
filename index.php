<?php

ob_start();

ini_set('display_errors', true);

error_reporting(E_ERROR | E_PARSE);

set_include_path(__DIR__ . '/lib/' . PATH_SEPARATOR . get_include_path());
require 'Zend/Json.php';

function __autoload($name) {
    include __DIR__ . '/lib/' . $name . '.php';
}

$config = parse_ini_file('config.ini');
define('BASE', $config['base_uri']);
define('BIBUTILS', $config['bibutils']);

header('Access-Control-Allow-Origin: *');

try {
	switch ($_SERVER['REQUEST_METHOD']) {
		case 'OPTIONS':
			break;

		case 'GET':
			$handler = new Articles;
			$data = $handler->get();

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
	print $e->getMessage();
}
