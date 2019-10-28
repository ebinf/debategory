cache = {};
admlstmain = "";
admlstadd = "";
admlstprev = "";
settings = {};
allowfoc = true;
timer = null;
interv = -1;

function l(translatestring) {
	if (translations == undefined) {
		return translatestring;
	} else {
		return translations[translatestring];
	}
}

function asyncreq(varurl, params="") {
	$.ajax({
		url: varurl,
		async: true,
		cache: false,
		method: "POST",
		data: params,
		complete: function() {
			autofocus(); getConts();
		}
	});
}

function getConts() {
	$.ajax({
		url: "../inc/gl/queue.json",
		async: true,
		cache: false,
		success: function(data) {
			if (JSON.stringify(cache) != JSON.stringify(data)) {
				if (cache["update"] != undefined && data["update"] == undefined) {
					location.reload();
				} else if (data["update"] != undefined) {
					clearInterval(timer);
					$("#performupdateDia").modal("show");
				}
				cache = data;
				tabmain(cache["main"]);
				tabadd(cache["add"]);
				if (cache["add"]["current"]) {
					tabprev({"queue": cache["add"], "closed": cache["settings"]["add_closed"], "title": additional_title});
				} else if (cache["main"]["current"]) {
					if (cache["settings"]["title"].length > 0) {
						title = cache["settings"]["title"];
					} else {
						title = "Hauptantrag";
					}
					tabprev({"queue": cache["main"], "closed": cache["settings"]["main_closed"], "title": title});
				} else {
					tabprev({"queue": [], "closed": false, "title": ""});
				}
				settingschanged(cache["settings"]);
				redtime();
				autofocus();
			}
		}
	});
}

function tabmain(data) {
	admtabmain = $("#redtab-main > tbody");
	admtabmain.html("");
	if (data["current"]) {
		admtabmain.append('<tr class="table-success"><td>' + data["current"] + '<a onclick="asyncreq(\'?action=finishedmain\');" title="' + l("Speech finished") + '" class="lnk pull-right"><i class="fa fa-check fa-fw text-success"></i></a></td></tr>');
	}
	for (i in data["prio"]) {
		admtabmain.append('<tr class="table-primary"><td><i class="fa fa-bolt text-primary" title="' + l("Prioritised") + '"></i>&nbsp;' + data["prio"][i] + '<a onclick="asyncreq(\'?action=delmain&q=prio&id=' + i + '\')" title="' + l("Remove from list") + '" class="lnk pull-right"><i class="fa fa-trash fa-fw text-danger"></i></a></td></tr>');
	}
	for (i in data["normal"]) {
		admtabmain.append('<tr><td>' + data["normal"][i] + '<a onclick="asyncreq(\'?action=delmain&q=normal&id=' + i + '\')" title="' + l("Remove from list") + '" class="lnk pull-right"><i class="fa fa-trash fa-fw text-danger"></i></a><a onclick="asyncreq(\'?action=priomain&id=' + i + '\')" title="' + l("Prioritise") + '" class="lnk pull-right"><i class="fa fa-bolt fa-fw text-primary"></i></a></td></tr>');
	}
	if (cache["settings"]["main_closed"]) {
		admtabmain.append('<tr><td><i class="text-muted" style="text-transform: none;">' + l("List closed.") + '</i><a onclick="asyncreq(\'?action=reopenmain\')" title="' + l("Reopen") + '" class="lnk pull-right"><i class="fa fa-unlock fa-fw text-muted"></i></a></td></tr>');
	}
}

