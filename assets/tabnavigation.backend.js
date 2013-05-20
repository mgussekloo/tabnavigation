var tabnavigationActivate = function(tabnavigationConfiguration) {
	var $=jQuery;

	var container = $("<nav id='tabnavigation' class='wide'></nav>");
	var ul = $("<ul class='content'></ul>");

	if (tabnavigationConfiguration.tabs.length <= 0) return;

	// the active tab
	var activeTab = false, unorderedGroups = [];

	// set classes for groups
	var navItems = $("#nav>ul.content>li");
	for (var x=0,max=navItems.length;x<max;x++) {
		var navItem = $(navItems[x]);
		var content = navItem.html();
		var cutoffIndex = content.toLowerCase().indexOf("<ul");
		if (cutoffIndex > 0) {
			content = content.substr(0, cutoffIndex);
		}
		content = $.trim(content);

		var className = tabnavigationConfiguration.groups[content];
		if (className) {
			navItem.addClass(className);
			if (!activeTab && navItem.is(".active")) {
				activeTab = (className.split(" "))[0];
			}
		} else {
			navItem.addClass("tabnavigation-_unsorted");
			unorderedGroups.push(content);
		}
	};

	// orphaned groups
	if (unorderedGroups.length) {
		tabnavigationConfiguration.tabs.push('Unsorted');
		tabnavigationConfiguration.slugs.push('_unsorted');
	}

	// create tabs
	for (var x=0;x<tabnavigationConfiguration.tabs.length;x++) {
		var tabName = tabnavigationConfiguration.tabs[x], slugName = tabnavigationConfiguration.slugs[x];
		$(ul).append("<li><a href='#" + slugName + "' class='tabnavigation-" + slugName + "'>" + tabName + "</a></li>");
	}
	$(container).insertBefore("nav#nav").html(ul);

	// events
	$("body").on("mouseover.tabnavigation", "#tabnavigation a", function(e) {
		e.preventDefault();
		$("#tabnavigation>ul>li.active").removeClass("active");
		$(this).parent().addClass("active");
		var tabName = $(this).attr("href").replace("#", "");
		$("#nav>ul.content>li").hide().filter(".tabnavigation-" + tabName).show();
	});

	if (activeTab) {
		$("#tabnavigation a." + activeTab).trigger("click");
	} else {
		$("#tabnavigation a").eq(0).trigger("click");
	}

}