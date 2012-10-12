<?php

ob_start();
ob_start('ob_gzhandler');

ini_set('display_errors', false);
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
			$config = parse_ini_file(__DIR__ . '/config.ini');

			$app = new App($config);
			$app->parse();

			$mods = new MODS($config['bibutils']);
			$mods->fromNLM($app->fetch());

			$response = new Response(200);
			$response->setContentType($app->params['format']);
			$response->outputHeader();

			$suffix = isset($_GET['id']) ? ('-' . (int) $_GET['id']) : null;

			switch($response->getContentType()) {
				case 'application/json':
					$data = $mods->toJSON();

					if($app->params['history']) $data = $app->data($data, $config['url']);

					$response->setBody($data);
					$response->outputBody();
				break;

				case 'text/bibtex':
					header('Content-Disposition: attachment; filename=hubmed' . $suffix . '.bib');
					$mods->toBibTeX(); // outputs data directly using passthru
					break;

				case 'application/research-info-systems':
					header('Content-Disposition: attachment; filename=hubmed' . $suffix . '.ris');
					$mods->toRIS(); // outputs data directly using passthru
					break;

				default:
					throw new WebException(415);
			}

			break;

		default:
			throw new WebException(405);
	}
}
catch (WebException $e) {
	ob_clean();
	$response = new Response($e->getCode(), $e->getMessage());
}
catch (Exception $e) {
	ob_clean();
	$response = new Response(500, $e->getMessage());
}

ob_end_flush(); // gzip buffer
header('Content-Length: ' . ob_get_length());
ob_end_flush(); // plain buffer
