<?php

ob_start();
ob_start('ob_gzhandler');

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
			$config = parse_ini_file(__DIR__ . '/config.ini');

			$format = isset($_GET['format']) ? $_GET['format'] : 'application/json';

			$service = new EFetch($config['tool'], $config['email']);

			if(isset($_GET['history'])) {
				$history = $_GET['history'];

				$total = isset($_GET['total']) ? $_GET['total'] : 1000;

				$offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
				$offset = max(0, $offset);

				$n = isset($_GET['n']) ? (int) $_GET['n'] : 20;
				$n = min($n, 100);

				$nlmfile = $service->getHistory($history, $offset, $n);
			}
			else if (isset($_GET['id'])) {
				$nlmfile = $service->getIds($_GET['id']);
			}
			else {
				throw new WebException(404);
			}

			$response = new Response(200);
			$response->setContentType($format);
			$response->outputHeader();

			$mods = new MODS($config['bibutils']);
			$mods->fromNLM($nlmfile);

			switch($format) {
				case 'application/json':
					$data = $mods->toJSON();

					if (isset($history)) {
						$data = array(
							'startIndex' => $offset,
							'itemsPerPage' => $n,
							'items' => $data,
							'links' => array(),
						);

						$nextOffset = $offset + $n;

						if($nextOffset <= $total) {
							$params = array('history' => $history, 'total' => $total, 'n' => $n, 'offset' => $nextOffset);
							$data['links']['next'] = $config['base_uri'] . 'articles/' . '?' . http_build_query($params);
						}
					}

					$response->setBody($data);
					$response->outputBody();
				break;

				case 'text/bibtex':
					header('Content-Disposition: attachment; filename=hubmed.bib');
					$mods->toBibTeX(); // outputs data directly using passthru
					break;

				case 'application/research-info-systems':
					header('Content-Disposition: attachment; filename=hubmed.ris');
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
