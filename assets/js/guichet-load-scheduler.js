(function (w) {
    'use strict';

    var queue = [];
    var running = false;

    function drain() {
        if (running || !queue.length) {
            return;
        }
        running = true;
        var job = queue.shift();
        Promise.resolve()
            .then(job.fn)
            .catch(function () {})
            .then(function () {
                running = false;
                setTimeout(drain, job.gap || 60);
            });
    }

    w.GuichetLoadScheduler = {
        enqueue: function (fn, gap) {
            queue.push({ fn: fn, gap: gap || 60 });
            drain();
        },
        deferFetch: function (url, options, gap) {
            var self = this;
            return new Promise(function (resolve, reject) {
                self.enqueue(function () {
                    return fetch(url, options).then(resolve, reject);
                }, gap);
            });
        }
    };
})(window);
