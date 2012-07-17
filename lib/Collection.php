<?php

class Collection {
	function build_url($url, $params = array()) {
		if($params) $url .= '?' . http_build_query($params);
		return $url;
	}
}