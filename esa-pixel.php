<?php

header('Content-Type: image/gif');
header('Expires: Wed, 5 Feb 1986 06:06:06 GMT'); 
header('Cache-Control: no-cache'); 
header('Cache-Control: must-revalidate'); 
header('X-Content-Type-Options: nosniff');
header("Content-Length: 43"); 
echo hex2bin('47494638396101000100900000ff000000000021f90405100000002c00000000010001000002020401003b');

error_reporting(E_ALL);
ini_set ('display_errors', 0);
ini_set ('log_errors', 1);

function fatal_handler() {
    $error = error_get_last();

	if ($error != NULL && array_key_exists('type', $error) && $error['type'] == 1) {
		$error['kind'] = 'Fatal';
		$error['url'] = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		$error['description'] = $error['message'];
		$error['stacktrace'] = debug_backtrace();
		$error['date'] = date("Y-m-d H:i:s");
		$Conf['page']['title'] = "77design";
		$handle = fopen(__DIR__.'/cache/logs/errors-'.date("Y-m-d").'.log', 'a');
		fwrite($handle, json_encode($error)."\n");
		fclose($handle);	
		if (array_key_exists('CONTENT_TYPE', $_SERVER) && $_SERVER['CONTENT_TYPE'] == 'application/json') {
			http_response_code(500);
			header('Content-Type: application/json');
			echo "{\"status\": \"FATAL\", \"kind\": \"{$error['kind']}\", \"description\": \"".$error['description']."\"}";
		}
		exit();
	}
}

register_shutdown_function( "fatal_handler");

include ("conf/esa-config.php");
include ("modules/esa-db.php");
require __DIR__ . '/vendor/autoload.php';

$db = DB::getInstance();
$db->setConnectionParameters(ESA_DB_HOST ,ESA_DB_NAME, ESA_DB_USER, ESA_DB_PASSWORD);
$db->connect();

$params = array();

function addParam($key, $arrayIn, $arrayOut, $fullKeyName, $type) {
	if (array_key_exists($key, $arrayIn)) {
		if ($arrayIn[$key] != '') {
			if($type == 1) {
				$arrayOut[$fullKeyName] = $arrayIn[$key];
			} elseif($type == 2) {
				$arrayOut[$fullKeyName] = 1*($arrayIn[$key]);
			}
		}
	}
	return $arrayOut;
}

