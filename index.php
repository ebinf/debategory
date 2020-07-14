<?php

	$config = require("config.inc.php");
	require("inc/gl/localisation.inc.php");
	$l = new localisation();
	$l->init($config);

?>
<!doctype html>
<html lang="<?=$config["language"]?>" class="h-100">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="inc/bw/bootstrap.min.css">
		<link rel="stylesheet" href="inc/fa/css/font-awesome.min.css">
		<link rel="stylesheet" href="inc/gl/style.css">
		<link rel="icon" href="inc/im/favicon.png" sizes="192x192" />
		<title><?=$config["name"]?></title>
	</head>
	<body class="h-100">
		<noscript>
			<div class="alert alert-danger" role="alert">
				<h2><b><?=$l->_("JavaScript disabled!");?></b></h2>
				<h2><?=$l->_("To keep the speech list up to date it needs JavaScript. Please activate it in your browser.");?></h2>
			</div>
			<br />
		</noscript>
		<div id="updateMsg" class="h-100 d-flex align-items-center justify-content-center text-center" style="display: none !important;">
			<span>
				<h1><i class="fa fa-fw fa-4x fa-spin fa-refresh"></i></h1>
				<h2><b><?=$l->_("Updating...");?></b></h2>
				<h2><?=$l->_("There's an update in progress. Please refresh this page in a few minutes.")?></h2>
			</span>
		</div>
		<div id="normalCnt">
			<h3 id="tit" class="p-1 m-0"></h3>
			<table class="table table-striped redetab" id="redtab">
				<tbody>
				</tbody>
			</table>
			<div class="h-100 d-flex align-items-center justify-content-center text-center flex-column" id="info">
				<?php if ($config["wifi"]["show"]) { ?>
					<span>
						<h1><i class="fa fa-fw fa-4x fa-wifi"></i></h1>
						<h2><b><?=$config["wifi"]["ssid"]?></b></h2>
						<?php if (!empty($config["wifi"]["psk"])) { ?>
							<h2><i class="fa fa-lock"></i> <?=$config["wifi"]["psk"]?></h2>
						<?php } ?>
						<br />
					</span>
				<?php } ?>
				<span>
					<h1><i class="fa fa-fw fa-4x fa-bars"></i></h1>
					<h2><b><?=$l->_("Speech list");?></b></h2>
					<h2><?=$config["url"]?></h2>
				</span>
			</div>
		</div>
		<?php if ($config["self_service"]) {
			session_start();
			if (!isset($_SESSION["self_service_name"])) {
				if (isset($_POST["self_service_name"])) {
					$_SESSION["self_service_name"] = htmlentities($_POST["self_service_name"]);
					header("Location: ./");
					exit();
				}
			?>
				<div class="card col-12 col-lg-4 col-md-7 shadow-lg fixed-bottom m-md-4 ml-md-auto" id="self_service_namein">
				  <div class="card-body">
						<button type="button" class="ml-2 mb-1 close" onclick="$('#self_service_namein').fadeOut();">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="card-title"><b><?=$l->_("Hello");?>!</b></h4>
						<h5 class="card-subtitle mb-2"><?=$l->_("Do you also want to say something? Enter your name below and raise your hand virtually.");?></h5>
						<form class="form-group" method="POST">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text" id="basic-addon1"><i class="fa fa-user"></i></span>
								</div>
								<input type="text" class="form-control" name="self_service_name" placeholder="<?=$l->_("Your Name");?>">
							</div>
							<small class="form-text text-muted"><?=$l->_("You cannot change your name after submitting.");?></small>
						</form>
					</div>
				</div>
			<?php } else { ?>
				<div class="card col-7 col-lg-2 col-md-4 shadow-lg fixed-bottom m-4 ml-auto" id="self_service_raisehand">
					<div class="card-body">
						<h4 class="card-title"><b><?=$l->_("Hello")?>,</b> <?=$_SESSION["self_service_name"]?>.</h4>
						<button type="button" class="btn btn-primary" id="raisehand_btn"><i id="raisehand_ico" class="fa fa-hand-paper-o"></i> <?=$l->_("Raise hand");?></button>
					</div>
				</div>
			<?php } ?>
		<?php } ?>
		<div class="logo"></div>
		<script>
			l_list_closed = "<?=$l->_("List closed.")?>";
			l_prioritised = "<?=$l->_("Prioritised")?>";
		</script>
		<script src="inc/jq/jquery.min.js"></script>
		<script src="inc/bs/bootstrap.min.js"></script>
		<script src="inc/gl/script.js"></script>
	</body>
</html>
