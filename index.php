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
