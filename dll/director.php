<?php 

defined('SAFE')or die('Not allowed');

class Director{
public static $data;

private $tasks = array(
'profile',
);
protected static function getServer(){ 
$serverDefaults = array(
		'SERVER_PROTOCOL' => 'HTTP/1.1',
		'HTTP_ACCEPT' => 'text/plain;q=0.5',
		'HTTP_ACCEPT_LANGUAGE' => '*;q=0.5',
		'HTTP_ACCEPT_ENCODING' => '',
		'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1;q=0.5',
		'SERVER_SOFTWARE' => 'PHP/'.phpversion(),
		'SERVER_ADDR' => '127.0.0.1',
		'REMOTE_ADDR' => '127.0.0.1',
		'REQUEST_METHOD' => 'GET',
	);
	return $serverDefaults;
	}
public static function headers(){
$h = array(
'X-Powered-By'=>LEO_CMS_NAME.'/'.LEO_CMS_VERSION,
'Content-Type'=>'text/html',
);
return $h;
}
	// Get the Cms name and version
	public static function CMS_(){

	return LEO_CMS_NAME."\n".LEO_CMS_VERSION."\n";
	}




public static function fullUrl(){
$pro = self::protocol();
$host = self::protocolAndHost();
$server = $pro.$_SERVER['HTTP_HOST'];

if($server == 'localhost'){
$server = Director::protocolAndHost();
}
$params = (explode('/',$_SERVER['REQUEST_URI']));
$hostOnly = $server;
$params = array_filter($params);
$urlArgs = parse_url(implode('/',$params));

if(isset($urlArgs['path'])){
$query = $urlArgs['path'];
}

if(isset($urlArgs['query'])){
parse_str($urlArgs['query'],$REQUEST);
$query .= '?'.ArrayLib::toString($REQUEST);
}



 return $hostOnly.DELIMITER_DIR.$query;

}

public static function get_SubDomain($url =NULL){
		if(is_null($url)){
	$url = self::fullUrl();
		}
	if(self::is_subdomain($url)){
		$purl = parse_url($url);
		
			if(stripos($purl['host'],'www') ===0){
				$purl['host'] = substr($purl['host'],4);
			}
			$parts = substr($purl['host'],0,-(strlen(SYS_DOMAIN_HOST)));
			$pos = strripos($purl['host'],SYS_DOMAIN_HOST);
			return rtrim($parts,'.');
	}return NULL;
}


private function setMenu($menu){

}
//Get to the assets directory
public static function getAssets($assets,$type){
$assets = strtolower($assets);
$base = $_SERVER['DOCUMENT_ROOT'];
$asset_path = str_replace('\\','/',SITE.'media/');
	if(stripos($asset_path,$base) ===0){
$relate = substr($asset_path,strlen($base));
}else{
$relate = '/';
}

if(file_exists($asset_path.'/plugins/'.$assets.'/'.$assets.'.'.$type)){
return $relate.'plugins/'.$assets.'/'.$assets.'.'.$type;
}elseif(file_exists($asset_path.'/js/'.$assets.'.'.$type)){
return $relate.'js/'.$assets.'.'.$type;
}elseif(file_exists($asset_path.'/js/pages/'.$assets.'.'.$type)){
return $relate.'js/pages/'.$assets.'.'.$type;
}elseif(file_exists($asset_path.'/plugins/forms/'.$assets.'/'.$assets.'.'.$type)){
return $relate.'plugins/forms/'.$assets.'/'.$assets.'.'.$type;
}elseif(file_exists($asset_path.'/css/'.$assets.'.'.$type)){
return $relate.'css/'.$assets.'.'.$type;
}else return NULL;
}
//Check bot
public static function isBot(){
$getBots = file_get_contents(SITE.'/includes/bots.php');
$bots = explode(',',$getBots);

    foreach($bots as $spider) {
	$br = self::getBrowser();
        //If the spider text is found in the current user agent, then return true
        if ( stripos($_SERVER['HTTP_USER_AGENT'], $spider) !== false and empty($_SERVER['HTTP_USER_AGENT']) and isset($_SERVER['REMOTE_ADDR']) and empty($_SERVER['REMOTE_ADDR']) or strpos($br['browser'],$spider) ){$isbot=true;}else{$isbot = false;}
    }

	return $isbot;
}

public static function getBrowser(){

$fagents = $_SERVER['HTTP_USER_AGENT'];

$agents = array("msie","mozilla","chrome","opera");
if (preg_match("/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|sony|sympian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wirless|xda|xoom|zte|opera mobi|playbook)/i",$fagents,$match)){

return $droid = array("browser"=>$match[0],"isMobile"=>true);
} else {


return $droid = array("browser"=>$fagents,"isMobile"=>false);

}

}

//Check if site is ruuning live

public static function isLive(){
return true;
}
//Check if is in dev or admin mode
public static function isAdmin(){
$path = explode('/',$_SERVER['DOCUMENT_ROOT']);
$requrl = $_SERVER['REQUEST_URI'];
if(strpos($requrl,'//') ===0){
$requrl = substr($requrl,2);

}
$suri = parse_url($requrl);
//Useful when in direct url mode
$path2 = array_filter(explode('/',$suri['path']));
//remove index.php
if(end($path2) == BASE_SCRIPT_URL){
array_pop($path2);
}
$split = explode($_SERVER['HTTP_HOST'],$_SERVER['REQUEST_URI']);
if(isset($_REQUEST['token']) and end($path)=='admin'){ 
return true;
}elseif(strtolower(end($path2)) == 'admin'){
return true;
}elseif(WebBaker::get_package_controller() =='administrator'){
return true;
}elseif(strpos($split[0],'admin')==1){
return true;
}else
return false;
}

//Check if site is runnking on https
public static function isHTTPS(){
if(isset($_SERVER['HTTP_X_FORWARDED_PROTOCOL'])) { 
			if(strtolower($_SERVER['HTTP_X_FORWARDED_PROTOCOL']) == 'https') {
				return true;
	}
	}
	
		if((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')) {
			return true;
	}
		else if(isset($_SERVER['SSL'])) {
			return true;
		}

		return false;
}
//check if ajax
public static function isAJAX(){
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest'){

return true;}else{ return false;}
}



//Get all the subclasses of a class
public static function subClass($parent){
$result;
foreach(get_declared_classes() as $class){
if(!is_subclass_of($class,$parent))continue;
	$result[] = $class;
}

return $result;
}
//Get the app view of an app

public static function xparam(){
$URLparams = parse_url($_SERVER['REQUEST_URI']);
parse_str($URLparams['query']);
return ('&token='.$token.'&pass='.$pass);
}



