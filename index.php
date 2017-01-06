<?php
define("DELIMITER_DIR",'/');
define("DELIMITER_UNDER",'_');
define("SAFE",true);
define("Oparrot",true);
define('SITE',__DIR__);
define('VIEW',SITE.'/msshift/');
define('MODELS',SITE.'/dll/');
define('ZEND',SITE.'/dll/zend/');
 $paths = array(
MODELS
);
set_include_path(
implode(PATH_SEPARATOR,$paths)
);
error_reporting(E_ERROR | E_USER_ERROR | E_PARSE);


function increase_time_limit_to($timeLimit = null) {
	$max = get_increase_time_limit_max();
	if($max != -1 && $timeLimit > $max) return false;
	
	if(!ini_get('safe_mode')) {
		if(!$timeLimit) {
			set_time_limit(0);
			return true;
		} else {
			$currTimeLimit = ini_get('max_execution_time');
			// Only increase if its smaller
			if($currTimeLimit && $currTimeLimit < $timeLimit) {
				set_time_limit($timeLimit);
			} 
			return true;
		}
	} else {
		return false;
	}
}

$_increase_time_limit_max = -1;

 
function set_increase_time_limit_max($timeLimit) {
	global $_increase_time_limit_max;
	$_increase_time_limit_max = $timeLimit;
}

function get_increase_time_limit_max() {
	global $_increase_time_limit_max;
	return $_increase_time_limit_max;
}
function absolute_path($path){
$CMS_URL_PARAMS = parse_url($_SERVER['REQUEST_URI']);
$host = 'http';
if(Director::isHTTPS())$host .= 's';
$output =$host.'://'.$_SERVER['HTTP_HOST'];
return $output;
}
if (!function_exists('http_response_code')) {
        function http_response_code($code = NULL) {
	$prev_code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

        if ($code === NULL) {
            return $prev_code;
        }
            if ($code !== NULL) {

                switch ($code) {
                    case 100: $text = 'Continue'; break;
                    case 101: $text = 'Switching Protocols'; break;
                    case 200: $text = 'OK'; break;
                    case 201: $text = 'Created'; break;
                    case 202: $text = 'Accepted'; break;
                    case 203: $text = 'Non-Authoritative Information'; break;
                    case 204: $text = 'No Content'; break;
                    case 205: $text = 'Reset Content'; break;
                    case 206: $text = 'Partial Content'; break;
                    case 300: $text = 'Multiple Choices'; break;
                    case 301: $text = 'Moved Permanently'; break;
                    case 302: $text = 'Moved Temporarily'; break;
                    case 303: $text = 'See Other'; break;
                    case 304: $text = 'Not Modified'; break;
                    case 305: $text = 'Use Proxy'; break;
                    case 400: $text = 'Bad Request'; break;
                    case 401: $text = 'Unauthorized'; break;
                    case 402: $text = 'Payment Required'; break;
                    case 403: $text = 'Forbidden'; break;
                    case 404: $text = 'Not Found'; break;
                    case 405: $text = 'Method Not Allowed'; break;
                    case 406: $text = 'Not Acceptable'; break;
                    case 407: $text = 'Proxy Authentication Required'; break;
                    case 408: $text = 'Request Time-out'; break;
                    case 409: $text = 'Conflict'; break;
                    case 410: $text = 'Gone'; break;
                    case 411: $text = 'Length Required'; break;
                    case 412: $text = 'Precondition Failed'; break;
                    case 413: $text = 'Request Entity Too Large'; break;
                    case 414: $text = 'Request-URI Too Large'; break;
                    case 415: $text = 'Unsupported Media Type'; break;
                    case 500: $text = 'Internal Server Error'; break;
                    case 501: $text = 'Not Implemented'; break;
                    case 502: $text = 'Bad Gateway'; break;
                    case 503: $text = 'Service Unavailable'; break;
                    case 504: $text = 'Gateway Time-out'; break;
                    case 505: $text = 'HTTP Version not supported'; break;
                    default:
                        user_error("Unrecognised HTTP status code '$code'", E_USER_WARNING);
                    break;
                }

                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

                header($protocol . ' ' . $code . ' ' . $text);

                $GLOBALS['http_response_code'] = $code;

            } else {

                $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

            }

            return $code;

        }
    }
function __autoload($class){
	$class = strtolower($class);
	if(file_exists(MODELS.DELIMITER_DIR.$class.'.php')){
		include_once(MODELS.DELIMITER_DIR.$class.'.php');
	}
}
require_once(MODELS.'/config.php');
if(!file_exists(MODELS.'/config.php')){
	director::force_redirect('/install.php');
}
Registry::getInstance();
		$app = new stdclass;
		session_start();
		$app->db = mspdo::getInstance();
		$app->config = $app->db->table('config')->fetchAll(NULL,NULL,1);
		
		$app->request = new HttpRequest(Director::getMethod(),Director::fullUrl(),$_GET,$_POST,Director::extract_request_headers($_SERVER));
	//require_once(MODELS.'auth.php');
	require_once(MODELS.'controller.php');
	require_once(VIEW.'view.php');
		
?>