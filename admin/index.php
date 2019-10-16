<?php

	$queue = json_decode(file_get_contents("../inc/gl/queue.json"), true);

	if (isset($_GET["action"])) {
		if ($_GET["action"] == "closemain") {
			if (sizeof($queue["main"]) > 0) {
				$queue["settings"]["main_closed"] = true;
			}
		} elseif ($_GET["action"] == "closeadd") {
			if (sizeof($queue["add"]) > 0) {
				$queue["settings"]["add_closed"] = true;
			}
		} elseif ($_GET["action"] == "reopenmain") {
			$queue["settings"]["main_closed"] = false;
		} elseif ($_GET["action"] == "reopenadd") {
			$queue["settings"]["add_closed"] = false;
		} elseif ($_GET["action"] == "addmain" && isset($_POST["addmainname"]) && !empty(trim($_POST["addmainname"]))) {
			if (!$queue["settings"]["main_closed"]) {
				if (empty($queue["main"]["current"])) {
					if (isset($queue["timer"]) && empty($queue["add"]["current"])) {
						$queue["timer"]["started"] = time();
						$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
					}
					$queue["main"]["current"] = trim($_POST["addmainname"]);
				} else {
					$queue["main"]["normal"][] = trim($_POST["addmainname"]);
				}
			}
		} elseif ($_GET["action"] == "addadd" && isset($_POST["addaddname"]) && !empty(trim($_POST["addaddname"]))) {
			if (!$queue["settings"]["add_closed"]) {
				if (empty($queue["add"]["current"])) {
					if (isset($queue["timer"])) {
						$queue["timer"]["started"] = time();
						$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
					}
					$queue["add"]["current"] = trim($_POST["addaddname"]);
				} else {
					$queue["add"]["normal"][] = trim($_POST["addaddname"]);
				}
			}
		} elseif ($_GET["action"] == "finishedmain" && !empty($queue["main"]["current"])) {
			if (isset($queue["timer"]) && empty($queue["add"]["current"])) {
				$queue["timer"]["started"] = time();
				$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
			}
			if (sizeof($queue["main"]["prio"]) > 0) {
				$queue["main"]["current"] = array_shift($queue["main"]["prio"]);
			} elseif (sizeof($queue["main"]["normal"]) > 0) {
				$queue["main"]["current"] = array_shift($queue["main"]["normal"]);
			} else {
				$queue["main"]["current"] = "";
				$queue["settings"]["main_closed"] = false;
				if (isset($queue["timer"]) && empty($queue["add"]["current"])) {
					$queue["timer"]["started"] = -1;
				}
			}
		} elseif ($_GET["action"] == "finishedadd" && !empty($queue["add"]["current"])) {
			if (isset($queue["timer"])) {
				$queue["timer"]["started"] = time();
				$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
			}
			if (sizeof($queue["add"]["prio"]) > 0) {
				$queue["add"]["current"] = array_shift($queue["add"]["prio"]);
			} elseif (sizeof($queue["add"]["normal"]) > 0) {
				$queue["add"]["current"] = array_shift($queue["add"]["normal"]);
			} else {
				$queue["add"]["current"] = "";
				$queue["settings"]["add_closed"] = false;
				if (isset($queue["timer"]) && empty($queue["main"]["current"])) {
					$queue["timer"]["started"] = -1;
				}
			}
		} elseif ($_GET["action"] == "priomain" && isset($_GET["id"]) && strlen($_GET["id"]) > 0) {
			$queue["main"]["prio"][] = $queue["main"]["normal"][$_GET["id"]];
			array_splice($queue["main"]["normal"], $_GET["id"], 1);
		} elseif ($_GET["action"] == "prioadd" && isset($_GET["id"]) && strlen($_GET["id"]) > 0) {
			$queue["add"]["prio"][] = $queue["add"]["normal"][$_GET["id"]];
			array_splice($queue["add"]["normal"], $_GET["id"], 1);
		} elseif ($_GET["action"] == "delmain" && isset($_GET["id"]) && isset($_GET["q"]) && in_array($_GET["q"], ["normal", "prio"]) && strlen($_GET["id"]) > 0) {
			array_splice($queue["main"][$_GET["q"]], $_GET["id"], 1);
		} elseif ($_GET["action"] == "deladd" && isset($_GET["id"]) && isset($_GET["q"]) && in_array($_GET["q"], ["normal", "prio"]) && strlen($_GET["id"]) > 0) {
			array_splice($queue["add"][$_GET["q"]], $_GET["id"], 1);
		} elseif ($_GET["action"] == "clearmain") {
			$queue["main"] = array(
				"current" => "",
				"prio" => array(),
				"normal" => array(),
			);
			$queue["settings"]["main_closed"] = false;
			if (isset($queue["timer"]) && empty($queue["add"]["current"])) {
				$queue["timer"]["started"] = -1;
				$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
			}
		} elseif ($_GET["action"] == "clearadd") {
			$queue["add"] = array(
				"current" => "",
				"prio" => array(),
				"normal" => array(),
			);
			$queue["settings"]["add_closed"] = false;
			if (isset($queue["timer"])) {
				if (empty($queue["main"]["current"])) {
					$queue["timer"]["started"] = -1;
				} else {
					$queue["timer"]["started"] = time();
				}
				$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
			}
		} elseif ($_GET["action"] == "savesettings") {
			if (empty($queue) || !isset($queue["settings"]) || empty($queue["settings"])) {
				$queue["settings"]["title"] = "Hauptantrag";
			}
			if (isset($_POST["title"])) {
				$queue["settings"]["title"] = $_POST["title"];
			}
			if (isset($_POST["wifi"]) && $_POST["wifi"] == "true") {
				$queue["settings"]["wifi_info"] = true;
			} else {
				$queue["settings"]["wifi_info"] = false;
			}
			if ((!isset($_POST["minutes"]) || empty($_POST["minutes"])) && (!isset($_POST["seconds"]) || empty($_POST["seconds"]))) {
				unset($queue["settings"]["time_minutes"]);
				unset($queue["settings"]["time_seconds"]);
				unset($queue["timer"]);
			} else {
				if (isset($_POST["minutes"])) {
					$queue["settings"]["time_minutes"] = $_POST["minutes"];
				}
				if (isset($_POST["seconds"])) {
					$queue["settings"]["time_seconds"] = $_POST["seconds"];
				}
				if (isset($queue["timer"])) {
					if ($queue["timer"]["started"] == -1) {
						$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
					}
				} else {
					$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
					if (sizeof($queue["add"]) > 0 || sizeof($queue["main"]) > 0) {
						$queue["timer"]["started"] = time();
					} else {
						$queue["timer"]["started"] = -1;
					}
				}
			}
		} elseif (isset($queue["timer"]) && $_GET["action"] == "timer" && isset($_POST["action"])) {
			if ((isset($queue["settings"]["time_minutes"]) && !empty($queue["settings"]["time_minutes"])) || (isset($queue["settings"]["time_seconds"]) && !empty($queue["settings"]["time_seconds"]))) {
				if ($_POST["action"] == "start") {
					$queue["timer"]["started"] = time();
				} elseif ($_POST["action"] == "pause") {
					$passed = time() - $queue["timer"]["started"];
					$curr = $queue["timer"]["current"] - $passed;
					if ($curr < 0) {
						$curr = 0;
					}
					$queue["timer"]["started"] = 0;
					$queue["timer"]["current"] = $curr;
				} elseif ($_POST["action"] == "reset") {
					$queue["timer"]["started"] = time();
					$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
				}
			}
		}

		file_put_contents("../inc/gl/queue.json", json_encode($queue, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}

?>
<!doctype html>
<html lang="de" onclick="autofocus();">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="../inc/bw/bootstrap.min.css">
		<link rel="stylesheet" href="../inc/fa/css/font-awesome.min.css">
		<link rel="stylesheet" href="../inc/gl/style.css">
		<link rel="icon" href="../inc/im/favicon.png" sizes="192x192" />
		<title>Redeliste</title>
	</head>
	<body class="bg-dark">
		<noscript>
			<div class="alert alert-danger" role="alert">
			  <h2><b>JavaScript deaktiviert!</b></h2>
			  <h2>Damit die Redeliste immer aktuell ist, benötigt sie JavaScript. Bitte aktiviere dieses in deinem Browser.</h2>
			</div>
		</noscript>
		<div class="modal fade" id="maintruncate" tabindex="-1" role="dialog" aria-labelledby="maintruncateLbl" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="maintruncateLbl">Liste des Hauptantrags leeren?</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Schließen">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Bist du dir sicher, dass du die gesamte Liste des Hauptantrags leeren möchtest?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" data-dismiss="modal"><b>Nein</b></button>
						<button onclick="asyncreq('?action=clearmain');" class="btn btn-danger" data-dismiss="modal"><b><i class="fa fa-recycle"></i> Ja</b>, Liste leeren</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="addtruncate" tabindex="-1" role="dialog" aria-labelledby="addtruncateLbl" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addtruncateLbl">Liste des Änderungsantrags leeren?</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Schließen">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						Bist du dir sicher, dass du die gesamte Liste des Änderungsantrags leeren möchtest?
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" data-dismiss="modal"><b>Nein</b></button>
						<button onclick="asyncreq('?action=clearadd');" class="btn btn-danger" data-dismiss="modal"><b><i class="fa fa-recycle"></i> Ja</b>, Liste leeren</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="settings" tabindex="-1" role="dialog" aria-labelledby="settingsLbl" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="settingsLbl"><i class="fa fa-fw fa-cog"></i> Einstellungen</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Schließen" onclick="allowfocus(true);">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<label for="maintitinp">Titel Hauptantrag</label>
						<input class="form-control" type="text" id="maintitinp" name="maintitinp" placeholder="Hauptantrag">
						<hr />
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="actwifi"><label for="actwifi"> WLAN-Informationen anzeigen</label>
						</div>
						<hr />
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="actredtime" onclick="toggleredtime()"><label for="actredtime"> Redezeit aktivieren</label>
						</div>
						<label for="redtime">Redezeit</label>
						<div class="input-group" id="redtime">
							<input type="number" min="0" max="59" step="1" class="form-control text-right" name="redtimemin" id="redtimemin" placeholder="Minuten" size="2" required maxlength="2">
							<div class="input-group-append input-group-prepend">
								<span class="input-group-text">:</span>
							</div>
							<input type="number" min="0" max="59" step="1" class="form-control" name="redtimesek" id="redtimesek" placeholder="Sekunden" size="2" required maxlength="2">
						</div>
					</div>
					<div class="modal-footer">
						<button onclick="savesettings(); allowfocus(true);" type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-fw fa-save"></i> <b>Speichern</b></button>
						<button type="button" class="btn btn-danger" data-dismiss="modal" onclick="allowfocus(true);"><i class="fa fa-fw fa-trash"></i> Verwerfen</button>
					</div>
				</div>
			</div>
		</div>
		<div class="col-lg-3 col-sm-3 col-xs-3 col-md-3 pull-left adminredtab m-0 p-0">
			<h3 id="tit" class="text-light p-1 m-0"></h3>
			<table class="table table-striped redetab" id="redtab-prev">
				<tbody>
				</tbody>
			</table>
		</div>
		<div class="col-lg-9 col-sm-9 col-xs-9 col-md-9 p-1 pull-right">
			<div class="card">
				<div class="card-body">
					<div>
						<div class="btn-group pull-right" role="group">
							<a href="../" onclick="return !window.open(this.href, 'Redeliste', 'width=500,height=500,menubar=0,scrollbars=0,status=0,titlebar=0,toolbar=0')" target="_blank" class="btn btn-secondary text-white"><i class="fa fa-fw fa-television"></i> Beameransicht</a>
							<button type="button" data-toggle="modal" data-target="#settings" class="btn btn-secondary" onclick="allowfocus(false);"><i class="fa fa-fw fa-cog"></i> Einstellungen</button>
						</div>
						<h1>Redelisten</h1>
					</div>
					<div class="d-flex flex-row align-items-start justify-content-between">
						<div class="container-fluid">
							<h3 id="maintithead">Hauptantrag</h3>
							<input type="text" class="form-control form-control-lg" placeholder="Hinzufügen" name="addmainname" id="addmainname" onfocus="allowfocus(false);" onfocusout="allowfocus(true);" onkeydown="checkenter('index.php?action=addmain', this);">
							<br />
							<div class="btn-group" role="group" style="display: grid;">
								<button class="btn btn-success" onclick="asyncreq('?action=finishedmain');"><i class="fa fa-fw fa-forward"></i> Nächste Person</button>
								<button class="btn btn-secondary" onclick="asyncreq('?action=closemain');"><i class="fa fa-fw fa-lock"></i> Liste schließen</button>
								<button type="button" data-toggle="modal" data-target="#maintruncate" class="btn btn-secondary"><i class="fa fa-fw fa-recycle"></i> Liste leeren</button>
							</div>
							<br /><br />
							<table class="table table-sm table-bordered table-striped redetab" id="redtab-main">
								<tbody>
								</tbody>
							</table>
						</div>
						<div class="col-lg-3 col-sm-4 col-xs-5 col-md-5" style="text-align: center; display: none;" id="redtimepanel">
							<h2 id="timelabel"><?=date("H:i:s")?></h2>
							<h1 class="display-3" id="redtimelabel"></h1>
							<div class="btn-group" role="group">
								<button class="btn btn-lg btn-secondary" id="redtimeplay" onclick="asyncreq('?action=timer', 'action=start');" title="Redezeit starten" style="display: none;"><i class="fa fa-fw fa-play"></i></button>
								<button class="btn btn-lg btn-secondary" id="redtimepause" onclick="asyncreq('?action=timer', 'action=pause');" title="Redezeit pausieren" style="display: none;"><i class="fa fa-fw fa-pause"></i></button>
								<button class="btn btn-lg btn-secondary" id="redtimereset" onclick="asyncreq('?action=timer', 'action=reset');" title="Redezeit zurücksetzen" style="display: none;"><i class="fa fa-fw fa-undo"></i></button>
							</div>
						</div>
						<div class="container-fluid">
							<h3>Änderungsantrag</h3>
							<input type="text" class="form-control form-control-lg" placeholder="Hinzufügen" name="addaddname" id="addaddname" onfocus="allowfocus(false);" onfocusout="allowfocus(true);" onkeydown="checkenter('index.php?action=addadd', this);">
							<br />
							<div class="btn-group" role="group" style="display: grid;">
								<button class="btn btn-success" onclick="asyncreq('?action=finishedadd');"><i class="fa fa-fw fa-forward"></i> Nächste Person</button>
								<button onclick="asyncreq('?action=closeadd');" class="btn btn-secondary"><i class="fa fa-fw fa-lock"></i> Liste schließen</button>
								<button type="button" data-toggle="modal" data-target="#addtruncate" class="btn btn-secondary"><i class="fa fa-fw fa-recycle"></i> Liste leeren</button>
							</div>
							<br /><br />
							<table class="table table-sm table-bordered table-striped redetab" id="redtab-add">
								<tbody>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="logo logo-weiss"></div>
		<script src="../inc/jq/jquery.min.js"></script>
		<script src="../inc/bs/bootstrap.min.js"></script>
		<script src="../inc/gl/admin.js"></script>
	</body>
</html>