	public static function absoluteURL($url, $relativeToSiteBase = false) {
		if(!isset($_SERVER['REQUEST_URI'])) return false;
		
		if(strpos($url,'/') === false && !$relativeToSiteBase) {
			
			$url = dirname($_SERVER['REQUEST_URI'] . 'x').'/'. $url;
		
				if(strpos($url,'\\') !==false){
				$url = str_replace('\\','/',$url);
				}
				$url = implode('/',array_filter(explode('/',$url)));
			
		}

		if(substr($url,0,4) != "http") {
			if($url[0] != "/") $url = Director::baseURL()  .''. $url;
			// Sometimes baseURL() can return a full URL instead of just a path
			if(substr($url,0,4) != "http") $url = self::protocolAndHost() . $url;
			
		}

		return $url;
	}
	public static function absoluteBaseURL() {
		return Director::absoluteURL(Director::baseURL());
	}
	/**
	 * Turns an absolute URL or folder into one that's relative to the root of 
	 * the site. This is useful when turning a URL into a filesystem reference, 
	 * or vice versa.
	 * 
	 * @param string $url Accepts both a URL or a filesystem path
	 * @return string Either a relative URL if the checks succeeded, or the 
	 * original (possibly absolute) URL.
	 */
	public static function makeRelative($url) {
		// Allow for the accidental inclusion whitespace and // in the URL
		$url = trim(preg_replace('#([^:])//#', '\\1/', $url));

			$base1 = self::absoluteBaseURL();
		$baseDomain = substr($base1, strlen(self::protocol()));

		// Only bother comparing the URL to the absolute version if $url looks like a URL.
		if(preg_match('/^https?[^:]*:\/\//',$url,$matches)) {
			$urlProtocol = $matches[0];
			$urlWithoutProtocol = substr($url, strlen($urlProtocol));

			// If we are already looking at baseURL, return '' (substr will return false)
			if($url == $base1) {
				return '';
			}
			else if(substr($url,0,strlen($base1)) == $base1) {
				return substr($url,strlen($base1));
			}
			else if(substr($base1,-1)=="/" && $url == substr($base1,0,-1)) {
			// Convert http://www.mydomain.com/mysitedir to ''
				return "";
		}
		
			if(substr($urlWithoutProtocol,0,strlen($baseDomain)) == $baseDomain) {
				return substr($urlWithoutProtocol,strlen($baseDomain));
			}
		}
	
		// test for base folder, e.g. /var/www
		$base2 = self::baseFolder();
			
		if(substr($url,0,strlen($base2)) == $base2) return substr($url,strlen($base2));

		// Test for relative base url, e.g. mywebsite/ if the full URL is http://localhost/mywebsite/
		$base3 = self::baseURL();
		if(substr($url,0,strlen($base3)) == $base3) {
			return substr($url,strlen($base3));
		}
		//Test for realpath
		//System root/path/file
		
		if(stripos(func_get_arg(0),$base2) !==false){
			return substr(func_get_arg(0),strlen($base2));
		}
		
		// Test for relative base url, e.g mywebsite/ if the full url is localhost/myswebsite
		if(substr($url,0,strlen($baseDomain)) == $baseDomain) {
			return substr($url, strlen($baseDomain));
		}

		// Nothing matched, fall back to returning the original URL
		return $url; 
	}
	public static function protocol(){
	$host = 'http';
if(Director::isHTTPS())$host .= 's';
return $host.'://';
	}
	
