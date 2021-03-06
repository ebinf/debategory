lst = {};
tab = $("#redtab > tbody");
tit = $("#tit");
inf = $("#info");
interv = -1;

function doAjax() {
	$.ajax({
		url: "inc/gl/show.php",
		async: true,
		cache: false,
		success: function(data) {
			if (JSON.stringify(lst) != JSON.stringify(data)) {
				lst = data;
				tab.html("");
				$("#raisehand_btn").removeClass("disabled");
				$("#raisehand_ico").removeClass();
				$("#raisehand_ico").addClass("fa fa-hand-paper-o");
				if (lst.length == 0) {
						inf.addClass("d-flex");
						inf.show();
						tab.hide();
						tit.html("");
				} else if (lst["update"] != undefined) {
					$("#updateMsg").show();
					$("#normalCnt").hide();
					clearInterval(interv);
					interv = -1;
					setTimeout(function() { location.reload(); }, 60000);
				} else {
					tit.html(lst["title"]);
					inf.hide();
					inf.removeClass("d-flex");
					tab.show();
					if (lst["queue"]["current"]) {
						if (lst["time"]["minutes"] >= 0 || lst["time"]["seconds"] >= 0) {
							if ((lst["time"]["minutes"] + lst["time"]["seconds"]) == 0) {
								tab.append('<tr><td><h1 class="activered text-muted">' + lst["queue"]["current"] + ' <small id="redtimelbl">00:00</small></h1></td></tr>');
							} else {
								timelabel = ("00" + lst["time"]["minutes"]).slice(-2) + ":" + ("00" + lst["time"]["seconds"]).slice(-2)
								tab.append('<tr><td><h1 class="activered">' + lst["queue"]["current"] + ' <small id="redtimelbl">' + timelabel + '</small></h1></td></tr>');
							}
						} else {
							tab.append('<tr><td><h1 class="activered">' + lst["queue"]["current"] + '</h1></td></tr>');
						}
					}
					for (i in lst["queue"]["prio"]) {
						tab.append('<tr><td><h1><i class="fa fa-bolt text-primary" title="' + l_prioritised + '"></i>&nbsp;' + lst["queue"]["prio"][i] + '</h1></td></tr>');
					}
					for (i in lst["queue"]["normal"]) {
						tab.append('<tr><td><h1>' + lst["queue"]["normal"][i] + '</h1></td></tr>');
					}
					if (lst["closed"]) {
						tab.append('<tr><td><h1 title="' + l_list_closed + '"><i class="fa fa-lock"></i></h1></td></tr>');
						$("#raisehand_btn").addClass("disabled");
						$("#raisehand_ico").removeClass();
						$("#raisehand_ico").addClass("fa fa-lock");
					}
				}
				delete i;
				delete data;
			}
		}
	});
}

$(document).ready(function() {
	doAjax();
	interv = setInterval(function(){ doAjax(); }, 100);
});

$("#raisehand_btn").click(function() {
	$.ajax({
		url: "inc/gl/show.php?action=raisehand",
		async: true
	});
});