function tabadd(data) {
	admtabadd = $("#redtab-add > tbody");
	admtabadd.html("");
	if (data["current"]) {
		admtabadd.append('<tr class="table-success"><td>' + data["current"] + '<a onclick="asyncreq(\'?action=finishedadd\');" title="' + l("Speech finished") + '" class="lnk pull-right"><i class="fa fa-check fa-fw text-success"></i></a></td></tr>');
	}
	for (i in data["prio"]) {
		admtabadd.append('<tr class="table-primary"><td><i class="fa fa-bolt text-primary" title="'+ l("Prioritised") +'"></i>&nbsp;' + data["prio"][i] + '<a onclick="asyncreq(\'?action=deladd&q=prio&id=' + i + '\')" title="' + l("Remove from list") + '" class="lnk pull-right"><i class="fa fa-trash fa-fw text-danger"></i></a></td></tr>');
	}
	for (i in data["normal"]) {
		admtabadd.append('<tr><td>' + data["normal"][i] + '<a onclick="asyncreq(\'?action=deladd&q=normal&id=' + i + '\')" title="' + l("Remove from list") + '" class="lnk pull-right"><i class="fa fa-trash fa-fw text-danger"></i></a><a onclick="asyncreq(\'?action=prioadd&id=' + i + '\')" title="' + l("Prioritise") + '" class="lnk pull-right"><i class="fa fa-bolt fa-fw text-primary"></i></a></td></tr>');
	}
	if (cache["settings"]["add_closed"]) {
		admtabadd.append('<tr><td><i class="text-muted" style="text-transform: none;">' + l("List closed.") + '</i><a onclick="asyncreq(\'?action=reopenadd\')" title="' + l("Reopen") + '" class="lnk pull-right"><i class="fa fa-unlock fa-fw text-muted"></i></a></td></tr>');
	}
}

function tabprev(data) {
	admtabprev = $("#redtab-prev > tbody");
	admtabprev.html("");
	if (data["queue"]["current"]) {
		admtabprev.append('<tr><td><h1 class="display-3 activered">' + data["queue"]["current"] + '</h1></td></tr>');
	}
	for (i in data["queue"]["prio"]) {
		admtabprev.append('<tr><td><h1 class="display-3"><i class="fa fa-bolt text-primary" title="' + l("Prioritised") + '"></i>&nbsp;' + data["queue"]["prio"][i] + '</h1></td></tr>');
	}
	for (i in data["queue"]["normal"]) {
		admtabprev.append('<tr><td><h1 class="display-3">' + data["queue"]["normal"][i] + '</h1></td></tr>');
	}
	if (data["closed"]) {
		admtabprev.append('<tr><td><h1 title="' + l("List closed.") + '"><i class="fa fa-lock"></i></h1></td></tr>');
	}
	$("#tit").html(data["title"]);
}

function settingschanged(data) {
	if (settings != data) {
		settings = data;
		if (settings["time_minutes"] > 0 || settings["time_seconds"] > 0) {
			$("#actredtime").prop('checked', true);
			$("#redtimemin").val(settings["time_minutes"]);
			$("#redtimesek").val(settings["time_seconds"]);
			$("#redtimelabel").html(String(settings["time_minutes"]).padStart(2, "0") + ":" + String(settings["time_seconds"]).padStart(2, "0"));
			$("#redtimepanel").show();
		} else {
			$("#actredtime").prop('checked', false);
			$("#redtimemin").val(0);
			$("#redtimesek").val(0);
			$("#redtimepanel").hide();
		}
		if (settings["title"].length > 0) {
			$("#maintitinp").val(settings["title"]);
			$("#maintithead").html(settings["title"]);
		} else {
			$("#maintitinp").val(default_list_title);
			$("#maintithead").html(default_list_title);
		}
		toggleredtime();
	}
}

function checkenter(url, obj) {
	if (event.keyCode == 13) {
		allowfoc = true;
		asyncreq(url, obj.name + "=" + obj.value);
		obj.value = "";
		allowfocus(true);
		autofocus();
	}
}

function savesettings() {
	if ($("#maintitinp").val().length > 0) {
		titlemain = $("#maintitinp").val();
	} else {
		titlemain = default_list_title;
	}
	if ($("#actredtime").prop('checked')) {
		if (isNaN(parseInt($("#redtimemin").val()))) {
			$("#redtimemin").val("0");
		}
		if (isNaN(parseInt($("#redtimesek").val()))) {
			$("#redtimesek").val("0");
		}
		if (parseInt($("#redtimemin").val()) > 0 || parseInt($("#redtimesek").val()) > 0) {
			asyncreq("?action=savesettings", "minutes=" + $("#redtimemin").val() + "&seconds=" + $("#redtimesek").val() + "&title=" + titlemain);
		} else {
			$("#actredtime").prop('checked', true);
			$("#redtimemin").val("");
			$("#redtimesek").val("");
			$("#redtimepanel").hide();
			asyncreq("?action=savesettings", "title=" + titlemain);
		}
		toggleredtime();
	} else {
		asyncreq("?action=savesettings", "title=" + titlemain);
	}
	getConts();
}

function toggleredtime() {
	if ($("#actredtime").prop('checked')) {
		$("#redtimemin").prop('disabled', false);
		$("#redtimesek").prop('disabled', false);
	} else {
		$("#redtimemin").prop('disabled', true);
		$("#redtimesek").prop('disabled', true);
		$("#redtimemin").val("");
		$("#redtimesek").val("");
	}
}

