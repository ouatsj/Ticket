(function (w, doc) {
    'use strict';

    var pending = [];
    var hooked = false;
    var origAdd = doc.addEventListener.bind(doc);

    doc.addEventListener = function (type, listener, options) {
        if (type === 'DOMContentLoaded' && typeof listener === 'function' && hooked) {
            pending.push(listener);
            return;
        }
        return origAdd(type, listener, options);
    };

    hooked = true;

    function runChunk(start) {
        var end = Math.min(start + 2, pending.length);
        for (var i = start; i < end; i++) {
            try {
                pending[i]();
            } catch (e) {}
        }
        if (end < pending.length) {
            var sched = w.requestIdleCallback || function (cb) {
                setTimeout(cb, 30);
            };
            sched(function () {
                runChunk(end);
            });
        }
    }

    origAdd('DOMContentLoaded', function () {
        hooked = false;
        if (!pending.length) {
            return;
        }
        runChunk(0);
    });
})(window, document);
