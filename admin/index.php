<?php

	$config = require("../config.inc.php");
	$queue = json_decode(file_get_contents("../inc/gl/queue.json"), true);
	$versioning = json_decode(file_get_contents("../inc/gl/versions.json"), true);

	function rrmdir($dir) {
		if (is_dir($dir)) {
			array_map("rrmdir", glob($dir . "/{,.[!.]}*", GLOB_BRACE));
			@rmdir($dir);
		}
		else {
			@unlink($dir);
		}
	}

	function rcopy($src, $dst) {
		if (is_dir($src)) {
			@mkdir($dst);
			$files = scandir($src);
	    foreach ($files as $file) {
				if ($file != "." && $file != "..") {
					rcopy("$src/$file", "$dst/$file");
				}
			}
		} elseif (file_exists($src)) {
			copy($src, $dst);
		}
	}

	if (isset($_GET["action"])) {
		if ($_GET["action"] != "performupdate") {
			unset($queue["update_seed"]);
			unset($queue["update_url"]);
		}
		if ($_GET["action"] == "closemain") {
			if (!empty($queue["main"]["current"])) {
				$queue["settings"]["main_closed"] = true;
			}
		} elseif ($_GET["action"] == "closeadd") {
			if (!empty($queue["add"]["current"])) {
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
				$queue["settings"]["title"] = $config["default_list_title"];
			}
			if (isset($_POST["title"])) {
				$queue["settings"]["title"] = $_POST["title"];
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
					if (!empty($queue["add"]["current"]) || !empty($queue["main"]["current"])) {
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
		} elseif ($_GET["action"] == "checkforupdates") {
			header("Content-type: application/json");
			if (extension_loaded("curl") && extension_loaded("zip")) {
				try {
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/ebinf/debategory/releases/latest");
					curl_setopt($ch, CURLOPT_HTTPGET, true);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_USERAGENT, "Debategory");
					$versionsinfo = curl_exec($ch);
					curl_close($ch);
					$versionsinfo = json_decode($versionsinfo, true);
					if (is_null($versionsinfo) || !isset($versionsinfo{"tag_name"}) || empty($versionsinfo["tag_name"])) {
						echo json_encode(["status" => "error", "message" => $e]);
					} elseif ($versionsinfo["tag_name"] == $versioning["tag"]) {
						echo json_encode(["status" => "no updates"]);
					} else {
						$seed = md5($versionsinfo["name"] . time());
						$queue["update_seed"] = $seed;
						$queue["update_url"] = $versionsinfo["zipball_url"];
						$body = explode("\r\n", str_replace("* ", "", $versionsinfo["body"]));
						echo json_encode(["status" => "update", "version" => $versionsinfo["name"], "changes" => $body, "seed" => $seed]);
					}
				} catch (\Exception $e) {
					echo json_encode(["status" => "error", "message" => $e]);
				}
			} else {
				echo json_encode(["status" => "error", "message" => "Your system does not support automatic updates. Module \"curl\" or \"zip\" not available."]);
			}
		} elseif ($_GET["action"] == "performupdate" && isset($queue["update_seed"]) && isset($_GET["seed"])) {
			header("Content-type: application/json");
			if ($_GET["seed"] == $queue["update_seed"]) {
				try {
					set_time_limit(240);
					$queue["update"] = true;
					file_put_contents("../inc/gl/queue.json", json_encode($queue, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
					$updatezip = fopen("update.zip", "w");
					$ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL, $queue["update_url"]);
					curl_setopt($ch, CURLOPT_HTTPGET, true);
			    curl_setopt($ch, CURLOPT_FILE, $updatezip);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
					curl_setopt($ch, CURLOPT_USERAGENT, "Debategory");
			    curl_exec($ch);
					curl_close($ch);
					fclose($updatezip);
					$zip = new ZipArchive;
					$zip->open("update.zip", ZipArchive::CHECKCONS);
					$zip->extractTo("./update");
					$zip->close();
					unlink("update.zip");
					copy("../inc/im/favicon.png", "../inc/im/favicon.png.backup");
					copy("../inc/im/logo-white.png", "../inc/im/logo-white.png.backup");
					copy("../inc/im/logo.png", "../inc/im/logo.png.backup");
					copy("../config.inc.php", "../config.inc.php.backup");
					rcopy(glob("update/*")[0], "../");
					$oldconfig = require("../config.inc.php.backup");
					$oldkeys = array_keys($oldconfig);
					$newconfig = require("../config.inc.php");
					foreach (array_keys($newconfig) as $option) {
						if (!in_array($option, $oldkeys)) {
							$oldconfig[$option] = $newconfig[$option];
						}
					}
					file_put_contents("../config.inc.php", "<?php return " . var_export($oldconfig, true) . ";");
					unlink("../README.md");
					rename("../inc/im/favicon.png.backup", "../inc/im/favicon.png");
					rename("../inc/im/logo-white.png.backup", "../inc/im/logo-white.png");
					rename("../inc/im/logo.png.backup", "../inc/im/logo.png");
					unlink("../config.inc.php.backup");
			    rrmdir("update");
					echo json_encode(["status" => "finished"]);
					die();
				} catch (\Exception $e) {
					echo json_encode(["status" => "error", "message" => $e]);
				}
			} else {
				echo json_encode(["status" => "error", "message" => "Invalid request."]);
			}
			unset($queue["update_seed"]);
			unset($queue["update_url"]);
		}
		file_put_contents("../inc/gl/queue.json", json_encode($queue, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die();
	}

	require("../inc/gl/localisation.inc.php");
	$l = new localisation();
	$l->init($config);

?>
<!doctype html>
<html lang="<?=$config["language"]?>" onclick="autofocus();">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" href="../inc/bw/bootstrap.min.css">
		<link rel="stylesheet" href="../inc/fa/css/font-awesome.min.css">
		<link rel="stylesheet" href="../inc/gl/style.css">
		<link rel="icon" href="../inc/im/favicon.png" sizes="192x192" />
		<title><?=$config["name"]?></title>
	</head>
	<body class="bg-dark">
		<noscript>
			<div class="alert alert-danger" role="alert">
			  <h2><b><?=$l->_("JavaScript disabled!")?></b></h2>
			  <h2><?=$l->_("To keep the speech list up to date it needs JavaScript. Please activate it in your browser.");?></h2>
			</div>
		</noscript>
		<div class="alert alert-success alert-dismissable" role="alert" id="noupdateMsg" style="display: none;">
			<button type="button" class="close" onclick="$('#noupdateMsg').hide();" aria-label="<?=$l->_("Close")?>">
		    <span aria-hidden="true">&times;</span>
		  </button>
			<h2><b><?=$l->_("No updates found.")?></b></h2>
			<h4><?=$l->_("Your version of Debategory is up to date. There is no need to update.");?></h4>
		</div>
		<div class="alert alert-danger alert-dismissable" role="alert" id="updateerrorMsg" style="display: none;">
			<button type="button" class="close" onclick="$('#updateerrorMsg').hide();" aria-label="<?=$l->_("Close")?>">
				<span aria-hidden="true">&times;</span>
		  </button>
			<h2><b><?=$l->_("Could not check for updates.")?></b></h2>
			<h4><?=$l->_("Error message");?>: <span id="updateerrorLbl"></span></h4>
		</div>
		<div class="modal fade" id="maintruncate" tabindex="-1" role="dialog" aria-labelledby="maintruncateLbl" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="maintruncateLbl"><?=$l->_("Empty main list?")?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="<?=$l->_("Close")?>">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<?=$l->_("Are you sure you want to empty the entire main list?")?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" data-dismiss="modal"><b><?=$l->_("No")?></b></button>
						<button onclick="asyncreq('?action=clearmain');" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-recycle"></i> <?=$l->_("Yes, empty list")?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="addtruncate" tabindex="-1" role="dialog" aria-labelledby="addtruncateLbl" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="addtruncateLbl"><?=$l->_("Empty additional list?")?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="<?=$l->_("Close")?>">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<?=$l->_("Are you sure you want to empty the entire additional list?")?>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" data-dismiss="modal"><b><?=$l->_("No")?></b></button>
						<button onclick="asyncreq('?action=clearadd');" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-recycle"></i> <?=$l->_("Yes, empty list")?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="settings" tabindex="-1" role="dialog" aria-labelledby="settingsLbl" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="settingsLbl"><i class="fa fa-fw fa-cog"></i> <?=$l->_("Settings")?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="<?=$l->_("Close")?>" onclick="allowfocus(true);">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<label for="maintitinp"><?=$l->_("Title of main speech list")?></label>
						<input class="form-control" type="text" id="maintitinp" name="maintitinp" placeholder="<?=$config["default_list_title"]?>">
						<hr />
						<div class="form-check">
							<input type="checkbox" class="form-check-input" id="actredtime" onclick="toggleredtime()"><label for="actredtime"> <?=$l->_("Activate speech time")?></label>
						</div>
						<label for="redtime"><?=$l->_("Speech time")?></label>
						<div class="input-group" id="redtime">
							<input type="number" min="0" max="59" step="1" class="form-control text-right" name="redtimemin" id="redtimemin" placeholder="<?=$l->_("Minutes")?>" size="2" required maxlength="2">
							<div class="input-group-append input-group-prepend">
								<span class="input-group-text">:</span>
							</div>
							<input type="number" min="0" max="59" step="1" class="form-control" name="redtimesek" id="redtimesek" placeholder="<?=$l->_("Seconds")?>" size="2" required maxlength="2">
						</div>
					</div>
					<div class="modal-footer">
						<button onclick="savesettings(); allowfocus(true);" type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-fw fa-save"></i> <b><?=$l->_("Save")?></b></button>
						<button type="button" class="btn btn-danger" data-dismiss="modal" onclick="allowfocus(true);"><i class="fa fa-fw fa-trash"></i> <?=$l->_("Discard")?></button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="about" tabindex="-1" role="dialog" aria-labelledby="aboutLbl" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="aboutLbl"><i class="fa fa-fw fa-info"></i> <?=$l->_("About")?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="<?=$l->_("Close")?>" onclick="allowfocus(true);">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<h2>Debategory <small class="text-muted"><?=$versioning["name"]?></small></h2>
						<p><a href="https://github.com/ebinf/debategory" target="_blank">Debategory</a> <?=$l->_("is free software licensed under the")?> <a href="https://github.com/ebinf/debategory/blob/master/LICENSE" target="_blank"><?=$l->_("MIT License")?></a>.</p>
						<hr />
						<h3><?=$l->_("Changelog")?></h3>
						<dl>
							<?php
								foreach ($versioning["changelog"] as $version) {
									echo "<dt>" . $version["name"] . "</dt>";
									echo "<dd><ul>";
									foreach ($version["changes"] as $change) {
										echo "<li>" . $change . "</li>";
									}
									echo "</ul></dd>";
								}
							?>
						</dl>
					</div>
					<div class="modal-footer">
						<button id="checkforupdatesBtn" onclick="checkforupdates();" type="button" class="btn btn-success"><i class="fa fa-fw fa-refresh"></i> <b><?=$l->_("Check for updates")?></b></button>
						<button id="checkforupdatesBtnDisabled" type="button" class="btn btn-success" style="display: none;" disabled><i class="fa fa-fw fa-spin fa-refresh"></i> <b><?=$l->_("Checking for updates...")?></b></button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal" id="updatedialog" tabindex="-1" role="dialog" aria-labelledby="updatedialogLbl" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="updatedialogLbl"><i class="fa fa-fw fa-cube"></i> <?=$l->_("Updates")?></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="<?=$l->_("Close")?>">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<h2><?=$l->_("Newer version found!")?></h2>
						<?=$l->_("A newer version of Debategory has been found. Do you want do download and install it now?")?><br />
						<b><?=$l->_("The automatic update will take up to a few minutes, depending on your system. The speech list will not be available during that time! Also make sure to backup all important data! The update will clear the speech lists and speech time settings!")?></b>
						<hr />
						<h3><?=$l->_("What's new")?></h3>
						<dl>
							<dt id="updatedialogNew"></dt>
							<dd><ul id="updatedialogChn"><li><i><?=$l->_("Error loading changes.")?></i></li></ul></dd>
						<dl>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal"><?=$l->_("Not now")?></button>
						<input type="hidden" id="updateseed" value="" />
						<button onclick="performupdate();" class="btn btn-success" data-dismiss="modal"><i class="fa fa-cube"></i> <b><?=$l->_("Update now")?></b></button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="performupdateDia" tabindex="-1" role="dialog" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered" role="document">
		    <div class="modal-content">
		      <div class="modal-body text-center">
						<h1 id="performupdateLbl">
							<i class="fa fa-fw fa-4x fa-spin fa-refresh"></i><br /><br />
							<b><?=$l->_("Performing update...");?></b><br />
							<?=$l->_("Do not refresh this page! You will automatically be redirected as soon as the update has finished.")?>
						</h1>
						<h1 id="performupdateerrorLbl" style="display: none;">
							<i class="fa fa-fw fa-4x fa-times-circle"></i><br /><br />
							<b><?=$l->_("Error performing update.");?></b><br />
						</h1>
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
							<a href="../" onclick="return !window.open(this.href, 'Redeliste', 'width=500,height=500,menubar=0,scrollbars=0,status=0,titlebar=0,toolbar=0')" target="_blank" class="btn btn-secondary text-white"><i class="fa fa-fw fa-television"></i> <?=$l->_("Projector view")?></a>
							<button type="button" data-toggle="modal" data-target="#settings" class="btn btn-secondary" onclick="allowfocus(false);"><i class="fa fa-fw fa-cog"></i> <?=$l->_("Settings")?></button>
							<button type="button" data-toggle="modal" data-target="#about" class="btn btn-secondary" onclick="allowfocus(false);"><i class="fa fa-fw fa-info"></i> <?=$l->_("About")?></button>
						</div>
						<h1><?=$l->_("Speech lists")?></h1>
					</div>
					<div class="d-flex flex-row align-items-start justify-content-between">
						<div class="container-fluid">
							<h3 id="maintithead"><?=$config["default_list_title"]?></h3>
							<input type="text" class="form-control form-control-lg" placeholder="<?=$l->_("Add")?>" name="addmainname" id="addmainname" onfocus="allowfocus(false);" onfocusout="allowfocus(true);" onkeydown="checkenter('index.php?action=addmain', this);">
							<br />
							<div class="btn-group" role="group" style="display: grid;">
								<button class="btn btn-success" onclick="asyncreq('?action=finishedmain');"><i class="fa fa-fw fa-forward"></i> <?=$l->_("Next person")?></button>
								<button class="btn btn-secondary" onclick="asyncreq('?action=closemain');"><i class="fa fa-fw fa-lock"></i> <?=$l->_("Close list")?></button>
								<button type="button" data-toggle="modal" data-target="#maintruncate" class="btn btn-secondary"><i class="fa fa-fw fa-recycle"></i> <?=$l->_("Empty list")?></button>
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
								<button class="btn btn-lg btn-secondary" id="redtimeplay" onclick="asyncreq('?action=timer', 'action=start');" title="<?=$l->_("Start speech time")?>" style="display: none;"><i class="fa fa-fw fa-play"></i></button>
								<button class="btn btn-lg btn-secondary" id="redtimepause" onclick="asyncreq('?action=timer', 'action=pause');" title="<?=$l->_("Pause speech time")?>" style="display: none;"><i class="fa fa-fw fa-pause"></i></button>
								<button class="btn btn-lg btn-secondary" id="redtimereset" onclick="asyncreq('?action=timer', 'action=reset');" title="<?=$l->_("Reset speech time")?>" style="display: none;"><i class="fa fa-fw fa-undo"></i></button>
							</div>
						</div>
						<div class="container-fluid">
							<h3><?=$config["additional_title"]?></h3>
							<input type="text" class="form-control form-control-lg" placeholder="<?=$l->_("Add")?>" name="addaddname" id="addaddname" onfocus="allowfocus(false);" onfocusout="allowfocus(true);" onkeydown="checkenter('index.php?action=addadd', this);">
							<br />
							<div class="btn-group" role="group" style="display: grid;">
								<button class="btn btn-success" onclick="asyncreq('?action=finishedadd');"><i class="fa fa-fw fa-forward"></i> <?=$l->_("Next person")?></button>
								<button onclick="asyncreq('?action=closeadd');" class="btn btn-secondary"><i class="fa fa-fw fa-lock"></i> <?=$l->_("Close list")?></button>
								<button type="button" data-toggle="modal" data-target="#addtruncate" class="btn btn-secondary"><i class="fa fa-fw fa-recycle"></i> <?=$l->_("Empty list")?></button>
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
		<script>
			additional_title = "<?=$config["additional_title"]?>";
			default_list_title = "<?=$config["default_list_title"]?>";
			<?php if(isset($l->translations)) { ?>
			translations = <?=json_encode($l->translations)?>;
			<?php } else { ?>
			translations = undefined;
			<?php } ?>
		</script>
		<script src="../inc/jq/jquery.min.js"></script>
		<script src="../inc/bs/bootstrap.min.js"></script>
		<script src="../inc/gl/admin.js"></script>
	</body>
</html>