function redtime() {
	if (cache["timer"] != undefined) {
		if (cache["timer"]["started"] <= 0) {
			current = cache["timer"]["current"];
			if (cache["timer"]["started"] == -1) {
				$("#redtimeplay").fadeOut();
				$("#redtimepause").fadeOut();
				$("#redtimereset").fadeOut();
			} else {
				$("#redtimeplay").fadeIn("fast");
				$("#redtimepause").hide();
				$("#redtimereset").fadeIn("fast");
			}
		} else {
			$("#redtimeplay").hide();
			$("#redtimereset").hide();
			$("#redtimepause").fadeIn("fast");
			passed = today.valueOf().toString().slice(0, -3) - cache["timer"]["started"];
			current = cache["timer"]["current"] - passed;
		}
		if (current <= 0) {
			mins = 0;
			secs = 0;
			clearTimeout(timer);
			timer = null;
		} else {
			secs = current % 60;
			mins = (current - secs) / 60;
		}
		$("#redtimelabel").html(checkTime(mins) + ":" + checkTime(secs));
		if (current <= 0) {
			$("#redtimelabel").removeClass("text-warning");
			$("#redtimelabel").addClass("bg-danger");
			$("#redtimelabel").addClass("text-light");
		} else if (current <= 10) {
			$("#redtimelabel").removeClass("bg-danger");
			$("#redtimelabel").removeClass("text-light");
			$("#redtimelabel").addClass("text-warning");
		} else {
			$("#redtimelabel").removeClass("text-warning");
			$("#redtimelabel").removeClass("bg-danger");
			$("#redtimelabel").removeClass("text-light");
		}
		if (timer == null) {
			timer = setInterval(function() { redtime(); }, 500);
		}
	} else {
		if (timer != null) {
			clearTimeout(timer);
			timer = null;
		}
	}
}


function autofocus() {
	if (allowfoc == true) {
		window.setTimeout(function (){
			if (cache["add"]["current"]) {
				$("#addaddname").focus();
			} else {
				$("#addmainname").focus();
			}
		}, 50);
	}
}

function allowfocus(varx) {
	allowfoc = varx;
}

function checkTime(varx) {
	return ("00" + varx).slice(-2);
}

function startTime() {
	today = new Date();
	h = checkTime(today.getHours());
	m = checkTime(today.getMinutes());
	s = checkTime(today.getSeconds());
	$("#timelabel").html(h + ":" + m + ":" + s);
}

function checkforupdates() {
	$("#checkforupdatesBtn").hide();
	$("#checkforupdatesBtnDisabled").show();
	$.ajax({
		url: "?action=checkforupdates",
		async: true,
		cache: false,
		method: "GET",
		complete: function(data) {
			data = data.responseJSON;
			if (data["status"] == "error") {
				if (data["message"] == undefined) {
					$("#updateerrorLbl").html(l("Unknown error."));
				} else {
					$("#updateerrorLbl").html(data["message"]);
				}
				$("#updateerrorMsg").show();
			} else if (data["status"] == "no updates") {
				$("#noupdateMsg").show();
			} else {
				$("#updatedialogNew").html(data["version"]);
				$("#updatedialogChn").html("");
				for (var i = 0; i < data["changes"].length; i++) {
					$("#updatedialogChn").append("<li>" + data["changes"][i] + "</li>");
				}
				$("#updateseed").val(data["seed"]);
				$("#updatedialog").modal("show");
			}
			$("#about").modal("hide");
			$("#checkforupdatesBtn").show();
			$("#checkforupdatesBtnDisabled").hide();
		}
	});
}

function performupdate() {
	clearInterval(interv);
	clearInterval(timer);
	$("#performupdateDia").modal("show");
	$.ajax({
		url: "?action=performupdate&seed=" + $("#updateseed").val(),
		async: true,
		cache: false,
		method: "GET",
		complete: function(data) {
			data = data.responseJSON;
			if (data["status"] == "finished") {
				location.reload();
			} else {
				$("#performupdateLbl").hide();
				$("#performupdateerrorLbl").append(data["message"]);
				$("#performupdateerrorLbl").show();
			}
		}
	});
}

$(document).ready(function() {
	interv = setInterval(function() { startTime(); getConts(); }, 500);
});