function getForeginId($type, $parameters, $db) {
	$types = array(
		"browser" => array(
			"searchColumn" => array("full_user_agent"),
			"searchKey" => array("userAgent"),
			"saveParamsKey" => array("browserName", "browserVersion", "browserType", "deviceCategory", "userAgent"),
			"saveColumn" => array("name", "version", "type", "category", "full_user_agent"),
			"type" => array("string", "string", "string", "string", "string")
		),
		"location" => array(
			"searchColumn" => array("ip", "countryCode"),
			"searchKey" => array("ip", "locationCountryCode"),
			"saveParamsKey" => array("ip", "host", "domain", "locationCountryName", "locationCountryCode", "locationCity", "locationLat", "locationLng"),
			"saveColumn" => array("ip", "host", "domain", "country", "countryCode", "city", "lat", "lng"),
			"type" => array("string", "string", "string", "string", "string", "string", "float", "float")
		),
		"os" => array(
			"searchColumn" => array("name", "version"),
			"searchKey" => array("os", "osVersion"),
			"saveParamsKey" => array("os", "osVersion"),
			"saveColumn" => array("name", "version"),
			"type" => array("string", "string")
		),
		"refferer" => array(
			"searchColumn" => array("url"),
			"searchKey" => array("refferer"),
			"saveParamsKey" => array("refferer", "reffererDomain", "reffererType"),
			"saveColumn" => array("url", "site", "type"),
			"type" => array("string", "string", "string")
		),
		"url" => array(
			"searchColumn" => array("url"),
			"searchKey" => array("url"),
			"saveParamsKey" => array("url", "urlHost", "urlPath", "urlQuery", "urlFragment"),
			"saveColumn" => array("url", "domain", "path", "query", "fragment"),
			"type" => array("string", "string", "string", "string", "string")
		),
		"events" => array(
			"saveParamsKey" => array("visitorId", "sessionId", "siteId", "locationId", "reffererId", "osId", "browserId", "urlId", "timestamp", "screenWidth", "screenHeight", "browserWidth", "browserHeight", "colorDepth", "pixelDepth", "pixelRatio"),
			"saveColumn" => array("visitor_id", "session_id", "site_id ", "location_id", "refferer_id", "os_id", "browser_id", "url_id", "timestamp", "screen_width", "screen_height", "browser_width", "browser_height", "color_depth", "pixel_depth", "pixel_ratio"),
			"type" => array("string", "string", "string", "string", "string", "string", "string", "string", "function", "string", "string", "string", "string", "string", "string", "string")
		),
		"errors" => array(
			"saveParamsKey" => array("siteId", "locationId", "osId", "browserId", "urlId", "timestamp", "type", "message", "file", "lineNo", "column_no"),
			"saveColumn" => array("site_id ", "location_id", "os_id", "browser_id", "url_id", "timestamp", "type", "message", "file", "line_no", "column_no"),
			"type" => array("string", "string", "string", "string", "string", "function", "string", "string", "string", "string", "string")
		)
	);
	if (array_key_exists("searchColumn", $types[$type])) {
		$searchCondition = '';
		$i = 0;
		foreach($types[$type]["searchColumn"] as $key => $value) {
			if ($i > 0) {
				$searchCondition = $searchCondition.' AND ';
			}
			$searchCondition = $searchCondition.' '.$value.' = \''.$parameters[$types[$type]["searchKey"][$i]].'\'';
			$i++;
		}
		$result = $db->query("select id from ".ESA_DB_PREFIX."_".$type." where $searchCondition");
		if ($db->count($result) > 0) {
			$row = $db->rowByNames($result);
			return $row['id'];
		}
	}

	$id = strval(time()).strval(mt_rand(100,999));
	$columns = '';
	$values = '';
	$i = 0;

	foreach($types[$type]["saveColumn"] as $key => $value) {
		if(array_key_exists($types[$type]["saveParamsKey"][$i], $parameters)) {
			if ($i > 0) {
				$columns = $columns.', ';
				$values = $values.', ';
			}
			$columns = $columns.$value;
			if($types[$type]["type"][$i] == 'string') {
				$values = $values.' \''.$parameters[$types[$type]["saveParamsKey"][$i]].'\'';
			} elseif($types[$type]["type"][$i] == 'float') {
				$values = $values.$parameters[$types[$type]["saveParamsKey"][$i]];
			} elseif($types[$type]["type"][$i] == 'function') {
				$values = $values.$parameters[$types[$type]["saveParamsKey"][$i]];
			}
		}
		$i++;
	}
	if($type == 'events' || $type == 'errors' ) {
		$siteKey = $parameters['siteId'];
		$query = "INSERT INTO ".ESA_DB_PREFIX."_".$type." (id, $columns) SELECT $id, $values WHERE EXISTS ( SELECT 1 FROM ".ESA_DB_PREFIX."_sites WHERE ".ESA_DB_PREFIX."_sites.key = '$siteKey')";
	} else {
		$query = "INSERT INTO ".ESA_DB_PREFIX."_".$type." (id, $columns) VALUES ($id, $values)";
	}
	
	$save = $db->query($query);
	return $id;

}

$params['timestamp'] = 'UTC_TIMESTAMP()';

// Basic request params 
$params = addParam('HTTP_USER_AGENT', $_SERVER, $params, 'userAgent', 1);
$params = addParam('REMOTE_ADDR', $_SERVER, $params, 'ip', 1);
$params = addParam('REMOTE_HOST', $_SERVER, $params, 'host', 1);
$params = addParam('X-Forwarded-For', $_SERVER, $params, 'proxy', 1);

