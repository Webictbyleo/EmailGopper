<?php
defined('SAFE')or die();
//rename($logfile,dirname($logfile).'/site/index.html');
$dir = file::inst(dirname($logfile).'/site/');

$htm = glob(dirname($logfile).'/site/*[a-z0-9]*.html',GLOB_BRACE);
//Asset collection
	if(is_array($htm)){
		$res = $_SESSION['assets'];
		foreach($htm as $h){
			
			${$h} = jqm(file_get_contents($h),'*');
			
		}
		
			if(is_array($res)){
				$stak = array();
				foreach($res as $k => $v){
					
					$stak[] = $v[2];
					$h = ${$v[0]};
					if(is_a($h,jqmdoc)){
						$el = $h->find('[data-dom-id="'.$v[1].'"]:first');
						if($el->length){
							$ns = $el->data('_name')->get();
							if($ns == 'link'){
								$v[2] = current(explode('?',$v[2]));
								$rp = 'css/'.basename($v[2]);
								$el->attr('href',$rp);
								
							}elseif($ns ==='script'){
								$v[2] = current(explode('?',$v[2]));
								$rp = 'js/'.basename($v[2]);
								$el->attr('src',$rp);
							}elseif($ns ==='img'){
								
								$rp = 'img/'.basename($v[2]);
								$el->attr('src',$rp);
							}
							if(in_array($res[$k],$stak)){unset($res[$k]);continue;}
							$streamTo = $dir->path.'/'.$rp;
							
							$fetch = Requests::get(passer::resolve($v[2]),array(),array('timeout'=>5000,'connect_timeout'=>5000,'useragent'=>'Mozilla/5.0 (Windows NT 6.2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.93 Safari/537.36 OPR/32.0.1948.69 w2brfeeder/2.0','filename'=>$streamTo));
								
						}
					}
				}
				
				//Save back to consolidate
				foreach($htm as $h){
			
			$doc = ${$h};
			if(is_a($doc,jqmdoc)){
				file_put_contents($h,$doc->__toString(true));
			}
			
		}
				
			}
		
	}
	$ch = $dir->children;
	$ch = array_merge(file::inst($dir->path.'/css')->children,$ch);
	$ch = array_merge(file::inst($dir->path.'/js')->children,$ch);
	$ch = array_merge(file::inst($dir->path.'/img')->children,$ch);
	$c = count($ch);
	
	for($i=0;$c > $i;$i++){
		$f = $ch[$i];
			if($f->is_dir){
				
			}else{
		$files[] = $f->path;
			}
		
	}
	
	
														$zp = new zipper;
														$zp->addFileList($files,$dir->path.'/');
														$savel = dirname($logfile).'/'.$nmz.'.zip';
														
														$zp->save($savel);
														
														$savel = $app->request->getUrl().'/tmp/'.$nmz.'/'.$nmz.'.zip';
														$r = array('dll'=>$savel,'viewll'=>$app->request->getUrl().'/tmp/'.$nmz.'/site/index.html');
														
														$dir->delete();
														unlink($logfile);
														unset($_SESSION['wlinks']);
														unset($_SESSION['wlinksHead']);
														die(json_encode($r));
?>