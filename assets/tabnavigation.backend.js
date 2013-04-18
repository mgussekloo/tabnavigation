var tabnavigationActivate = function(tabnavigationConfiguration) {
	var $=jQuery;

	var container = $("<nav id='tabnavigation' class='wide'></nav>");
	var ul = $("<ul class='content'></ul>");
console.log(tabnavigationConfiguration);
	if (tabnavigationConfiguration.tabs.length <= 0) return;

	for (var x=0;x<tabnavigationConfiguration.tabs.length;x++) {
		var tabName = tabnavigationConfiguration.tabs[x], slugName = tabnavigationConfiguration.slugs[x];
		$(ul).append("<li><a href='#" + slugName + "' class='tabnavigation-" + slugName + "'>" + tabName + "</a></li>");
	}

	$(container).insertBefore("nav#nav").html(ul);

	// the active tab
	var activeTab = false;;

	// prepare classes for groups
	$("#nav ul>li").each(function() {
		var content = $(this).html();
		var cutoffIndex = content.indexOf("<ul");
		if (cutoffIndex > 0) {
			content = content.substr(0, cutoffIndex);
		}
		content = content.trim();
		var className = tabnavigationConfiguration.groups[content];
		if (className) {
			$(this).addClass("tabnavigation-" + className);
			if (!activeTab && $(this).is(".active")) {
				activeTab = className;
			}
		}
	});


	$("body").on("click.tabnavigation", "#tabnavigation a", function(e) {
		e.preventDefault();
		$("#tabnavigation li.active").removeClass("active"); $(this).parent().addClass("active");

		var tabName = $(this).attr("href").replace("#", "");
		$("#nav>ul>li").hide().filter(".tabnavigation-" + tabName).show();
	});

	if (activeTab) {
		$("#tabnavigation a.tabnavigation-" + activeTab).trigger("click");
	} else {
		$("#tabnavigation a").eq(0).trigger("click");
	}

}