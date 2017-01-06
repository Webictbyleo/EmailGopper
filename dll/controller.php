<?php 
defined('SAFE')or die();
$p = $app->request->getvar('p');
$pages = array(
'Generate Emails <i class="mdi-communication-email"></i>'=>'gmail',
'Site page Wizard'=>'sitescrap',
'Organize lists'=>'emaillist',
'Body templates'=>'mailbody',
'Settings'=>'config',
'Compose Email'=>'cgmail'
);
	if(!empty($p) AND in_array($p,$pages)){
		//Process page
		$title = array_search($p,$pages);
		$app->view = '<h4 class="pagetitle white-text">'.$title.'</h4><hr>';
		switch($p){
			case 'cgmail':
			$isposted = (Director::isAjax() AND $app->request->isPost());
			
			if($isposted){
				$vars = $app->request->postvars();
				$use_bd_tpl = (isset($vars['use_bd_template']) AND !empty($vars['use_bd_template']));
				$use_list_tpl = (isset($vars['use_email_list']) AND !empty($vars['use_email_list']));
				
					if($use_bd_template){
						//Get email body
						if(empty($vars['email_body'])){
							//Error
							http_response_code(500);
							die('Please select email body');

						}
						$table = $app->db->table('mailbody');
						$get = $table->fetchAll('title="'.$vars['email_body'].'"');
						$body = $get->current()->body;
					}else{
						$body = $vars['email_body'];
					}
					
					if($use_list_tpl){
						if(empty($vars['recipient_list'])){
							http_response_code(500);
							die('Please select recipients');
						}
						//get emails
						$table = $app->db->table('emails');
						$get = $table->fetchAll('list_title="'.$vars['recipient_list'].'"');
						$to = explode(',',$get->current()->mails);
						
					}else{
						$to = trim($vars['recipient_list'],"\n\r\s");
						$to = explode(',',$to);
						$tt = count($to);
						for($i=0;$tt > $i;$i++){
							
							if(!preg_match('/[\w.-]+@[\w.-]+\.[a-zA-Z]{2,4}/',$to[$i])){
								
								unset($to[$i]);
							}
						}
						$to = array_values($to);
						
					}
					
					$subject = $vars['email_subject'];
						if(empty($subject)){
							http_response_code(500);
							die('Invalid subject');
						}
					$mailer = new email;
					$mailer->setRecipients($to);
					$mailer->setSubject($subject);
					$mailer->setmessage($body);
					$from = NULL;
					if($app->config->current()){
							if(!empty($app->config->current()->appemail)){
						$from = $app->config->current()->appemail;
							}
					}
					$send = $mailer->push(false,$from);
					
					//Send email here
					if($use_bd_tpl==false AND isset($vars['save_bd_template'])){
						if(!empty($vars['save_bd_template_title'])){
							//Save new body template
								$title = strip_tags($vars['save_bd_template_title']);
								
							$table = $app->db->table('mailbody');
								$get = $table->fetchAll('title="'.$title.'"');
								
								if($get->count() > 0){
									$title .= time();
								}
								
							$table->insert(array('title'=>$title,
							'body'=>$body
							));
						}else{
							http_response_code(500);
							//Throw error for invalid title
							die('Provide a valid body template title');
						}
					}
					
					
					if($use_list_tpl==false AND isset($vars['save_recipient_list'])){
						if(!empty($vars['save_recipient_list_title'])){
							
							//Save new email list
							$title = strip_tags($vars['save_recipient_list_title']);
							$table = $app->db->table('emails');
							$get = $table->fetchAll('list_title="'.$title.'"');
							if($get->count() > 0){
									$title .= time();
								}
							$table->insert(array('list_title'=>$title,
							'mails'=>implode(',',$to),'total_email'=>count($to)
							));
						}else{
							//Throw error for invalid title
							http_response_code(500);
							die('Provide a valid email list title');
						}
					}
				die("ok");
			}
			$lists = $app->db->rawquery('SELECT `list_title` AS title FROM emails');
				$lt = count($lists);
				if($lt > 0){
				for($i=0;$lt > $i;$i++){
					$lists[$i] = '<option>'.$lists[$i]['title'].'</option>';
				}
				$lists = implode("\n",$lists);
				}else{
					$lists = '';
				}
				
			$body_tpl = $app->db->rawquery('SELECT `title` FROM mailbody');
				$lt = count($body_tpl);
				if($lt > 0){
				for($i=0;$lt > $i;$i++){
					$body_tpl[$i] = '<option>'.$body_tpl[$i]['title'].'</option>';
				}
				$body_tpl = implode("\n",$body_tpl);
				}else{
					$body_tpl = '';
				}
			
				$app->view .= <<<EOT
				<div class="card z-depth-1 row">
					<div class="card-content">
							<form id="compose">
								<div class="col s12">
							<div class="input-field col s10">
          <input name="email_subject"  type="text" required class="validate">
          <label>Subject</label>
        </div>
		<div class="input-field col s10">
          <input name="email_sendername"  type="text" required class="validate">
          <label>Sender name</label>
        </div>
		</div>
							<p>
      <input type="checkbox" name="use_bd_template" data-toggle-fieldset="body_template" id="test6"  />
      <label for="test6">Use template</label>
    </p>
		<fieldset>
			<div class="input-field col s12">
          <i class="mdi-content-create prefix"></i>
          <textarea name="email_body" data-required id="icon_prefix" type="text" class="validate materialize-textarea"></textarea>
          
        </div>
		<p>
		<input type="checkbox" name="save_bd_template" id="test7" checked="checked" />
      <label for="test7">Save as template</label>
	  </p>
	  <div class="input-field col s6">
          <input name="save_bd_template_title"  type="text" class="validate">
          <label>Template name</label>
        </div>
		</fieldset>
		<fieldset id="body_template">
		<div class="input-field col s12">
          <select class="validate" name="email_body" data-required>
		  <option selected value="">Select template</option>
			$body_tpl
		  </select>
          <label>Email templates <i class="mdi-action-print suffix"></i></label>
        </div>
		</fieldset>
		<div>
      <input type="checkbox" name="use_email_list" id="uselist" data-toggle-fieldset="list_template" />
      <label for="uselist">Use email list</label>
		</div>
			<fieldset>
		<div class="input-field col s12">
          <i class="mdi-action-supervisor-account prefix"></i>
          <textarea name="recipient_list" data-required type="text" class="validate materialize-textarea" placeholder="Separated multiple email with a comma(,)"></textarea>
          <label>Recipients</label>
        </div>
		<p>
		<input type="checkbox" name="save_recipient_list" id="test9" checked="checked" />
      <label for="test9">Save as list</label>
	  </p>
	  <div class="input-field col s6">
         
          <input type="text" name="save_recipient_list_title" class="validate">
          <label>List title</label>
        </div>
		</fieldset>
		<fieldset id="list_template">
		<div class="input-field col s12">
          <select class="validate" name="recipient_list" data-required>
		  <option selected value="">Select Recipients</option>
			$lists
		  </select>
          <label>Email Recipients <i class="mdi-action-supervisor-account preffix"></i></label>
        </div>
		</fieldset>
		<button type="submit" class="btn lg red darken-1 large right">Send</button>
		</form>
					</div></div>
EOT;
$app->script = '<script type="text/javascript" src="msshift/deck/js/tinymce/tinymce.min.js"></script>';
$app->script .= <<<EOD
	<script type="text/javascript">
	tinymce.init({
		selector:"textarea[name=email_body]",
		mode:'textareas',
			theme: 'modern',
			directionality: 'ltr',
			language : 'en',
			dialog_type:'modal',
			schema: 'html5',
		toolbar1: 'newdocument fullpage | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | styleselect formatselect fontselect fontsizeselect',
		toolbar2: 'cut copy paste | searchreplace | bullist numlist | outdent indent blockquote | undo redo | link unlink anchor image media code | insertdatetime preview | forecolor backcolor',
		plugins: [
		'advlist autolink lists link image charmap preview hr anchor pagebreak',
        'searchreplace wordcount fullscreen',
        'insertdatetime nonbreaking table ',
        'template paste textcolor colorpicker textpattern'
		]
	})
		toggler = $('input[type=checkbox][data-toggle-fieldset]');
		visible = $("fieldset[id]").hide();
		toggler.on("change",function(){
			if($(this).is(':checked')){
				tg = $(this).data('toggle-fieldset');
					$("fieldset#"+tg).show();
					$(this).parent().next().hide();
			}else{
				tg = $(this).data('toggle-fieldset');
				$("fieldset#"+tg).hide();
				$(this).parent().next().show();
				
			}
		})
		toggler2 = $('input[name="save_recipient_list"],input[name="save_bd_template"]');
			toggler2.each(function(){
				if($(this).is(':checked')==false){
					$(this).parent().next().hide();
				}else{
					$(this).parent().next().show();
				}
			})
			toggler2.on('change',function(){
				if($(this).is(':checked')==false){
					$(this).parent().next().hide();
				}else{
					$(this).parent().next().show();
				}
			})
		$("form#compose").on('submit',function(e){
			e.preventDefault();
			data = $('fieldset:visible').find(':input').serialize();
			data += '&'+$('input[type=checkbox][data-toggle-fieldset]').serialize();
			data += '&'+$('input[name="email_subject"]').serialize();
			data += '&email_body='+tinyMCE.activeEditor.getContent();
			aj = $(this).ajRequest({autorun:false})
			aj.data = data;
			
			aj.responseType = 'html';
			aj.beforeSend = function(){
				$("form#compose button[type=submit]").attr('disabled',true)
				$('#modal .modal-content').html('<h4>Please wait..</h4><div class="progress"><div class="indeterminate"></div></div>')
  $('#modal').openModal();
			}
			send = aj.post();
			send.done(function(){
					$("form#compose button[type=submit]").removeAttr('disabled')
				$('#modal').closeModal();
				$('#modal .modal-content').html('');
			})
			send.fail(function(r){
				$('#modal .modal-content').html('<div class="padd grey card-panel lighten-3"><h5 class="red-text center">'+r.responseText+'</h5></div>');
				$("form#compose button[type=submit]").removeAttr('disabled')
			})
		})
	</script>
EOD;
			break;
			case 'gmail':
			$isposted = (Director::isAjax() AND $app->request->isPost());
				if($isposted){
						$t = $app->request->postvar('t');
							
							if($t ==='info' AND isset($_SESSION['generated_emails'])){
								//Retrieve emails found
								$t = count($_SESSION['generated_emails']);
								die(''.$t.'');
							}
							
							if($t ==='gen'){
									$region = $app->request->postvar('target_region');
									$dest = strtolower($app->request->postvar('target_destination'));
									$title = strip_tags($app->request->postvar('target_destination_title'));
										if(in_array($dest,array('new_list','file'))){
												if(empty($title)){
											http_response_code(500);
											die('Please provide list title');
												}
										}
									if(!file_exists(dirname(SITE).'/fileserve/names_'.$region.'.txt')){
										http_response_code(500);
											die('Selected country could not be found');
									}
									$ct = array_filter(explode(',',file_get_contents(dirname(SITE).'/fileserve/names_'.$region.'.txt')));
									$cc = count($ct);
									$count = 5101;
									shuffle($ct);
									$done = false;
									$i = 0;
									$_SESSION['generated_emails'] = array();
									$providers = array('gmail.com','yahoo.com','hotmail.com','aol.com');
									for($i=0;$count > $i;$i++){
										
										$n = current($ct);
											if($n===false){
												shuffle($ct);
												$n = reset($ct);
												
											}
											$n = preg_replace('#\s#','',$n);
											shuffle($providers);
											$prv = end($providers);
										if(in_array($n.'@'.$prv,$_SESSION['generated_emails'])){
											$range = range(0,9,1);
											shuffle($range);
											$sr = implode('',array_slice($range,0,3));
											$n = $n.$sr;
										}
										if(in_array($n.'@'.$prv,$_SESSION['generated_emails'])){
											$n = implode('_',explode(current($ct),$n));
										}
											if(in_array($n.'@'.$prv,$_SESSION['generated_emails'])){
												$n = str_shuffle($n);
											}
											
											$n = $n.'@'.$prv;
											if(!in_array($n,$_SESSION['generated_emails'])){
												array_push($_SESSION['generated_emails'],$n);
											}
										next($ct);
									}
									$out = array('emails'=>$_SESSION['generated_emails']);
										if($dest ==='new_list'){
											$t = count($_SESSION['generated_emails']);
												if($t > 0){
													
												$table = $app->db->table('emails');	
												$get = $table->fetchAll('list_title="'.$title.'"');
							if($get->count() > 0){
									$title .= time();
								}
												$table->insert(array('list_title'=>$title,
							'mails'=>implode(',',$_SESSION['generated_emails']),'total_email'=>$t
							));
												}
											
											
										}elseif($dest =='file'){
											$filename = SITE.'/tmp/emails_'.time().'.zip';
											$zp = new zipper;
											$tmpf = SITE.'/tmp/'.basename($filename,'.zip').'.csv';
												file_put_contents($tmpf,implode(',',$_SESSION['generated_emails']));
											$zp->addFileList(array($filename),SITE.'/tmp/');
											$zp->save($filename);
												if(file_exists($filename)){
													unlink($tmpf);
													$out['dll'] = $app->request->getUrl().'/tmp/'.basename($filename);
												}
										}
										
									die(json_encode($out));
							}
					die();
				}
				$app->view .= <<<EOT
				<div class="card z-depth-1 row">
					<div class="card-content">
						<form name="generate_email_form" class="col s12">
						<div class="row">
        <div class="input-field col s6">
          <i class="mdi-action-language prefix"></i>
          <input id="icon_prefix" type="text"  name="target_destination_title" class="validate">
          <label for="icon_prefix">List title</label>
        </div>
        <div class="input-field col s12">
          <select class="validate browser-default" required name="target_destination">
		  <option selected value="">Select destination</option>
			<option value="new_list">New list</option>
			<option value="file">File</option>
			<option value="inline">Inline</option>
		  </select>
          
        </div>
		<div class="input-field col s12">
          
          <select class="browser-default" required name="target_region">
			<option selected value="">Select country</option>
			<option value="uk">UK</option>
			<option value="norway">NORWAY</option>
			<option value="usa">USA</option>
			<option value="spain">SPAIN</option>
			<option value="australia">AUSTRALIA</option>
		  </select>
          
        </div>
      </div>
	  <button type="submit" class="btn lg red darken-1 large col s12">Start</button>
						</form>
					</div>
				</div>
EOT;
$app->script .= <<<EOD
<script type="text/javascript">
	$('form[name="generate_email_form"]').on('submit',function(e){
		e.preventDefault();
		aj = $(this).ajRequest({autorun:false})
	aj.responseType = 'json';
	aj.data = $(this).serialize()+'&t=gen';
	aj.beforeSend = function(){
		$('form[name="generate_email_form"] button[type=submit]').attr('disabled',true);
		$('#modal .modal-content').html('<h4>Searching..</h4><div class="card-panel"><div class="progress"><div class="indeterminate"></div></div></div>');
		$('#modal').openModal();
	};
	send = aj.post();
		send.done(function(r){
			$('form[name="generate_email_form"] button[type=submit]').removeAttr('disabled');
			if(r.dll !==undefined){
				$('#modal .modal-content').html('<div class="card-panel"><a class="btn red large">Download</a></div>');
				$('#modal .modal-content a').attr("href",r.dll)
			}else{
				$('#modal .modal-content').html('<div class="card-panel"><pre></pre></div>');
					$.each(r.emails,function(i,e){
						$('#modal .modal-content pre:first').append(e+"<br>");
					})
			}
		})
		send.fail(function(r){
			$('#modal .modal-content').html('<div class="card-panel"><h4 class="center red-text">'+r.responseText+'</h4></div>');
			$('form[name="generate_email_form"] button[type=submit]').removeAttr('disabled');
		})
	})
</script>
EOD;
			break;
			case 'sitescrap':
			require_once(MODELS.'/requests/requests.lib.php');
			Requests::register_autoloader();
			$isposted = (Director::isAjax() AND $app->request->isPost());
			
			if($isposted){
				require_once(MODELS.'/scrap.php');
			}
		$app->view .=		<<<EOT
		
				<div class="card z-depth-1 row">
					<div class="card-content">
						<div class="hide" id="progress" data-value="0">
					<div class="progress progress-danger white-text narrow-margin">
						<span class="amount" style="color:#339bb9;">
							<i class="micon-user-2"></i>
						</span>
						<div class="bar text-filled-2" data-percentage="0" style="width: 0%;">0%</div>
					</div>
					</div>
						<form name="generate_email_form" class="col s12">
						<div class="row">
        <div class="input-field col s12">
          <i class="mdi-action-language prefix"></i>
          <input id="icon_prefix" name="url" required type="url" class="validate">
          <label for="icon_prefix">Website</label>
        </div>
		<fieldset class="s12">
		<div class="input-field col s6">
          <input id="deep" name="deep"  type="checkbox" class="validate">
          <label for="deep">Use deep search</label>
        </div>
		<div class="input-field col s6">
          <input id="deep_length" min="1" name="depth"  type="number" class="validate">
          <label for="deep_length">Maximum tranversal</label>
        </div>
		</fieldset>
        
		
      </div>
	  <button type="submit" id="submit" class="btn lg green darken-1 large col s12">Start</button>
	  <button type="button" id="submitstop" class="btn lg red darken-1 large col s12">Stop</button>
						</form>
					</div>
				</div>
EOT;
$app->style = '<link rel="stylesheet" type="text/css" href="msshift/deck/css/progress-bars.css" />';
$app->script = <<<EOD
	<script type="text/javascript">
	function scrapview(){
		
	}
		function scrapfinalize(e){
			
			aj = $("form[name='generate_email_form']").ajRequest({autorun:false})
			aj.data = {url:$("form[name='generate_email_form'] input[type=url]:first").val()}
			aj.beforeSend = function(){
				$("#modal .modal-content button").addClass('disabled');
			};
			aj.responseType = 'json';
			send = aj.post();
			send.done(function(r){
				$('#modal .modal-content').html('<div class="card-panel center"><button onclick="scrapdownload(this)" class="btn red">Download</button><a target="blank" onclick="scrapview(this)" class="btn red">View</a></div>');
					$('#modal .modal-content a').attr('href',r.viewll);
					$('#modal .modal-content button').attr('data-href',r.dll);
				
			})
		}
		function scrapdownload(){
			href = $('#modal .modal-content button').data('href');
			
			location.href = href;
		}
		$("button#submitstop").on("click",function(){
			f = $("form[name='generate_email_form']");
			if(f.find("button#submit:first").is(":disabled")){
				aj = f.data("ajRequest");
				aj.data += '&step=abort';
				//aj.post();
			aj.container.ajax.abort();
			}
		})
	$("form[name='generate_email_form']").on("submit",function(e){
		e.preventDefault();
		aj = $(this).ajRequest({autorun:false})
		aj.data = $(this).serialize()+"&url="+$("form[name='generate_email_form'] input[type=url]:first").val();
		aj.beforeSend = function(){
			$("form[name='generate_email_form'] input[type=url]:first").attr("disabled",true);
			$("form[name='generate_email_form']").find("button#submit").addClass("disabled").attr("disabled",true).text("Waiting..");
				if($("div#progress").data('value') ==0){
			update_progress(1);
				}
		};
		aj.responseType = 'html'
		send = aj.post();
		send.fail(function(r){
				if(r.statusText =="abort"){
					update_progress(0);
			$('div#progress').addClass("hide");
			
			$("form[name='generate_email_form'] input[type=url]:first").removeAttr("disabled");$("form[name='generate_email_form']").find("button#submit").removeClass("disabled").removeAttr("disabled").text("Start");
				}
			if(r.responseText !==undefined && r.responseText.replace(/[^\d\.]/g,"") != r.responseText){
			
			}
		})
		send.done(function(r){
					if(r.indexOf("{") !='-1'){
						r = JSON.parse(r);
						$('#modal .modal-content').html('<div class="card-panel center"><button onclick="scrapdownload(this)" class="btn red">Download</button><a target="blank" onclick="scrapview(this)" class="btn red">View</a></div>');
					$('#modal .modal-content a').attr('href',r.viewll);
					$('#modal .modal-content button').attr('data-href',r.dll);
					$('#modal').closeModal();
					$('#modal').openModal();
					$("form[name='generate_email_form'] input[type=url]:first").removeAttr("disabled");$("form[name='generate_email_form']").find("button#submit").removeClass("disabled").removeAttr("disabled").text("Start");
					update_progress(100);
					return;
					}
			if(r < 100){
			update_progress(r);
			
			$("form[name='generate_email_form']").trigger("submit");
			}else{
				//Build the page
					if(r ==100){
				$('#modal .modal-content').html('<div class="card-panel"><button onclick="scrapfinalize(this)" class="btn red">Finalize</button></div>')
					}else if(r ==120){
						$('#modal .modal-content').html('<div class="card-panel"><button onclick="scrapdownload(this)" class="btn red">Download</button></div>')
						
					}
					if(r > 100){
						r = 100;
					}
				$('#modal').openModal();
			$("form[name='generate_email_form'] input[type=url]:first").removeAttr("disabled");update_progress(r);$("form[name='generate_email_form']").find("button#submit").removeClass("disabled").removeAttr("disabled").text("Start");
			}
		})
		send.fail(function(r){
			update_progress(0);
			$('div#progress').addClass("hide");
			
			$("form[name='generate_email_form'] input[type=url]:first").removeAttr("disabled");$("form[name='generate_email_form']").find("button#submit").removeClass("disabled").removeAttr("disabled").text("Start");
			
			if(r.responseText !==undefined && r.responseText.replace(/[^\d\.]/g,"") != r.responseText){
			alert(r.responseText);
			}
		})
	})
	</script>
EOD;
			break;
			case 'emaillist':
			case 'mailbody':
				$isposted = (Director::isAjax() AND $app->request->isPost());
				if($p ==='emaillist'){
							$table = 'emails';
							$blob = 'mails';
						}else{
							$table = 'mailbody';
							$blob = 'body';
						}
					if($app->request->getvar('t') ==='download'){
						$id = $app->request->getvar('tid');
						$get = $app->db->rawquery('SELECT '.$blob.' AS data FROM '.$table.' WHERE id='.$id);
						if(isset($get[0]) AND !empty($get[0])){
							
								$dn = $app->request->send_file($get[0]['data'],'data.txt','text/html');
								
							$dn->output();
							die();
						}
						
					}
				if($isposted AND $app->request->postvar('t')==='del'){
					$id = $app->request->postvar('tid');
					if(!is_numeric($id)){
						http_response_code(500);
						die('Not resolved');
					}
						
					$do = $app->db->table($table)->delete('id='.$id);
					if($do){
					$app->db->getConnection()->commit();
					die("$id");
					}else{
						http_response_code(500);
						die('Not resolved');
					}
				}
				if($p ==='emaillist'){
					$get = $app->db->rawquery('SELECT id,`list_title` AS title,`total_email` AS "Total emails" FROM emails');
				}else{
					$get = $app->db->rawquery('SELECT id,title FROM mailbody');
					
				}
				$th = array_keys($get[0]);
				$thead = '';
				foreach($th as $thk){
					$thead .= '<th data-field="'.$thk.'">'.ucfirst($thk).'</th>';
				}
				$tbody = '';
				$t = count($get);
				for($i=0;$t > $i;$i++){
					$tbody .= '<tr id="'.$get[$i]['id'].'">';
					foreach($th as $tk){
						$tbody .= '<td>'.$get[$i][$tk].'</td>';
						
					}
					$tbody .= '<td><div><a href="?p='.$p.'&t=download&tid='.$get[$i]['id'].'" class="btn">Download</a><button data-t="del" data-tid="'.$get[$i]['id'].'" class="btn red">Delete</button></div></td>';
					$tbody .= '</tr>';
				}
			$app->view .=	<<<EOT
			<div class="card ">
				<div class="card-content">
				 <table class="highlight">
        <thead>
          <tr>
              $thead
          </tr>
        </thead>

        <tbody>
          $tbody
        </tbody>
      </table>
	  </div></div>
EOT;
$app->script .= <<<EOD
<script type="text/javascript">
del = $("table tr button[data-t]").on('click',function(e){
	e.preventDefault();
	aj = $(this).ajRequest({autorun:false})
	aj.responseType = 'html';
	aj.beforeSend = function(){};
	aj.data = {'t':$(this).data('t'),'tid':$(this).data('tid')}
	send = aj.post();
	send.done(function(r){
		
		$("table tr#"+r).remove();
	})
})
</script>
EOD;
			break;
			case 'config':
			$isposted = (Director::isAjax() AND $app->request->isPost());
			if($isposted){
				$vars = $app->request->postvars();
					if($app->config->count() ==0){
						$app->config->getTable()->insert(array('tocc'=>$vars['toemail'],'tocc2'=>$vars['toccmail'],
						'appemail'=>$vars['appemail']
						));
					
					}else{
						$app->config->current()->tocc = $vars['toemail'];
						$app->config->current()->tocc2 = $vars['toccmail'];
						$app->config->current()->appemail = $vars['appemail'];
						$app->config->current()->save();
					}
				die("1");
			}
			if($app->config->count() ==0){
				$config = new stdClass;
			}else{
				$config = $app->config->current();
			}
			$app->view .= <<<EOD
				<div class="card z-depth-1 row grey lighten-3">
				<div class="card-content">
			<form name="config_form" class="col s12">
						<div class="row">
        <div class="input-field col s6">
          <i class="mdi-communication-email prefix"></i>
          <input name="toemail" value="$config->tocc" id="icon_prefix" required type="email" class="validate">
          <label>To email</label>
        </div>
		<div class="input-field col s6">
          <i class="mdi-communication-email prefix"></i>
          <input name="toccmail" value="$config->tocc2"  required type="email" class="validate">
          <label>To email :CC</label>
        </div>
		<div class="input-field col s12">
          <i class="mdi-communication-email prefix"></i>
          <input name="appemail" value="$config->appemail" required type="email" class="validate">
          <label>Application email</label>
        </div>
      </div>
	  <button type="submit" id="submit" class="btn lg red darken-1 large col s12">Start</button>
						</form>
						</div>
						</div>
EOD;
$app->script .= <<<EOT
		<script type="text/javascript">
			$('form[name="config_form"]').on('submit',function(e){
				e.preventDefault();
				aj = $(this).ajRequest({autorun:false})
				aj.responseType = 'html';
				aj.beforeSend = function(){};
				aj.data = $(this).serialize();
				aj.post();
			})
			
			 </script>
EOT;
			break;
			

		}
		$app->script .= <<<EOT
		<script type="text/javascript">
			 $('select').material_select();
			
			 </script>
EOT;
	}else{
		//Show start page
$app->view = <<<EOT
		<div class="card-panel center col l4 offset-l4 "><div class="card-content">
		<h4>Email Gopher 2</h4>
		<p><span class="mdi-action-announcement mdi-3x red-text"></span></p>
		<p>This program is meant for educational and testing purposes. Though without the developers' notice, part of this project might be altered to suit a special purpose which the developer is not responsible for.</p>
		</div></div>
EOT;
	}
?>