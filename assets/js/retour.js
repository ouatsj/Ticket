(function () {
    function sessionRetourUrl() {
        var body = document.body;

        if (!body) {
            return '';
        }

        return body.getAttribute('data-app-retour-url') || '';
    }

    function isRetourLink(anchor) {
        if (!anchor || anchor.tagName !== 'A') {
            return false;
        }

        var href = anchor.getAttribute('href');

        if (!href || href === '#' || href.indexOf('javascript:') === 0) {
            return false;
        }

        if (!anchor.querySelector('.fa-arrow-circle-left')) {
            return false;
        }

        var text = (anchor.textContent || '').replace(/\s+/g, ' ').trim();

        if (/RETOUR/i.test(text)) {
            return true;
        }

        return text.length <= 2;
    }

    function enhanceRetourLinks(root) {
        var scope = root || document;
        var links = scope.querySelectorAll('a[href]');

        links.forEach(function (anchor) {
            if (anchor.classList.contains('btn-retour-smart')) {
                return;
            }

            if (!isRetourLink(anchor)) {
                return;
            }

            anchor.classList.add('btn-retour-smart');

            if (!anchor.getAttribute('data-retour-fallback')) {
                anchor.setAttribute('data-retour-fallback', anchor.getAttribute('href'));
            }
        });
    }

    function navigateRetour(btn) {
        var href = btn.getAttribute('href');
        var fallback = btn.getAttribute('data-retour-fallback') || href;
        var sessionUrl = sessionRetourUrl();

        if (window.history.length > 1 && document.referrer) {
            try {
                var ref = new URL(document.referrer, window.location.origin);

                if (ref.origin === window.location.origin) {
                    window.history.back();
                    return;
                }
            } catch (err) {
                /* ignore */
            }
        }

        if (sessionUrl && sessionUrl !== window.location.href) {
            window.location.href = sessionUrl;
            return;
        }

        window.location.href = href || fallback;
    }

    document.addEventListener('DOMContentLoaded', function () {
        enhanceRetourLinks(document);
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-retour-smart');

        if (!btn) {
            return;
        }

        e.preventDefault();
        navigateRetour(btn);
    });
})();
