/* global languageCode */

(function () {
    function intval (a) {
        var res = parseInt(a);
        if (isNaN(res)) {
            res=0;
        }
        return res;
    }

    function runIf (callback) {
        if (typeof callback === 'function') {
            callback();
        }
    }

    function filterEmptyNodes (array) {
        var res = [];
        $(array).each(function (key) {
            var elem = array[key];
            if (elem.nodeName !== '#text' || elem.data.trim()) {
                res.push(elem);
            }
        });
        return res;
    }

    function or (either, or) {
        return either ? either : or;
    }

    function windowHeight () {
        return window.innerHeight
            || document.documentElement.clientHeight
            || document.getElementsByTagName('body')[0].clientHeight;
    }

    function offsetBottom () {
        return $(window).scrollTop() + windowHeight();
    }

    var list = function ($elem) {
        var curIndex = 0;
        var loading = false;
        var fullyLoaded = false;

        setInterval(function () {
            if (shouldLoad()) {
                load();
            }
        }, 1000);

        var items = function ($items) {
            $items = or($items, $elem.find(".item"));

            var first = function () {
                return $($items[0]);
            };

            var last = function () {
                return $($items[$items.size() - 1]);
            };

            var wrap = function ($container) {
                $container = or($container, $('<div></div>'));
                first().before($container);
                var $clone = $items.clone();
                $container
                    .append($clone)
                    .append($('<div class="af"></div>')); // TODO: fix me maybe?
                $items.remove();
                $items = $clone;

                return $container;
            };

            return {
                first: first,
                last: last,
                wrap: wrap,
                $: function () { return $items; }
            };
        };

        var load = function () {
            loading = true;
            var uri = '/' + languageCode + '/' + $elem.data('type') + '/' +
                (curIndex + 1);
            $.post(uri).success(function (response) {
                var $response = $(filterEmptyNodes($(response)));
                if ($response.size() === 0) {
                    fullyLoaded = true;
                    return;
                }

                var $newItems = items($response);
                var $container = $newItems.wrap();
                $container.css({display: 'hidden'});
                var _bottomSpace = bottomSpace();
                $elem.find(".items").append($container);
                if (_bottomSpace > 0) {
                    $container.css({display: 'display', height: 0});
                    show($newItems);
                } else {
                    $container.css({display: 'display'});
                }
                curIndex++;
                loading = false;
            });
        };

        var shouldLoad = function () {
            return !fullyLoaded && !loading && bottomSpace() > -300;
        };

        var bottomSpace = function () {
            var offsetTop = $elem.offset().top;
            var height = parseInt($elem.css("height"));
            var elemOffsetBottom = offsetTop + height;
            return offsetBottom() - elemOffsetBottom;
        };

        var hide = function ($items, done_callback) {
            var options = {
                duration: 500,
                easing: "linear"
            };
            $items = or($items, items());
            var $container = $items.wrap();
            $container.css({overflow: "hidden"});
            $items.$().animate(
                {top: -intval($container.css("height"))},
                options.duration,
                options.easing
            );
            $container.animate(
                {height: 0},
                options.duration,
                options.easing,
                function () {
                    runIf(done_callback);
                }
            );
            $elem.removeClass("active").addClass("inactive"); // TODO: what about case when hiding not all items?
        };

        var show = function ($items, done_callback) {
            var durationMultiplier = 70;
            var options = {
                //duration: 500,
                easing: "linear"
            };
            $items = or($items, items());
            var $container = $items.$().parent();

            var itemHeight = intval($items.first().css("height"));
            var nrItems = $items.$().size();
            var containerHeight = itemHeight * nrItems;

            //$items.$().each(function (index) {
            //    index++;
            //    var duration = index * durationMultiplier;
            //    $(this).css({
            //        top: -index * itemHeight
            //    }).animate(
            //        {top: 0},
            //        duration,
            //        options.easing
            //    );
            //});
            $items.$().css({top: 0});

            var totalDuration = durationMultiplier * (nrItems + 1);
            $container.animate(
                {height: containerHeight},
                totalDuration,
                options.easing,
                function () {
                    //$elem.css({height: "auto"});
                    runIf(done_callback);
                }
            );

            // TODO: move out of container
            $elem.removeClass("inactive").addClass("active");
        };

        return {
            $: function () { return $elem; },
            load: load,
            hide: hide,
            show: show,
            items: items
        };
    };

    $('.list').each(function () {
        list($(this));
    });
})();
