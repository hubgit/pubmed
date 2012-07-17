<?php

function route(Request $request) {
	switch ($_SERVER['REQUEST_METHOD']) {
		case 'OPTIONS':
			header('Access-Control-Allow-Origin: *');
			break;

		case 'GET':
			switch ($request->getCollection()) {
				case 'articles':
					//$handler = $request->hasId() ? new Article($request->getId()) : new Articles;
					$handler = new Articles;
					$data = $handler->get();

					$response = new Response;
					$response->setBody($data);
					return $response;

				default:
					return new Response(404);
			}

		default:
			return new Response(405);
	}
}