	function seekImport($class){
	$s = $class;
	if(strpos($s,DELIMITER_DOT)!==false){$del = DELIMITER_DOT;}elseif(strpos($s,DELIMITER_UNDER) !==false){$del = DELIMITER_UNDER;}elseif(
strpos($s,DELIMITER_DIR !==false)){
$del = DELIMITER_DIR;
}
$fdir = explode($del,$s);

if(Director::isAdmin())$admin = BASE;else $admin = BASE.'/admin/';
$directive = array(
'SITE'=>SITE,
'ADMIN'=>$admin,
);
if(array_key_exists(current($fdir),$directive)) 

//Replace the first directive
$fdir[0] = $directive[current($fdir)];


//Now to avoid conflicting path specific files ,find the last two ext
$ext = substr($s,strpos($s,end($fdir)));
//Make sure that file extension is intact
$lib = prev($fdir);
//Get the array keys
$fkey = array_keys($fdir);
if($fdir[1] == 'libraries' and $lib =='lib'){
$fdir[end($fkey	)] = NULL;
$fdir = array_filter($fdir);

$fdir[array_search($lib,$fdir)] = NULL;
$fdir = array_filter($fdir);
$ff = end($fdir);
$append = end($fdir).DELIMITER_DIR.$ff.DELIMITER_DOT.$lib.DELIMITER_DOT.$ext;
}else{
$fdir[end($fkey)] = NULL;
$fdir = array_filter($fdir);
$ff = end($fdir);
$fdir[array_search($lib,$fdir)] = NULL;
$append = end($fdir).DELIMITER_DIR.$ff.DELIMITER_DOT.$ext;

}


foreach($fdir as $fi){
if($fi != end($fdir))
$realPath[] = $fi.'/';
}
$file = implode('',$realPath).$append;



$isPhp = ((strtolower($ext)=='php'));

if($isPhp){
require_once($file);
}else{
return file_get_contents($file);
}
	}
		/**
	 * Takes a $_SERVER data array and extracts HTTP request headers.
	 *
	 * @param  array $data
	 * @return array
	 */
	public static function extract_request_headers(array $server) {
		$headers = array();
	
		foreach($server as $key => $value) {
			if(substr($key, 0, 5) == 'HTTP_') {
				$key = substr($key, 5);
				$key = strtolower(str_replace('_', ' ', $key));
				$key = str_replace(' ', '-', ucwords($key));
				$headers[$key] = $value;
			}
		}
	
		if(isset($server['CONTENT_TYPE'])) $headers['Content-Type'] = $server['CONTENT_TYPE'];
		if(isset($server['CONTENT_LENGTH'])) $headers['Content-Length'] = $server['CONTENT_LENGTH'];
	
		return $headers;
	}
		/**
	 * Returns true if this script is being run from the command line rather than the webserver.
	 * 
	 * @return boolean
	 */
	public static function is_cli() {
		return (php_sapi_name() == "cli");
	}
	
	
	//Check of we are in test mode
	public static function isTest($skipDatabase = false){
	return false;
	}
	public static function baseFolder() {
	
		return dirname(dirname(dirname(__FILE__)));
	}
	public static function protocolAndHost(){
	return absolute_path($_SERVER['PHP_SELF'] );
	
	}
			public static function baseURL() {
			$path = realpath($_SERVER['SCRIPT_FILENAME']);
		$BASE_PATH = rtrim(dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);
	
	if(substr($path, 0, strlen($BASE_PATH)) == $BASE_PATH) {
		$urlSegmentToRemove = substr($path, strlen($BASE_PATH));
		if(substr($_SERVER['SCRIPT_NAME'], -strlen($urlSegmentToRemove)) == $urlSegmentToRemove) {
			$baseURL = substr($_SERVER['SCRIPT_NAME'], 0, -strlen($urlSegmentToRemove));
			
		}
	}
	
		if($alternate) {
		
			return $alternate;
		} else {
			$base = self::BASE_URL();
			
			if($base == '/' || $base == '/.' || $base == '\\') {
				$baseURL = '/';
			} else {
				$baseURL = $base . '/';
		}
			
			if(defined('BASE_SCRIPT_URL')) {
				return $baseURL;
	}
	
			return $baseURL;
		}
	}
	public static function BASE_URL(){
	// Define base path
	$candidateBasePath = rtrim(dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);
	if($candidateBasePath == '') $candidateBasePath = DIRECTORY_SEPARATOR;
	$BASE_PATH = $candidateBasePath;
	
	$path = realpath($_SERVER['SCRIPT_FILENAME']);
	if(substr($path, 0, strlen($BASE_PATH)) == $BASE_PATH) {
	
		$urlSegmentToRemove = substr($path, strlen($BASE_PATH));
		if(substr($_SERVER['SCRIPT_NAME'], -strlen($urlSegmentToRemove)) == $urlSegmentToRemove) {
			$baseURL = substr($_SERVER['SCRIPT_NAME'], 0, -strlen($urlSegmentToRemove));
			$baseF = rtrim($baseURL, DIRECTORY_SEPARATOR);
			
		}
	}
	
	return $baseF;
	}
		/**
	 * Checks if a given URL is absolute (e.g. starts with 'http://' etc.).
	 * URLs beginning with "//" are treated as absolute, as browsers take this to mean
	 * the same protocol as currently being used. 
	 * 
	 * Useful to check before redirecting based on a URL from user submissions
	 * through $_GET or $_POST, and avoid phishing attacks by redirecting
	 * to an attackers server.
	 * 
	 * Note: Can't solely rely on PHP's parse_url() , since it is not intended to work with relative URLs
	 * or for security purposes. filter_var($url, FILTER_VALIDATE_URL) has similar problems.
	 * 
	 * @param string $url
	 * @return boolean
	 */
	public static function is_absolute_url($url) {
		// Strip off the query and fragment parts of the URL before checking
		if(($queryPosition = strpos($url, '?')) !== false) {
			$url = substr($url, 0, $queryPosition-1);
		}
		if(($hashPosition = strpos($url, '#')) !== false) {
			$url = substr($url, 0, $hashPosition-1);
		}
		$colonPosition = strpos($url, ':');
		$slashPosition = strpos($url, '/');
		return (
			// Base check for existence of a host on a compliant URL
			parse_url($url, PHP_URL_HOST)
			// Check for more than one leading slash without a protocol.
				// While not a RFC compliant absolute URL, it is completed to a valid URL by some browsers,
				// and hence a potential security risk. Single leading slashes are not an issue though.
			|| preg_match('%^\s*/{2,}%', $url)
			|| (
				// If a colon is found, check if it's part of a valid scheme definition
				// (meaning its not preceded by a slash).
				$colonPosition !== FALSE 
				&& ($slashPosition === FALSE || $colonPosition < $slashPosition)
			)
		);
	}
	public static function forceSSL($patterns = null, $secureDomain = null) {
		if(!isset($_SERVER['REQUEST_URI'])) return false;
		
		$matched = false;

		if($patterns) {
			// Calling from the command-line?
			if(!isset($_SERVER['REQUEST_URI'])) return;

			$relativeURL = self::makeRelative(Director::absoluteURL($_SERVER['REQUEST_URI']));

			// protect portions of the site based on the pattern
			foreach($patterns as $pattern) {
				if(preg_match($pattern, $relativeURL)) {
					$matched = true;
					break;
				}
			}
		} else {
			// protect the entire site
			$matched = true;
		}

		if($matched && !self::isHTTPS()) {

			// if an domain is specified, redirect to that instead of the current domain
			if($secureDomain) {
				$url = 'https://' . $secureDomain . $_SERVER['REQUEST_URI'];
			} else {
				$url = $_SERVER['REQUEST_URI'];
			}

			$destURL = str_replace('http:', 'https:', Director::absoluteURL($url));

			// This coupling to SapphireTest is necessary to test the destination URL and to not interfere with tests
			if(class_exists('SapphireTest', false) && SapphireTest::is_running_test()) {
				return $destURL;
			} else {
				self::force_redirect($destURL);
			}
		} else {
			return false;
		}
	}
		/**
	 * Force a redirect to a domain starting with "www."
	 */
	public static function forceWWW() {

			$destURL = str_replace(self::protocol(),'http://www.',Director::absoluteURL($_SERVER['REQUEST_URI']));
			
			self::force_redirect($destURL);
			
		
	}
	public static function force_redirect($destURL) {
		$response = new HTTPResponse(
			"<h1>Your browser is not accepting header redirects</h1>".
			"<p>Please <a href=\"$destURL\">click here</a>",
			301
		);

		
		$response->addHeader(array('Location'=>$destURL));
		
		// TODO: Use an exception - ATM we can be called from _config.php, before Director#handleRequest's try block
		$response->output();
		die;
	}
		/**
	 * Checks if a given URL is relative by checking {@link is_absolute_url()}.
	 * 
	 * @param string $url
	 * @return boolean
	 */
	public static function is_relative_url($url) {
			
		return (Director::is_absolute_url($url) ===false);
	}
	
