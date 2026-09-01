document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('form-recapglcr-0');
    if (!modal) {
        return;
    }
    var gare = modal.querySelector('select[name="departgarcrgl"]');
    var sous = modal.querySelector('select[name="sousgarecrgl"]');
    if (!gare || !sous) {
        return;
    }

    function resetSous() {
        sous.options.length = 1;
        sous.selectedIndex = 0;
    }

    gare.addEventListener('change', function () {
        resetSous();
        var opt = gare.options[gare.selectedIndex];
        var gid = opt ? opt.getAttribute('data-garesid') : '';
        if (!gid) {
            return;
        }
        var root = (typeof APP_ROOT !== 'undefined') ? APP_ROOT : '';
        var xhr = new XMLHttpRequest();
        xhr.open('GET', window.location.origin + root + '/programmes/verifsousgares/' + encodeURIComponent(gid), true);
        xhr.onload = function () {
            var data;
            try {
                data = JSON.parse(xhr.responseText);
            } catch (e) {
                return;
            }
            if (!data) {
                return;
            }
            var list = Array.isArray(data) ? data : Object.keys(data).map(function (k) { return data[k]; });
            for (var i = 0; i < list.length; i++) {
                if (!list[i] || list[i].idsousgare == null) {
                    continue;
                }
                var o = document.createElement('option');
                o.value = list[i].idsousgare;
                o.textContent = list[i].nomsousgare;
                sous.appendChild(o);
            }
        };
        xhr.send();
    });
});
