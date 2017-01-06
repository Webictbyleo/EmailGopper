<?php
defined('SAFE')or die();
//Normalize url only
														
															$tt = count($l);
															
															for($k=0;$tt > $k; $k++){
																$href = $l[$k][2];
																
														$hst = parse_url($href,PHP_URL_SCHEME);
														if(empty($hst)){
															
														$href = passer::nodots(passer::abs($href,$_SESSION['wlinksHead']));
														
														$id = $l[$k][1];
														$ele = $j->find($l[$k][0].'[data-dom-id="'.$id.'"]:first');
														
															if($ele->length > 0){
																if(in_array(strtolower($l[$k][0]),array('script','img'))){
																	
																	$ele->attr('src',$href);
																	
																}else{
																	$ele->attr('href',$href);
																}
																
																
															}else{
														
													
															}
														}else{
															//Skip
															
														}
														unset($l[$k]);
													
															}
															$_SESSION['wlinks'] = $l;
															$nf = dirname($logfile).'/site/home.html';
																
																
															file_put_contents($nf,$j->__toString(true));
															file_put_contents($logfile,$j->__toString(true));
														die("100");
													
												$t = (100 / count($l));
												
												die(''.$t.'');

?>