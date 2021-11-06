<script>
/*
 * metismenu - v1.1.3
 * Easy menu jQuery plugin for Twitter Bootstrap 3
 * https://github.com/onokumus/metisMenu
 *
 * Made by Osman Nuri Okumus
 * Under MIT License
 */
var maxHeight = 400;
;(function($, window, document, undefined) {

    var pluginName = "metisMenu",
        defaults = {
            toggle: true,
            doubleTapToGo: false
        };

    function Plugin(element, options) {
        this.element = $(element);
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    Plugin.prototype = {
        init: function() {

            var $this = this.element,
                $toggle = this.settings.toggle,
                obj = this;

            if (this.isIE() <= 9) {
                $this.find("li.active").has("ul").children("ul").collapse("show");
                $this.find("li").not(".active").has("ul").children("ul").collapse("hide");
            } else {
                $this.find("li.active").has("ul").children("ul").addClass("collapse in");
				setTimeout(function(){
					$("ul li ul.scrolldown li").each(function(){
						$(this).hover(function(){
							var $container = $(this).parent().parent(),
								 $list = $container.parent(),
								 $anchor = $container.parent().prev(),
								 height = $list.height() * 1.05,       // make sure there is enough room at the bottom
								 multiplier = height / maxHeight;     // needs to move faster if list is taller

							// need to save height here so it can revert on mouseout            
							$container.data("origHeight", $container.height());

							// so it can retain it's rollover color all the while the dropdown is open
							$anchor.addClass("hover");

							// make sure dropdown appears directly below parent list item    
							$list
								.show()
								.css({
									paddingTop: $container.children("a").data("origHeight")
								});

							// don't do any animation if list shorter than max
							if (multiplier > 1) {
								$container
									.css({
										height: maxHeight,
										overflow: "hidden"
									})
									.mousemove(function(e) {
										var offset = $container.offset();
										var relativeY = ((e.pageY - offset.top) * multiplier) - ($container.data("origHeight") * multiplier);
										if (relativeY > $container.data("origHeight")) {
											$list.css("top", -relativeY + $container.data("origHeight"));
										};
									});
							}
						},function(){
							var $el = $(this);

							// put things back to normal
							$el
								.height($(this).data("origHeight"))
								.find("ul")
								.css({ top: 0 })
								.hide()
								.end()
								.find("a")
								.removeClass("hover");
						});
					});
				},1000);
                $this.find("li").not(".active").has("ul").children("ul").addClass("collapse");
            }

            //add the "doubleTapToGo" class to active items if needed
            if (obj.settings.doubleTapToGo) {
                $this.find("li.active").has("ul").children("a").addClass("doubleTapToGo");
            }

            $this.find("li").has("ul").children("a").on("click" + "." + pluginName, function(e) {
                e.preventDefault();

                //Do we need to enable the double tap
                if (obj.settings.doubleTapToGo) {

                    //if we hit a second time on the link and the href is valid, navigate to that url
                    if (obj.doubleTapToGo($(this)) && $(this).attr("href") !== "#" && $(this).attr("href") !== "") {
                        e.stopPropagation();
                        document.location = $(this).attr("href");
                        return;
                    }
                }

                $(this).parent("li").toggleClass("active").children("ul").collapse("toggle");

                if ($toggle) {
                    $(this).parent("li").siblings().removeClass("active").children("ul.in").collapse("hide");
                }

            });
        },

        isIE: function() { //https://gist.github.com/padolsey/527683
            var undef,
                v = 3,
                div = document.createElement("div"),
                all = div.getElementsByTagName("i");

            while (
                div.innerHTML = "<!--[if gt IE " + (++v) + "]><i></i><![endif]-->",
                all[0]
            ) {
                return v > 4 ? v : undef;
            }
        },

        //Enable the link on the second click.
        doubleTapToGo: function(elem) {
            var $this = this.element;

            //if the class "doubleTapToGo" exists, remove it and return
            if (elem.hasClass("doubleTapToGo")) {
                elem.removeClass("doubleTapToGo");
                return true;
            }

            //does not exists, add a new class and return false
            if (elem.parent().children("ul").length) {
                 //first remove all other class
                $this.find(".doubleTapToGo").removeClass("doubleTapToGo");
                //add the class on the current element
                elem.addClass("doubleTapToGo");
                return false;
            }
        },

        remove: function() {
            this.element.off("." + pluginName);
            this.element.removeData(pluginName);
        }

    };

    $.fn[pluginName] = function(options) {
        this.each(function () {
            var el = $(this);
            if (el.data(pluginName)) {
                el.data(pluginName).remove();
            }
            el.data(pluginName, new Plugin(this, options));
        });
        return this;
    };

})(jQuery, window, document);
</script>



