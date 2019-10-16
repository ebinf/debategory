<?php

	header("Content-type:application/json");

	$queue = json_decode(file_get_contents("queue.json"), true);

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

	if (isset($queue["add"]) && !empty($queue["add"]["current"])) {
		echo json_encode(["title" => "Änderungsantrag", "queue" => $queue["add"], "closed" => $queue["settings"]["add_closed"], "time" => $time]);
	} else if (isset($queue["main"]) && !empty($queue["main"]["current"])) {
		if (isset($queue["settings"]["title"]) && sizeof($queue["settings"]["title"]) > 0) {
			$title = $queue["settings"]["title"];
		} else {
			$title = "Hauptantrag";
		}
		echo json_encode(["title" => $title, "queue" => $queue["main"], "closed" => $queue["settings"]["main_closed"], "time" => $time]);
	} else {
		echo json_encode(["wifi_info" => (isset($queue["settings"]["wifi_info"]) ? $queue["settings"]["wifi_info"] : false)]);
	}

?>
