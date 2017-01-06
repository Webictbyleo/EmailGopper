<?php
defined('SAFE')or die();
	
		class cookie{
			
			function __set($n,$v){
				$this->set($n,$v);
		}
			function set($n,$v){
				
				if(is_numeric($n) || !empty($v)){
				$_COOKIE[$n] = $v;
				setcookie($n,$v,NULL,'/');
				$this->{$n} = $v;
				
				}
				
				
			}
			function __unset($n){
				unset($this->{$n});
				unset($_COOKIE[$n]);
				setcookie ($n, "", time() - 3600);
			}
		}
	$app->cookie = new cookie;
		
		
		parse_str(str_replace('; ','&',$_SERVER['HTTP_COOKIE']),$cookies);
		
		foreach($cookies as $k => $v){
				
				$app->cookie->set($k,$v);
			
		}
		
		$key = trim($app->request->getvar('passkey'));
		if(!isset($app->cookie->passkey)){
			$app->cookie->passkey = NULL;
		}else{
			if($app->config->current()->passkey !== $app->cookie->passkey){
				$app->cookie->passkey = NULL;
				http_response_code(404);
			}
		}
			
			
			
				if($app->cookie->passkey===NULL AND empty($key)){
				http_response_code(404);
					exit();
					}
					if(!empty($key) AND $app->config->current()->passkey !=$key){
				http_response_code(404);
					exit();
					}
			if($key===$app->config->current()->passkey){
				
				$app->cookie->passkey = $app->config->current()->passkey;
			}
			http_response_code(200);
	if(empty($_SERVER['PHP_AUTH_DIGEST']) ===true AND  is_null($app->cookie->ESANT_PL_AUTH_ADMIN_REALM)===true){
		header('HTTP/1.0 401 Unauthorized');
				header('WWW-Authenticate:Digest realm="Restricted area",qop="auth",nonce="'.uniqid().'",opaque="'.md5('Restricted area').'" ');
			
			exit('Not Found');
	}
	function parse_auth_var($txt){
			$required = array(
			'nonce'=>1,'nc'=>1,'cnonce'=>1,'qop'=>1,'username'=>1,'uri'=>1,'response'=>1,
				);
				$data = array();
				//Stringify the keys
				$keys = implode('|',array_keys($required));
				preg_match_all('@('.$keys.')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@',$txt,$matches,PREG_SET_ORDER);
				foreach($matches as $m){
				$data[$m[1]] = $m[3] ? $m[3] : $m[4];
				unset($required[$m[1]]);
				}
				return $required ? false : $data;
			}
			$users = array($app->config->current()->appemail=>$app->config->current()->authkey);
			$data = parse_auth_var($_SERVER['PHP_AUTH_DIGEST']);
			$A = md5($data['username'].':Restricted area:'.$users[$data['username']]);
				
				$B = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
				$valid = (md5($A.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$B) === $data['response']);
			if(!($data) || ($data['username'] !== $app->config->current()->appemail)){
				exit('Not Found');
			}
				if($valid !==true){
					http_response_code(404);
					exit('Not Found');
						
				}
				$app->cookie->set('ESANT_PL_AUTH_ADMIN_REALM',$app->config->current()->authkey);
	
?>