	/**
	 * Checks if the given URL is belonging to this "site" (not an external link).
	 * That's the case if the URL is relative, as defined by {@link is_relative_url()},
	 * or if the host matches {@link protocolAndHost()}.
	 * 
	 * Useful to check before redirecting based on a URL from user submissions
	 * through $_GET or $_POST, and avoid phishing attacks by redirecting
	 * to an attackers server.
	 * 
	 * @param string $url
	 * @return boolean
	 */
	public static function is_site_url($url) {
		$urlHost = parse_url($url, PHP_URL_HOST);
		$actualHost = parse_url(self::protocolAndHost(), PHP_URL_HOST);
		if($urlHost && $actualHost && $urlHost == $actualHost) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Given a filesystem reference relative to the site root, return the full file-system path.
	 * 
	 * @param string $file
	 * @return string
	 */
	public static function getAbsFile($file) {
		return self::is_absolute($file) ? $file : Director::baseFolder() . '/' . $file;
	}
	
	/**
	 * Returns true if the given file exists.
	 * @param $file Filename specified relative to the site root
	 */
	public static function fileExists($file) {
		// replace any appended query-strings, e.g. /path/to/foo.php?bar=1 to /path/to/foo.php
		$file = preg_replace('/([^\?]*)?.*/','$1',$file);
		return file::getInst(Director::getAbsFile($file))->exists();
	}
	/**
	 * Returns true if a given path is absolute. Works under both *nix and windows
	 * systems
	 *
	 * @param string $path
	 * @return bool
	 */
	public static function is_absolute($path) {
		if($path[0] == '/' || $path[0] == '\\') return true;
		return preg_match('/^[a-zA-Z]:[\\\\\/]/', $path) == 1;
	}
	
	public static function getMethod(){
	
	return $_SERVER['REQUEST_METHOD'];
	}
	
	public static function isSEO(){
	if(isset($_REQUEST['app']) and isset($_REQUEST['view'])){
	return false;
	}
	return true;
	}
	//Used for tasking
		public function get_tasks(){
$er = get_class_vars(__CLASS__);
return $er['tasks'];
}
	public function meta($task){
	$d = array(
	'file'=>'Director',
	'line'=>648,
	'class'=>new Director,
	);
	$meta = (object)array('file'=>$d['file'],'line'=>$d['line'],'task'=>$task,'object'=>$d['class']);

	return $meta;
	}


}





?>