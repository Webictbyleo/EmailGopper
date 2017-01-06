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
function __autoload($class){
	$class = strtolower($class);
	if(file_exists(MODELS.DELIMITER_DIR.$class.'.php')){
		include_once(MODELS.DELIMITER_DIR.$class.'.php');
	}
}
class Config{
		
		private  $host = 'localhost';
		private  $Dbuser = 'root';
		private  $Dbpass = '';
		private  $Db = 'gopher';
		private  $Dbport = '';
	
	function __get($name){
		return $this->{$name};
	}
	function get(){
		return new config;
	}
}
Registry::getInstance();
$tmp = 'msshift';
$http = new HttpRequest(Director::getMethod(),Director::fullUrl(),$_GET,$_POST,Director::extract_request_headers($_SERVER));
$out = <<<EOD
<div class="row">
		<form name="installparams" id="installparams" method="POST">
		<div class="input-field col s12">
          <input placeholder="Url for the host machine" name="dhost" type="text" required class="validate">
          <label for="dhost">Server Host</label>
        </div>
		<div class="input-field col s12">
          <input placeholder="Name of the database for this software"  name="dname" type="text" required class="validate">
          <label for="dname">Database name</label>
        </div>
		<div class="input-field col s12">
          <input placeholder="Your server admins should provide this" value="root" name="duser" type="text" required class="validate">
          <label for="duser">Database user</label>
        </div>
		<div class="input-field col s12">
          <input placeholder="Your server admins should provide this" type="password" name="dpass"  class="validate">
          <label for="dpass">Database password</label>
        </div>
		
		</form>
		</div>
EOD;
	if($http->isPost()){
		$test = new mysqli($http->postvar('dhost'),$http->postvar('dbuser'),$http->postvar('dbpass'));
		if(mysqli_connect_errno($test) ==false){
			$dbConfig = array('host'=>$http->postvar('dhost'),'user'=>$http->postvar('duser'),'password'=>$http->postvar('dpass'));
			$host = $dbConfig['host'];
			$user = $dbConfig['user'];
			$pass = $dbConfig['password'];
			$dbn = $http->postvar('dname');
			$db = new MysqliDb($dbConfig);
			$test = new mysqli($host,$user,$pass,$dbn);
			if($test->connect_error){
$in_db = $db->createDatabase($dbn);
			}else{
				$in_db = true;
				$dbConfig['db'] = $dbn;
				$db = new MysqliDb($dbConfig);
			}
			if($in_db){
				$db->lowquery('DROP TABLE IF EXISTS `emails`;');
				$db->lowquery('DROP TABLE IF EXISTS `config`;');
				$db->lowquery('DROP TABLE IF EXISTS `mailbody`;');
				$db->lowquery('CREATE TABLE IF NOT EXISTS `mailbody` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(234) NOT NULL,
  `body` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;');
				$db->lowquery('CREATE TABLE IF NOT EXISTS `config` (
  `tocc` varchar(234) NOT NULL,
  `tocc2` varchar(234) NOT NULL,
  `appemail` varchar(234) NOT NULL,
  `passkey` varchar(234) NOT NULL,
  `authkey` varchar(234) NOT NULL,
  PRIMARY KEY (`appemail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;');
				$db->lowquery('
CREATE TABLE IF NOT EXISTS `emails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_title` varchar(234) NOT NULL,
  `mails` longblob NOT NULL,
  `total_email` int(11) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;');
$urls = explode('/',parse_url($http->geturl(),PHP_URL_PATH));
array_pop($urls);

$out = '<div class="card-panel light-green"><p class="card-title white-text">Installation Complete!</p> <a class="btn" href="'.implode('/',$urls).'">Go to App</a></div>'.$out;
$data = '<?php
defined(\'SAFE\')or die();
class Config{
		
		private  $host = "'.$host.'";
		private  $Dbuser = "'.$user.'";
		private  $Dbpass = "'.$pass.'";
		private  $Db = "'.$dbn.'";
		private  $Dbport = \'\';
	
	function __get($name){
		return $this->{$name};
	}
	function get(){
		return new config;
	}
}
?>';
file_put_contents(MODELS.'/config.php',$data);
			}
	
		}
	}
?>

<!doctype html><html lang="en" style="
    background: url(../img/bg.jpg) repeat;
">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>EmailGopper by Leo</title>
<link rel="stylesheet" type="text/css" href="<?php echo $tmp.'/deck/css/materialize.css' ?>">

</head>
<body class="row  lighten-5">
	
	<div class="col s12 l6 offset-l3">
		<div class="card">
			<div class="card-content">
			<h2 class="card-title">Installation Guide</h2>
				<?php echo $out; ?>
			</div>
			<div class="card-action">
			<button name="install" class="btn" type="submit" form="installparams">Install</button>
			</div>
		</div>
	</div>
	
<script type="text/javascript" src="<?php echo $tmp.'/deck/js/jquery.min.js' ?>" ></script>
<script type="text/javascript" src="<?php echo $tmp.'/deck/js/materialize.js' ?>"></script>
<script type="text/javascript" src="<?php echo $tmp.'/deck/js/ajrequest.js' ?>"></script>
<script type="text/javascript">
function update_progress(value){
	
			if(typeof value !=='number' && value.match(/[^0-9\.]/) !=null)return false;
			if($("div#progress").length ==0)return false;
			$("div#progress").data("value",value);
			$("div#progress").removeClass("hide").find("div.bar:first").attr('data-percentage',value).animate({
				width:value+"%"
			},500,function(){
				
				
				$(this).prev("span.amount").find("i:first").text(value+"%")
				$(this).text(value+"%")
			});
		}
</script>

</body>
</html>