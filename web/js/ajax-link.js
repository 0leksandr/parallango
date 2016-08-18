(function () {
    function deactivateLinks () {
        $("a[onclick]:not([onclick=''])").each(function () {
            $(this).click(function (event) {
                event.preventDefault();
            });
        });
    }

    var link = function ($link, betweenSlidesCallback) {
        var slideLeft = true;
        var slideWidth = "100%";
        var $pageContent = $("#page_content");

        var loadingAnimationStart = function () {
            var $loading = $("#loading_img");
            var loadingDimension = Math.pow(
                Math.min(windowWidth(), windowHeight()),
                0.3
            ) * 7;
            var loadingDistance = 300; // TODO: wtf?
            $loading.css({
                width: loadingDimension,
                height: loadingDimension
            });
            $loading.css({
                position: "fixed",
                top: (windowHeight() - loadingDimension) / 2 - loadingDistance,
                left: (windowWidth() - loadingDimension) / 2,
                opacity: 0,
                "z-index": 2,
                visibility: "visible"
            }).appendTo($("#page")).animate({
                top: "+=" + loadingDistance,
                opacity: 1
            }, 500);
        };
        var loadingAnimationStop = function () {
            var $loading = $("#loading_img");
            $loading.css({visibility: "hidden"});
        };

        var showContent = function (content) {
            $pageContent.html(content);
            $pageContent
                .css({
                    left: (slideLeft ? "" : "-") + slideWidth
                })
                .animate(
                    {left: 0},
                    200,
                    function () {
                        onReady();
                    }
                );
        };

        $link.click(function (event) {
            event.preventDefault();

            var loaded = false;
            var loadingStarted = false;
            $.post($link.attr('href'))
                .success(function (response) {
                    loaded = true;
                    if (loadingStarted) {
                        loadingAnimationStop();
                    }
                    showContent(response);
                });

            var endState = {left: (slideLeft ? "-" : "") + slideWidth};
            $pageContent.animate(
                endState,
                500,
                "linear", //maybe something else?
                function () {
                    $pageContent.css(endState); // TODO: remove?
                    scrollTop(function () {
                        runIf(betweenSlidesCallback);
                        setTimeout(function () {
                            if (!loadingStarted && !loaded) {
                                loadingStarted = true;
                                loadingAnimationStart();
                            }
                        }, 500);
                    });
                }
            );
        });
    };

    //onReady(deactivateLinks);
    $('a').each(function () {
        link($(this));
    });
})();