// TODO: remove on prod
if (in_array($params['ip'], ESA_IP_EXCLUSIONS)) {
	exit();
}

// Basic analytcis params
$params = addParam('si', $_GET, $params, 'siteId', 1);
$params = addParam('iv', $_GET, $params, 'visitorId', 2);
$params = addParam('is', $_GET, $params, 'sessionId', 2);
$params = addParam('w', $_GET, $params, 'browserWidth', 2);
$params = addParam('h', $_GET, $params, 'browserHeight', 2);
$params = addParam('sw', $_GET, $params, 'screenWidth', 2);
$params = addParam('sh', $_GET, $params, 'screenHeight', 2);
$params = addParam('cd', $_GET, $params, 'colorDepth', 2);
$params = addParam('pd', $_GET, $params, 'pixelDepth', 2);
$params = addParam('pr', $_GET, $params, 'pixelRatio', 2);
$params = addParam('r', $_GET, $params, 'refferer', 1);
$params = addParam('u', $_GET, $params, 'url', 1);

// Errors loging
$params = addParam('e', $_GET, $params, 'type', 1);
$params = addParam('f', $_GET, $params, 'file', 1);
$params = addParam('c', $_GET, $params, 'colNo', 2);
$params = addParam('l', $_GET, $params, 'lineNo', 2);
$params = addParam('m', $_GET, $params, 'message', 1);
$params = addParam('u', $_GET, $params, 'url', 1);

// Url parsing
$urlElements = parse_url($params['url']);
$params = addParam('scheme', $urlElements, $params, 'urlScheme', 1);
$params = addParam('host', $urlElements, $params, 'urlHost', 1);
$params = addParam('path', $urlElements, $params, 'urlPath', 1);
$params = addParam('query', $urlElements, $params, 'urlQuery', 1);
$params = addParam('fragment', $urlElements, $params, 'urlFragment', 1);

//User Agent parsing
$browser = \hexydec\agentzero\agentzero::parse($params['userAgent']);

$params['os'] = $browser->platform;
$params['osVersion'] = $browser->platformversion;
$params['browserName'] = $browser->browser;
$params['browserVersion'] = $browser->browserversion;
$params['browserType'] = $browser->type;
$params['deviceCategory'] = $browser->category;

//IP geolocation 
use MaxMind\Db\Reader;

$geolocationDatabaseFile = './data/maxmind-db/GeoLite2-City.mmdb';
$reader = new Reader($geolocationDatabaseFile);
$locationData = $reader->get($params['ip']); // $params['ip']

if ($locationData != NULL) {
	$params['locationCountryCode'] = $locationData['country']['iso_code'];
	$params['locationCountryName'] = $locationData['country']['names']['en'];
	$params['locationCity'] = $locationData['city']['names']['en'];
	$params['locationLat'] = $locationData['location']['latitude'];
	$params['locationLng'] = $locationData['location']['longitude'];
}

if (!array_key_exists("host", $params)) {
	$params['host'] = dns_get_record($params['ip'], DNS_AAAA );
	if (count($params['host']) == 0) {
		$params['host'] = gethostbyaddr($params['ip']);
	} else {
		$params['host'] = $params['host'][0];
	}
}
$params['reffererId'] = 0;
if (array_key_exists("refferer", $params)) {
	//TODO: get refferer domain, get refferer type
	$params['reffererId'] = getForeginId("refferer", $params, $db);
}

$params['browserId'] = getForeginId("browser", $params, $db);
$params['locationId'] = getForeginId("location", $params, $db);
$params['osId'] = getForeginId("os", $params, $db);
$params['urlId'] = getForeginId("url", $params, $db);

//save visit event
if(array_key_exists("visitorId", $params)) {
	getForeginId("events", $params, $db);
} elseif(array_key_exists("type", $params)) {
	getForeginId("errors", $params, $db);
}

$db->close();