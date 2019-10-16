lst = {};
tab = $("#redtab > tbody");
tit = $("#tit");
inf = $("#info");

$.ajaxSetup({
	url: "inc/gl/show.php",
	async: true,
	cache: false,
	success: function(data) {
		if (JSON.stringify(lst) != JSON.stringify(data)) {
			lst = data;
			tab.html("");
			if (lst["wifi_info"] != undefined) {
					inf.show();
					tab.hide();
					tit.html("");
					if (lst["wifi_info"]) {
						$("#wifiinfo").show();
					} else {
						$("#wifiinfo").hide();
					}
			} else {
				tit.html(lst["title"]);
				inf.hide();
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
					tab.append('<tr><td><h1><i class="fa fa-bolt text-primary" title="Priorisiert"></i>&nbsp;' + lst["queue"]["prio"][i] + '</h1></td></tr>');
				}
				for (i in lst["queue"]["normal"]) {
					tab.append('<tr><td><h1>' + lst["queue"]["normal"][i] + '</h1></td></tr>');
				}
				if (lst["closed"]) {
					tab.append('<tr><td><h1 title="Redeliste geschlossen."><i class="fa fa-lock"></i></h1></td></tr>');
				}
			}
			delete i;
			delete data;
		}
	}
});

$(document).ready(function() {
	$.ajax();
	setInterval(function(){ $.ajax(); }, 100);
});
