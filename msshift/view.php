<?php 
defined('SAFE')or die();
$tmp = 'msshift';
?>

<!doctype html><html lang="en" style="
    background: url(img/bg.jpg) repeat;
">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<title>EmailGopper by Leo</title>
<link rel="stylesheet" type="text/css" href="<?php echo $tmp.'/deck/css/materialize.css' ?>">
<?php echo $app->style ?>
</head>
<body class="row  lighten-5">
	<div class="col l2 m4 s12">
		<div class="card">
			<div class="card-content">
				<h5><a href="?">Email Gopher</a> <span class="mdi-action-home"></span></h5>
				<ul class="collection">
					<a href="?p=cgmail" class="collection-item">Compose Emails <span class="mdi-content-create"></span></a>
					
					<a href="?p=sitescrap" class="collection-item">Site page Wizard <span class="mdi-action-account-balance"></span></a>
					<a href="?p=emaillist" class="collection-item">Organize lists <span class="mdi-content-filter-list"></span></a>
					<a href="?p=mailbody" class="collection-item">Body templates <span class="mdi-action-account-balance"></span></a>
					<a href="?p=config" class="collection-item">Settings <span class="mdi-action-settings"></span></a>
					
					
					
				</ul>
			</div>
		</div>
	</div>
	<div class="col s8 m8 l8">
		<?php echo $app->view ?>
	</div>
	<div id="modal" class="modal">
    <div class="modal-content">
      
      
    </div>
    <div class="modal-footer">
      <a href="#!" class=" modal-action modal-close waves-effect waves-green btn-flat">Close</a>
    </div>
  </div>
<script type="text/javascript" src="<?php echo $tmp.'/deck/js/jquery.min.js' ?>" ></script>
<script type="text/javascript" src="<?php echo $tmp.'/deck/js/materialize.js' ?>"></script>
<script type="text/javascript" src="<?php echo $tmp.'/deck/js/ajrequest.js' ?>"></script>
<script type="text/javascript">
function update_progress(value){
	
			if(typeof value !=='number' && value.match(/[a-z]/i) !=null)return false;
			
			if($("div#progress").length ==0)return false;
			
			$("div#progress").data("value",value);
			$("div#progress").removeClass("hide").find("div.bar:first").attr('data-percentage',value).css({
				width:value+"%"
			}).text(value+"%").prev("span.amount").find("i:first").text(value+"%")
		}
</script>
<?php echo $app->script ?>
</body>
</html>