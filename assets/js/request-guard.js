(function (w) {
    'use strict';

    var debounceTimers = {};
    var xhrInflight = {};

    w.AppRequestGuard = {
        debounce: function (key, fn, ms) {
            clearTimeout(debounceTimers[key]);
            debounceTimers[key] = setTimeout(fn, ms || 400);
        },

        getJson: function (url, key, onLoad, onFail) {
            if (xhrInflight[key]) {
                try {
                    xhrInflight[key].abort();
                } catch (e) {}
            }

            var xhr = new XMLHttpRequest();
            xhrInflight[key] = xhr;
            xhr.open('GET', url, true);
            xhr.onload = function () {
                delete xhrInflight[key];
                if (xhr.status >= 200 && xhr.status < 300) {
                    onLoad(xhr);
                } else if (typeof onFail === 'function') {
                    onFail(xhr);
                }
            };
            xhr.onerror = function () {
                delete xhrInflight[key];
                if (typeof onFail === 'function') {
                    onFail(xhr);
                }
            };
            xhr._rgSkipGuard = true;
            xhr.send();
        },

        phoneDigits: function (value) {
            return String(value || '').replace(/\D/g, '');
        },

        phonesMatch: function (a, b) {
            var da = this.phoneDigits(a);
            var db = this.phoneDigits(b);
            if (!da || !db) {
                return false;
            }
            if (da === db) {
                return true;
            }
            return da.length >= 8 && db.length >= 8 && da.slice(-8) === db.slice(-8);
        },

        guardForm: function (selector) {
            var form = document.querySelector(selector);
            if (!form || form.dataset.guarded === '1') {
                return;
            }
            form.dataset.guarded = '1';

            var clearSubmitterMirror = function () {
                form.querySelectorAll('input[data-rg-submitter="1"]').forEach(function (el) {
                    el.parentNode.removeChild(el);
                });
            };

            var resetGuard = function () {
                delete form.dataset.submitting;
                clearSubmitterMirror();
                form.querySelectorAll('input[type="submit"], button[type="submit"]').forEach(function (btn) {
                    btn.disabled = false;
                });
            };

            // Mémorise le bouton cliqué (navigateurs sans SubmitEvent.submitter).
            form.addEventListener('click', function (ev) {
                var t = ev.target;
                if (!t) {
                    return;
                }
                if (t.matches && t.matches('input[type="submit"], button[type="submit"]')) {
                    form._rgLastSubmitter = t;
                }
            }, true);

            form.addEventListener('submit', function (ev) {
                if (form.dataset.submitting === '1') {
                    ev.preventDefault();
                    return false;
                }
                form.dataset.submitting = '1';

                // Un bouton disabled n'est PAS envoyé dans le POST. Or on désactive
                // les submit juste après — il faut donc mirorer name/value EPSON etc.
                var submitter = ev.submitter || form._rgLastSubmitter || null;
                if (submitter && submitter.form === form
                    && (submitter.type === 'submit' || submitter.type === 'image')) {
                    var name = submitter.getAttribute('name');
                    if (name) {
                        clearSubmitterMirror();
                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = name;
                        hidden.value = submitter.value || '1';
                        hidden.setAttribute('data-rg-submitter', '1');
                        form.appendChild(hidden);
                    }
                }

                form.querySelectorAll('input[type="submit"], button[type="submit"]').forEach(function (btn) {
                    btn.disabled = true;
                });
                setTimeout(resetGuard, 90000);
            });

            form.addEventListener('invalid', resetGuard, true);
        },

        resetAllFormGuards: function () {
            document.querySelectorAll('form[data-guarded="1"]').forEach(function (form) {
                delete form.dataset.submitting;
                form.querySelectorAll('input[data-rg-submitter="1"]').forEach(function (el) {
                    el.parentNode.removeChild(el);
                });
                form.querySelectorAll('input[type="submit"], button[type="submit"]').forEach(function (btn) {
                    btn.disabled = false;
                });
            });
        },

        ensureNonce: function (formSelector, fieldName) {
            var form = document.querySelector(formSelector);
            if (!form) {
                return;
            }
            var el = form.querySelector('[name="' + fieldName + '"]');
            if (!el) {
                el = document.createElement('input');
                el.type = 'hidden';
                el.name = fieldName;
                form.appendChild(el);
            }
            el.value = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 14);
        },

        syncClientMirror: function (pairs) {
            (pairs || []).forEach(function (p) {
                var src = document.querySelector(p[0]);
                var dst = document.querySelector(p[1]);
                if (src && dst) {
                    dst.value = src.value;
                }
            });
        },

        /**
         * Signale au serveur les conditions à risque 403 hCDN (burst JS, échec chargement asset).
         */
        cdnWatch: (function () {
            var endpoint = null;
            var scriptWarn = 20;
            var reported = {};

            function initMeta() {
                var m = document.querySelector('meta[name="cdn-watch-endpoint"]');
                endpoint = m ? m.getAttribute('content') : null;
                var w = document.querySelector('meta[name="cdn-watch-script-warn"]');
                if (w && w.getAttribute('content')) {
                    scriptWarn = parseInt(w.getAttribute('content'), 10) || scriptWarn;
                }
            }

            function send(event, detail) {
                if (!endpoint) {
                    return;
                }
                detail = detail || {};
                var key = event + '|' + (detail.url || '') + '|' + (detail.scripts || '');
                if (reported[key]) {
                    return;
                }
                reported[key] = 1;

                var payload = JSON.stringify({
                    event: event,
                    page: location.pathname,
                    url: detail.url || '',
                    status: detail.status || 0,
                    scripts: detail.scripts || 0,
                    role: detail.role || ''
                });

                try {
                    if (navigator.sendBeacon) {
                        navigator.sendBeacon(endpoint, new Blob([payload], { type: 'application/json' }));
                    } else {
                        var x = new XMLHttpRequest();
                        x.open('POST', endpoint);
                        x.setRequestHeader('Content-Type', 'application/json');
                        x._rgSkipGuard = true;
                        x.send(payload);
                    }
                } catch (e) {}
            }

            function countAssetScripts() {
                return document.querySelectorAll('script[src*="/assets/"]').length;
            }

            function onHttpBlock(url, status) {
                if (status === 403) {
                    send('http_403', { url: url, status: status });
                } else if (status === 408) {
                    send('http_408', { url: url, status: status });
                }
            }

            initMeta();

            document.addEventListener('DOMContentLoaded', function () {
                var n = countAssetScripts();
                if (n >= scriptWarn) {
                    send('script_burst', { scripts: n });
                }
            });

            window.addEventListener('error', function (ev) {
                var t = ev.target;
                if (!t || (t.tagName !== 'SCRIPT' && t.tagName !== 'LINK')) {
                    return;
                }
                var url = t.src || t.href;
                if (!url || url.indexOf('/assets/') === -1) {
                    return;
                }
                send('asset_load_fail', { url: url });
            }, true);

            return {
                report: send,
                onHttpBlock: onHttpBlock,
                countAssetScripts: countAssetScripts
            };
        })()
    };

    /**
     * Debounce global des GET verifinfos / verifquart (legacy onkeyup sans AppRequestGuard).
     */
    var xhrGuardState = {};
    var xhrOpen = XMLHttpRequest.prototype.open;
    var xhrSend = XMLHttpRequest.prototype.send;

    function xhrGuardKey(method, url) {
        if (!method || String(method).toUpperCase() !== 'GET' || !url) {
            return null;
        }
        var u = String(url);
        var m;
        if (u.indexOf('/programmes/verifinfos/') !== -1 || u.indexOf('/Programmes/verifinfos/') !== -1) {
            m = u.match(/verifinfos\/([^?#]+)/i);
            if (!m || !m[1] || m[1] === 'undefined') {
                return null;
            }
            return 'vi:' + decodeURIComponent(m[1]);
        }
        if (u.indexOf('/programmes/verifinfosbis/') !== -1 || u.indexOf('/Programmes/verifinfosbis/') !== -1) {
            m = u.match(/verifinfosbis\/([^?#]+)/i);
            if (!m || !m[1] || m[1] === 'undefined') {
                return null;
            }
            return 'vib:' + decodeURIComponent(m[1]);
        }
        if (u.indexOf('/personnels/verifinfos/') !== -1 || u.indexOf('/Personnels/verifinfos/') !== -1) {
            m = u.match(/verifinfos\/([^?#]+)/i);
            if (!m || !m[1] || m[1] === 'undefined') {
                return null;
            }
            return 'pvi:' + decodeURIComponent(m[1]);
        }
        if (u.indexOf('/programmes/verifquart') !== -1 || u.indexOf('/Programmes/verifquart') !== -1) {
            m = u.match(/verifquartr?\/([^?#]+)/i);
            if (!m || !m[1] || m[1] === 'undefined') {
                return null;
            }
            return 'vq:' + decodeURIComponent(m[1]);
        }
        if (u.indexOf('/confirmation/verifquart/') !== -1 || u.indexOf('/Confirmation/verifquart/') !== -1) {
            m = u.match(/verifquart\/([^?#]+)/i);
            if (!m || !m[1] || m[1] === 'undefined') {
                return null;
            }
            return 'cvq:' + decodeURIComponent(m[1]);
        }
        if (u.indexOf('/programmes/deltamponsieg/') !== -1 || u.indexOf('/Programmes/deltamponsieg/') !== -1) {
            m = u.match(/deltamponsieg\/([^/]+)\/([^?#]+)/i);
            if (!m || !m[1] || !m[2] || m[1] === 'undefined' || m[2] === 'undefined') {
                return null;
            }
            return 'ds:' + decodeURIComponent(m[1]) + ':' + decodeURIComponent(m[2]);
        }
        return null;
    }

    XMLHttpRequest.prototype.open = function (method, url) {
        this._rgMethod = method;
        this._rgUrl = url;
        return xhrOpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function (body) {
        if (this._rgSkipGuard) {
            return xhrSend.call(this, body);
        }

        var xhr = this;
        if (!xhr._rgCdnWatchBound) {
            xhr._rgCdnWatchBound = 1;
            xhr.addEventListener('load', function () {
                if (w.AppRequestGuard.cdnWatch && (xhr.status === 403 || xhr.status === 408)) {
                    w.AppRequestGuard.cdnWatch.onHttpBlock(xhr._rgUrl || '', xhr.status);
                }
            });
        }

        var method = this._rgMethod ? String(this._rgMethod).toUpperCase() : 'GET';
        if (method === 'POST') {
            var paramMeta = document.querySelector('meta[name="csrf-param"]');
            var tokenMeta = document.querySelector('meta[name="csrf-token"]');
            if (paramMeta && tokenMeta) {
                var pName = paramMeta.getAttribute('content');
                var pVal = tokenMeta.getAttribute('content');
                if (pName && pVal) {
                    if (typeof body === 'string' && body.indexOf(pName + '=') === -1) {
                        body = (body ? body + '&' : '') + encodeURIComponent(pName) + '=' + encodeURIComponent(pVal);
                    } else if (body && typeof FormData !== 'undefined' && body instanceof FormData && !body.has(pName)) {
                        body.append(pName, pVal);
                    }
                }
            }
        }

        var key = xhrGuardKey(this._rgMethod, this._rgUrl);
        if (!key) {
            return xhrSend.call(this, body);
        }

        var xhr = this;
        var state = xhrGuardState[key] || (xhrGuardState[key] = {});

        if (state.timer) {
            clearTimeout(state.timer);
        }
        if (state.activeXhr && state.activeXhr !== xhr) {
            try {
                state.activeXhr.abort();
            } catch (e) {}
        }

        state.timer = setTimeout(function () {
            state.activeXhr = xhr;
            xhrSend.call(xhr, body);
        }, 400);
    };
})(window);

document.addEventListener('pageshow', function () {
    if (window.AppRequestGuard && typeof window.AppRequestGuard.resetAllFormGuards === 'function') {
        window.AppRequestGuard.resetAllFormGuards();
    }
});
