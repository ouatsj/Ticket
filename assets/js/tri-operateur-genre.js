/**
 * Cascade TYPE DOCUMENT → GENRE pour les tris opérateur
 * (form-fortridepense / form-tricette).
 */
document.addEventListener('DOMContentLoaded', function () {
    var root = (typeof APP_ROOT !== 'undefined' && APP_ROOT) ? APP_ROOT : '';

    function parseList(raw) {
        var data = [];
        try {
            var text = (raw || '').trim();
            if (text.charAt(0) === '[' || text.charAt(0) === '{') {
                data = JSON.parse(text);
            }
        } catch (e) {
            return [];
        }
        if (Array.isArray(data)) {
            return data;
        }
        if (data && typeof data === 'object') {
            return Object.keys(data).map(function (k) { return data[k]; });
        }
        return [];
    }

    function resetGenre(select) {
        if (!select) {
            return;
        }
        select.options.length = 0;
        var keep = document.createElement('option');
        keep.value = '';
        keep.textContent = 'Tous les genres';
        select.appendChild(keep);
    }

    function fillGenre(select, rows, valueKey) {
        resetGenre(select);
        (rows || []).forEach(function (row) {
            if (!row) {
                return;
            }
            var val = row[valueKey];
            if (val == null || String(val).trim() === '') {
                return;
            }
            var opt = document.createElement('option');
            opt.value = String(val).trim();
            opt.textContent = String(val).trim();
            select.appendChild(opt);
        });
    }

    function wire(typeId, genreId, endpoint, valueKey) {
        var typeEl = document.getElementById(typeId);
        var genreEl = document.getElementById(genreId);
        if (!typeEl || !genreEl) {
            return;
        }
        typeEl.addEventListener('change', function () {
            resetGenre(genreEl);
            var typ = String(typeEl.value || '').trim();
            if (!typ) {
                return;
            }
            var http = window.XMLHttpRequest
                ? new XMLHttpRequest()
                : new ActiveXObject('Microsoft.XMLHTTP');
            http.open(
                'GET',
                window.location.origin + root + endpoint + encodeURIComponent(typ),
                true
            );
            http.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            http.setRequestHeader('Accept', 'application/json');
            http.onload = function () {
                fillGenre(genreEl, parseList(http.responseText), valueKey);
            };
            http.onerror = function () {
                resetGenre(genreEl);
            };
            http.send();
        });
    }

    wire('typedepense-op', 'genredepense-op', '/depenses/listegenre/', 'genre_depens');
    wire('typerecette-op', 'genrerecette-op', '/recettes/listegenre/', 'type_personnel');
});
