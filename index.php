<?php

ob_start();

ini_set('display_errors', true);

error_reporting(E_ERROR | E_PARSE);

function __autoload($name) {
    include __DIR__ . '/lib/' . $name . '.php';
}

require __DIR__ . '/route.php';

$config = parse_ini_file('config.ini');
define('BASE', $config['base_uri']);

header('Access-Control-Allow-Origin: *');

try {
	$request = new Request;
	$response = route($request);
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
