(function () {
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

            //var wrap = function ($container) {
            //    $container = or($container, $('<div></div>'));
            //    first().before($container);
            //    var $clone = $items.clone();
            //    $container
            //        .append($clone)
            //        .append($('<div class="af"></div>')); // TODO: fix me maybe?
            //    $items.remove();
            //    $items = $clone;
            //
            //    return $container;
            //};
            var _wrap = function ($container) {
                var wrapped = wrap($items, $container);
                $items = wrapped.element;
                $container = wrapped.container;
                $container.append($('<div class="af"></div>')); // TODO: fix me maybe?
                return $container;
            };

            return {
                first: first,
                last: last,
                wrap: _wrap,
                $: function () { return $items; }
            };
        };

        var load = function () {
            var prepend = function ($newItems) {
                var $container = $newItems.wrap();
                $container.css({display: "hidden"});
                var _bottomSpace = bottomSpace();
                $elem.find(".items").append($container);
                if (_bottomSpace > 0) {
                    $container.css({display: "display", height: 0});
                    show($newItems);
                } else {
                    $container.css({display: "display"});
                }
            };

            loading = true;
            var uri = $elem.data("upload-url-prefix") + (curIndex + 1);
            $.post(uri).success(function (response) {
                var $response = $(response);

                if ($response.size() === 0) {
                    fullyLoaded = true;
                    return;
                }

                prepend(items($response));
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
                duration: 5000,
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
            var durationMultiplier = 700;
            var options = {
                //duration: 500,
                easing: "linear"
            };
            $items = or($items, items());
            var $container = $items.$().parent();

            var itemHeight = intval($items.first().css("height"));
            var nrItems = $items.$().size();
            var containerHeight = itemHeight * nrItems;

            // TODO: fix empty space under items
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
    //var authors = list($('#authors'));
    //authors.hide(null, authors.show);
})();
