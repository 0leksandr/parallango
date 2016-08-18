/* global languageCode */

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

function or (either, or) {
    return either ? either : or;
}

function windowHeight () {
    return window.innerHeight
        || document.documentElement.clientHeight
        || document.getElementsByTagName('body')[0].clientHeight;
}

function windowWidth () {
    return window.innerWidth
        || document.documentElement.clientWidth
        || document.getElementsByTagName('body')[0].clientWidth;
}

var onReadyMethods = [];
function onReady (callback) {
    if (callback) {
        onReadyMethods.push(callback);
    } else {
        for (var method in onReadyMethods) {
            runIf(method); // TODO: check
        }
    }
}
//$(document).ready(function () {
    onReady();
//});

function wrap ($element, $container) {
    $container = or($container, $('<div></div>'));
    $element.before($container); // TODO: for list: first().before($container); Does it work?
    var $clone = $element.clone();
    $container.append($clone);
    $element.remove();

    return {
        element: $clone,
        container: $container
    };
}

function scrollTop (callback) {
    $('body').animate(
        {scrollTop: 0},
        200,
        runIf(callback)
    );
}

function runWhen (condition, callback, timeoutMilliseconds) {
    if (condition()) {
        callback();
    } else {
        var refreshIntervalId = 0;
        refreshIntervalId = setInterval(function () {
            if (condition()) {
                callback();
                clearInterval(refreshIntervalId);
            }
        }, timeoutMilliseconds);
    }
}
