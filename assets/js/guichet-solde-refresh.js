/**
 * Refresh SOLDE guichet pendant la session (poll + focus + événement).
 * Badges : .js-guichet-solde[data-solde-url][data-solde-field]
 */
(function (w, d) {
    'use strict';

    var POLL_MS = 25000;
    var timer = null;
    var inFlight = false;
    var lastByUrl = {};

    function nodes() {
        return d.querySelectorAll('.js-guichet-solde[data-solde-url]');
    }

    function decodeEntities(html) {
        var t = d.createElement('textarea');
        t.innerHTML = html || '';
        return t.value;
    }

    function applyPayload(url, data) {
        var list = nodes();
        var i;
        var el;
        var field;
        var val;
        var prefix;
        var suffix;
        for (i = 0; i < list.length; i++) {
            el = list[i];
            if (el.getAttribute('data-solde-url') !== url) {
                continue;
            }
            field = el.getAttribute('data-solde-field') || 'formatted';
            if (!data || typeof data[field] === 'undefined' || data[field] === null) {
                continue;
            }
            val = String(data[field]);
            prefix = decodeEntities(el.getAttribute('data-solde-prefix') || '');
            suffix = decodeEntities(el.getAttribute('data-solde-suffix') || '');
            el.textContent = prefix + val + suffix;
        }
    }

    function urls() {
        var map = {};
        var list = nodes();
        var i;
        var u;
        for (i = 0; i < list.length; i++) {
            u = list[i].getAttribute('data-solde-url');
            if (u) {
                map[u] = true;
            }
        }
        return Object.keys(map);
    }

    function fetchOne(url) {
        var fetchFn = function () {
            return fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }).then(function (res) {
                if (!res.ok) {
                    throw new Error('solde http ' + res.status);
                }
                return res.json();
            }).then(function (data) {
                if (data && data.ok) {
                    lastByUrl[url] = data;
                    applyPayload(url, data);
                }
            });
        };

        if (w.GuichetLoadScheduler && typeof w.GuichetLoadScheduler.deferFetch === 'function') {
            return w.GuichetLoadScheduler.deferFetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            }, 40).then(function (res) {
                if (!res.ok) {
                    throw new Error('solde http ' + res.status);
                }
                return res.json();
            }).then(function (data) {
                if (data && data.ok) {
                    lastByUrl[url] = data;
                    applyPayload(url, data);
                }
            });
        }

        return fetchFn();
    }

    function refreshAll() {
        var list = urls();
        if (!list.length || inFlight || d.hidden) {
            return Promise.resolve();
        }
        inFlight = true;
        return Promise.all(list.map(function (url) {
            return fetchOne(url).catch(function () {});
        })).then(function () {
            inFlight = false;
        }, function () {
            inFlight = false;
        });
    }

    function startPoll() {
        if (timer) {
            return;
        }
        timer = w.setInterval(function () {
            refreshAll();
        }, POLL_MS);
    }

    function stopPoll() {
        if (timer) {
            w.clearInterval(timer);
            timer = null;
        }
    }

    w.GuichetSolde = {
        refresh: refreshAll,
        start: startPoll,
        stop: stopPoll
    };

    function boot() {
        if (!nodes().length) {
            return;
        }
        startPoll();
        d.addEventListener('visibilitychange', function () {
            if (!d.hidden) {
                refreshAll();
            }
        });
        w.addEventListener('focus', function () {
            refreshAll();
        });
        d.addEventListener('guichet:solde-refresh', function () {
            refreshAll();
        });
        // Premier tick différé (laisse la page stabiliser).
        w.setTimeout(function () {
            refreshAll();
        }, 4000);
    }

    if (d.readyState === 'loading') {
        d.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window, document);
