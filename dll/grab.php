<?php
defined('SAFE')or die();
$res = $_SESSION['assets'];
if(!is_array($res))$res = array();
										$href = $i[2];
										
										$href_arg = (parse_url($href));
										if(!isset($href_arg['host'])){
											$href_arg['host'] = $baseurl['host'];
											$href_arg['scheme'] = $baseurl['scheme'];
										}
											if(isset($href_arg['host'])){
										$href_arg['host'] = str_ireplace('www.','',$href_arg['host']);
											}
													
										if(!isset($href_arg['host']) || (stripos($baseurl['host'],$href_arg['host'])) !==false){
												
											if((stripos($href,'http:') !==0 AND stripos($href,'https:')!==0)){
												$href = passer::nodots(passer::abs($href,$_SESSION['wlinksHead']));
												
														}
													
																//This is not an external link
																try{
																	
																	if($i[1] ===NULL){
																		//Indexpage
																$nf = dirname($logfile).'/site/home.html';
																$h = $j;
																
																	}else{
																		$nf = dirname($logfile).'/site/htm'.count($l).'.html';
																		$fetch = Requests::get($href,array(),array('timeout'=>5000,'connect_timeout'=>5000,'useragent'=>'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.93 Safari/537.36 OPR/32.0.1948.69 w2brfeeder/2.0'));
																	$h = jqm($fetch->body,'*');
																	}
												
													array_shift($l);
													$_SESSION['wlinks'] = $l;
														switch($i[0]){
															case 'a':
													
													
			$rs = $h->find('link[href][rel=stylesheet],script[src]:empty,img[src]')->map(function()use($nf){
				$n = $this->data('_name')->get();
				
				switch($n){
					case 'link':
					$href = file::inst($this->attr('href')->get())->path;
					return array($nf,key($this->data('_domId')->get()),passer::nodots(passer::abs($href,$_SESSION['wlinksHead'])));
					break;
					case 'script':
					case 'img':
					$href = file::inst($this->attr('src')->get())->path;
					return array($nf,key($this->data('_domId')->get()),passer::nodots(passer::abs($href,$_SESSION['wlinksHead'])));
					break;
				}
			});
			
			$res = array_merge($rs,$res);
			$_SESSION['assets'] = $res;
			
			file_put_contents($nf,$h->__toString(true));
			
			
			
															break;
															
														}
													
																}catch(Exception $e){
																	//Schedule for retry
																	
																	http_response_code(500);
																	$t = 100 / count($l);
																	die($e->getMessage());
																}
															}else{
																//Skip it
																array_shift($l);
													$_SESSION['wlinks'] = $l;
															}
												
															
														$t = 100 / count($l);
														
														if(count($l)==1){
																die("99");
															}
															if($t===false){
																die("100");
															}
													die(''.$t.'');
													
?>