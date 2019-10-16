<!doctype html>
<html lang="de">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="inc/bw/bootstrap.min.css">
		<link rel="stylesheet" href="inc/fa/css/font-awesome.min.css">
		<link rel="stylesheet" href="inc/gl/style.css">
		<link rel="icon" href="inc/im/favicon.png" sizes="192x192" />
		<title>Redeliste</title>
	</head>
	<body>
		<h3 id="tit" class="p-1 m-0"></h3>
		<table class="table table-striped redetab" id="redtab">
			<tbody>
			</tbody>
		</table>
		<div class="w-100 h-100" style="text-align: center;" id="info">
			<noscript>
				<div class="alert alert-danger" role="alert">
				  <h2><b>JavaScript deaktiviert!</b></h2>
				  <h2>Damit die Redeliste immer aktuell ist, benötigt sie JavaScript. Bitte aktiviere dieses in deinem Browser.</h2>
				</div>
				<br />
			</noscript>
			<span id="wifiinfo" style="display: none;">
				<h1><i class="fa fa-fw fa-4x fa-wifi"></i></h1>
				<h2><b>WiFi-SSID</b></h2>
				<h2><i class="fa fa-lock"></i> WiFi-PSK</h2>
				<br />
			</span>
			<h1><i class="fa fa-fw fa-4x fa-bars"></i></h1>
			<h2><b>Redeliste</b></h2>
			<h2><?=$_SERVER["HTTP_HOST"]?></h2>
		</div>
		<div class="logo"></div>
		<script src="inc/jq/jquery.min.js"></script>
		<script src="inc/bs/bootstrap.min.js"></script>
		<script src="inc/gl/script.js"></script>
	</body>
</html>
