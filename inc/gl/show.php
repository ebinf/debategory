<?php

	header("Content-type: application/json");

	$queue = json_decode(file_get_contents("queue.json"), true);

	if (isset($_GET["action"]) && $_GET["action"] == "raisehand") {
		$config = require("../../config.inc.php");
		if (!$config["self_service"]) {
			die("false");
		}
		session_start();
		if (!isset($_SESSION["self_service_name"]) || empty($_SESSION["self_service_name"])) {
			die("false");
		}
		$list = "main";
		if (!empty($queue["add"]["current"]) || !empty($queue["add"]["normal"])) {
			$list = "add";
		}
		if ($queue["settings"][$list . "_closed"]) {
			die("false");
		}
		if (empty($queue[$list]["current"])) {
			if (isset($queue["timer"]) && empty($queue["add"]["current"])) {
				$queue["timer"]["started"] = time();
				$queue["timer"]["current"] = ($queue["settings"]["time_minutes"] * 60) + $queue["settings"]["time_seconds"];
			}
			$queue[$list]["current"] = trim($_SESSION["self_service_name"]);
		} else {
			$queue[$list]["normal"][] = trim($_SESSION["self_service_name"]);
		}
		file_put_contents("../../inc/gl/queue.json", json_encode($queue, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
		die("true");
	}

	if (isset($queue["timer"]["current"]) && isset($queue["timer"]["started"])) {
		if ($queue["timer"]["started"] <= 0) {
			$curr = $queue["timer"]["current"];
		} else {
			$passed = time() - $queue["timer"]["started"];
			$curr = $queue["timer"]["current"] - $passed;
		}
		if ($curr > 0) {
			$secs = $curr % 60;
			$mins = ($curr - $secs) / 60;
			$time = ["minutes" => $mins, "seconds" => $secs];
		} else {
			$time = ["minutes" => 0, "seconds" => 0];
		}
	} else {
		$time = [];
	}

	if (isset($queue["update"])) {
		echo json_encode(["update" => true]);
	} elseif (isset($queue["add"]) && !empty($queue["add"]["current"])) {
		echo json_encode(["title" => "Änderungsantrag", "queue" => $queue["add"], "closed" => $queue["settings"]["add_closed"], "time" => $time]);
	} else if (isset($queue["main"]) && !empty($queue["main"]["current"])) {
		if (isset($queue["settings"]["title"]) && strlen($queue["settings"]["title"]) > 0) {
			$title = $queue["settings"]["title"];
		} else {
			$title = "Hauptantrag";
		}
		echo json_encode(["title" => $title, "queue" => $queue["main"], "closed" => $queue["settings"]["main_closed"], "time" => $time]);
	} else {
		echo json_encode([]);
	}

?>
