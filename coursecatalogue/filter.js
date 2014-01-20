/**
 * @namespace
 */
M.filter_coursecatalogue = M.filter_coursecatalogue || {};

/**
 * This function is initialized from PHP
 *
 * @param {Object} Y YUI instance
 */
M.filter_coursecatalogue.init = function(Y) {
	// tabbify links
	Y.all("#tab-links a").on('click', function(e) {
		e.preventDefault();
		e.target.ancestor("ul").all("li").removeClass("active");
		e.target.ancestor("li").addClass("active");
		Y.one("#tab-bodies").all("div[id^=tab_]").setStyles({
			"opacity":1,
			"display":"none"
		}); // .hide() fades??? dumb
		Y.one(e.target.getAttribute("href")).setStyles({
			"opacity":1,
			"display":"block"
		});
	});
}

