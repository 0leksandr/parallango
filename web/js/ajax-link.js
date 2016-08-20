(function () {
    var __currentPage;

    //var __currentState;
    //var State = function (url, isNext, historyPosition, title) { // history item
    //    var $title = $('title');
    //
    //    var toDict = function () {
    //        return {
    //            url: url,
    //            isNext: isNext,
    //            historyPosition: historyPosition,
    //            title: title
    //        };
    //    };
    //    var fromDict = function (dict) {
    //        return State(
    //            dict.url,
    //            dict.isNext,
    //            dict.historyPosition,
    //            dict.title
    //        );
    //    };
    //    var save = function () { // make page returnable
    //        window.history.replaceState(
    //            JSON.stringify(toDict()),
    //            title,
    //            url
    //        );
    //    };
    //    var push = function () {
    //        window.history.pushState(
    //            JSON.stringify(toDict()),
    //            title,
    //            url
    //        );
    //        $title.html(title);
    //    };
    //    var current = function (state) {
    //        if (state !== undefined) {
    //            __currentState = state;
    //        }
    //        if (__currentState === undefined) {
    //            var url = window.location.href;
    //            var isNext = true;
    //            var historyPosition = 0;
    //            var title = $title.text(); // TODO: or document.title ?
    //            __currentState = State(url, isNext, historyPosition, title);
    //        }
    //        return __currentState;
    //    };
    //
    //    return {
    //        fromDict: fromDict,
    //        save: save,
    //        push: push,
    //        current: current,
    //        isNext: function (_isNext) {
    //            if (_isNext !== undefined) {
    //                isNext = _isNext;
    //            }
    //            return isNext;
    //        },
    //        historyPosition: function () {return historyPosition;}
    //    };
    //};
    //var Page = function (content) {
    //    var $pageContent = $("#page_content");
    //
    //    var load = function () {
    //        $pageContent.html(content);
    //    };
    //    var toDict = function () {
    //        return {
    //            content: content
    //        };
    //    };
    //    var fromDict = function (dict) {
    //        return Page(dict.content);
    //    };
    //
    //    var LoadingAnimation = function () {
    //        var isOn = false;
    //        var $loading = $("#loading_img");
    //
    //        var start = function () {
    //            if (isOn) {
    //                return;
    //            }
    //
    //            var loadingDimension = Math.pow(
    //                    Math.min(windowWidth(), windowHeight()),
    //                    0.3
    //                ) * 7;
    //            var loadingDistance = 300; // TODO: wtf?
    //            $loading.css({
    //                width: loadingDimension,
    //                height: loadingDimension
    //            });
    //            $loading.css({
    //                position: "fixed",
    //                top: (windowHeight() - loadingDimension) / 2 - loadingDistance,
    //                left: (windowWidth() - loadingDimension) / 2,
    //                opacity: 0,
    //                "z-index": 2,
    //                visibility: "visible"
    //            }).appendTo($("#page")).animate({
    //                top: "+=" + loadingDistance,
    //                opacity: 1
    //            }, 500);
    //
    //            isOn = true;
    //        };
    //
    //        var stop = function () {
    //            if (!isOn) {
    //                return;
    //            }
    //            $loading.css({visibility: "hidden"});
    //            isOn = false;
    //        };
    //
    //        return {
    //            start: start,
    //            stop: stop
    //        };
    //    };
    //
    //    var Slide = function () {
    //        var move = function (from, to, durationMilliseconds, doneCallback) {
    //            from = "" + from * 100 + "%";
    //            to = "" + to * 100 + "%";
    //            $pageContent
    //                .css(
    //                    {left: from}
    //                )
    //                .animate(
    //                    {left: to},
    //                    durationMilliseconds,
    //                    "linear", //maybe something else?
    //                    function () {
    //                        runIf(doneCallback);
    //                    }
    //                );
    //        };
    //
    //        return {
    //            out: function (toLeft, doneCallback) {
    //                move(0, toLeft ? -1 : 1, 500, doneCallback);
    //            },
    //            in: function (fromLeft, doneCallback) {
    //                scrollTop(function () {
    //                    move(fromLeft ? -1 : 1, 0, 200, doneCallback);
    //                });
    //            }
    //        };
    //    };
    //
    //    return {
    //        load: load,
    //        toDict: toDict,
    //        fromDict: fromDict,
    //        LoadingAnimation: LoadingAnimation(),
    //        Slide: Slide()
    //    };
    //};

    var Page = function (url, isNext, historyPosition, data) {
        var title, content;

        var $title = $('title'); // TODO: something
        var $pageContent = $("#page_content");

        (function () {
            if (data !== undefined) {
                title = data.title;
                content = data.content;
            }
        })();

        var load = function () {
            $title.html(title);
            $pageContent.html(content);
        };
        var current = function (currentPage) {
            if (currentPage !== undefined) {
                __currentPage = currentPage;
            }
            if (__currentPage === undefined) {
                var url = window.location.href;
                var isNext = true;
                var historyPosition = 0;
                var title = $title.text(); // TODO: or document.title ?
                var content = $pageContent.html();
                __currentPage = Page(url, isNext, historyPosition, {
                    title: title,
                    content: content
                });
            }
            return __currentPage;
        };
        var toDict = function () {
            return {
                url: url,
                isNext: isNext,
                historyPosition: historyPosition,
                title: title,
                content: content
            };
        };
        var fromDict = function (dict) {
            return Page(dict.url, dict.isNext, dict.historyPosition, dict);
        };

        var State = function () {
            var save = function () { // make page returnable
                window.history.replaceState(
                    JSON.stringify(toDict()),
                    title,
                    url
                );
            };

            var push = function () {
                window.history.pushState(
                    JSON.stringify(toDict()),
                    title,
                    url
                );
            };

            return {
                save: save,
                push: push
            };
        };

        var LoadingAnimation = function () {
            var isOn = false;
            var $loading = $("#loading_img");

            var start = function () {
                if (isOn) {
                    return;
                }

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

                isOn = true;
            };

            var stop = function () {
                if (!isOn) {
                    return;
                }
                $loading.css({visibility: "hidden"});
                isOn = false;
            };

            return {
                start: start,
                stop: stop
            };
        };

        var Slide = function () {
            var move = function (from, to, durationMilliseconds, doneCallback) {
                from = "" + from * 100 + "%";
                to = "" + to * 100 + "%";
                $pageContent
                    .css(
                        {left: from}
                    )
                    .animate(
                        {left: to},
                        durationMilliseconds,
                        "linear", //maybe something else?
                        function () {
                            runIf(doneCallback);
                        }
                    );
            };

            return {
                out: function (toLeft, doneCallback) {
                    move(0, toLeft ? -1 : 1, 500, doneCallback);
                },
                in: function (fromLeft, doneCallback) {
                    scrollTop(function () {
                        move(fromLeft ? -1 : 1, 0, 200, doneCallback);
                    });
                }
            };
        };

        return {
            load: load,
            current: current,
            fromDict: fromDict,
            State: State(),
            LoadingAnimation: LoadingAnimation(),
            Slide: Slide(),
            isNext: function (_isNext) {
                if (_isNext !== undefined) {
                    isNext = _isNext;
                }
                return isNext;
            },
            historyPosition: function () {return historyPosition;}
        };
    };

    var Link = function ($link, betweenSlidesCallback) {
        var isNext = true;

        (function () {
            var _isNext = $link.data("is-next");
            if (_isNext !== undefined) {
                isNext = _isNext === "true" || _isNext === 1;
            }
        })();

        var click = function () {
            var loaded = false;
            var address = $link.attr('href');
            var currentPage = Page().current();

            currentPage.Slide.out(isNext, function () {
                runIf(betweenSlidesCallback);
                setTimeout(function () {
                    if (!loaded) {
                        currentPage.LoadingAnimation.start();
                    }
                }, 500);
            });

            $.post(address).success(function (response) {
                loaded = true;
                currentPage.LoadingAnimation.stop();
                currentPage.State.save();
                var newPage = Page(
                    address,
                    isNext,
                    currentPage.historyPosition() + 1,
                    response
                );
                newPage.load();
                newPage.Slide.in(!isNext, onReady);
                newPage.State.push();
                Page().current(newPage);
            });
        };

        $link.click(function (event) {
            event.preventDefault();
            click();
        });
    };

    onReady(function () {
        $('a').each(function () {
            Link($(this));
        });
    });

    var restorePagesOnHistoryMove = function () {
        window.onpopstate = function (event) {
            var currentPage = Page().current();
            var oldPage = Page().fromDict(JSON.parse(event.state));
            var back;
            if (currentPage.historyPosition() > oldPage.historyPosition()) {
                back = currentPage.isNext();
            } else {
                back = !oldPage.isNext();
            }
            currentPage.Slide.out(!back, function () {
                oldPage.load();
                oldPage.Slide.in(back);
                Page().current(oldPage);
            });
        };
    };
    onReady(restorePagesOnHistoryMove);
})();
