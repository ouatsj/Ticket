<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style>
@page {
    margin: 0;
}
html, body {
    margin: 0 !important;
    padding: 0 !important;
    background: #fff !important;
    color: #000 !important;
    -webkit-text-size-adjust: 100%;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
@media screen {
    html, body {
        width: 80mm;
        height: auto;
        min-height: 0;
        overflow: hidden;
    }
    #printStatus {
        position: fixed;
        inset: 0;
        z-index: 50;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #fff;
        font-family: Arial, Helvetica, sans-serif;
        color: #222;
        text-align: center;
        padding: 24px;
    }
    #printStatus .msg { font-size: 20px; font-weight: 700; margin: 0 0 8px; }
    #printStatus .sub { font-size: 14px; color: #666; margin: 0; }
    #ticketEpsonLibre, #recuEpsonStack {
        position: absolute;
        left: 0;
        top: 0;
        width: 80mm;
        height: auto;
        z-index: 1;
    }
}
@media print {
    #printStatus { display: none !important; }
    html, body {
        width: 80mm !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    #ticketEpsonLibre, #recuEpsonStack {
        position: static !important;
        width: 80mm !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    .recu-copy.is-print-skip {
        display: none !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
    }
}
#ticketEpsonLibre, .recu-copy {
    box-sizing: border-box;
    width: 80mm;
    max-width: 80mm;
    height: auto;
    margin: 0;
    padding: 0 1mm 1mm;
    background: #fff;
    color: #000;
    text-align: center;
    font-family: Arial, Helvetica, DejaVu Sans, sans-serif;
    overflow: visible;
    display: block;
    line-height: 1.25;
}
.recu-copy.is-print-skip {
    display: none !important;
}
#ticketEpsonLibre .t-logo, .recu-copy .t-logo {
    display: block;
    width: 78mm;
    max-width: 100%;
    height: auto;
    max-height: 22mm;
    margin: 0 0 1mm;
    object-fit: contain;
    object-position: center top;
}
#ticketEpsonLibre .t-company, .recu-copy .t-company {
    font-size: 11pt;
    font-weight: 700;
    margin: 0 0 0.6mm;
}
#ticketEpsonLibre .t-title, .recu-copy .t-title {
    font-size: 10pt;
    font-weight: 700;
    margin: 0 0 0.6mm;
}
#ticketEpsonLibre .t-od, .recu-copy .t-od {
    font-size: 14pt;
    font-weight: 700;
    margin: 0 0 0.6mm;
}
#ticketEpsonLibre .t-passager, #ticketEpsonLibre .t-tel,
.recu-copy .t-line, .recu-copy .t-dep {
    font-size: 12pt;
    font-weight: 700;
    margin: 0 0 0.4mm;
    white-space: normal;
}
#ticketEpsonLibre .t-prix, .recu-copy .t-prix {
    font-size: 18pt;
    font-weight: 700;
    margin: 0.8mm 0;
}
#ticketEpsonLibre .t-code, .recu-copy .t-code {
    font-size: 12pt;
    font-weight: 700;
    margin: 0 0 0.4mm;
}
#ticketEpsonLibre .t-emis, .recu-copy .t-emis {
    font-size: 10pt;
    margin: 0.6mm 0 0;
}
#ticketEpsonLibre img.ticket-barcode, .recu-copy img.ticket-barcode {
    display: block !important;
    width: 78mm !important;
    max-width: 100% !important;
    height: 16mm !important;
    margin: 1mm 0 0 !important;
    object-fit: fill !important;
}
</style>
<script>
function posEscaleFitPage(el) {
    if (!el) return;
    var status = document.getElementById('printStatus');
    if (status) status.style.display = 'none';
    var stack = document.getElementById('recuEpsonStack');
    if (stack) {
        stack.style.position = 'static';
        stack.style.width = '80mm';
        stack.style.height = 'auto';
        stack.style.margin = '0';
    }
    el.style.position = 'static';
    el.style.width = '80mm';
    el.style.margin = '0';
    document.documentElement.style.height = 'auto';
    document.documentElement.style.minHeight = '0';
    document.documentElement.style.maxHeight = 'none';
    document.body.style.width = '80mm';
    document.body.style.height = 'auto';
    document.body.style.minHeight = '0';
    document.body.style.maxHeight = 'none';
    document.body.style.margin = '0';
    document.body.style.overflow = 'hidden';
    var px = Math.ceil(el.getBoundingClientRect().height || el.offsetHeight || 0);
    if (px < 40) px = 40;
    var pageH = px + 12;
    var tag = document.getElementById('posPageSize');
    if (!tag) {
        tag = document.createElement('style');
        tag.id = 'posPageSize';
        document.head.appendChild(tag);
    }
    tag.textContent = '@page{margin:0;size:80mm ' + pageH + 'px}'
        + '@media print{'
        + 'html,body{margin:0 !important;padding:0 !important;width:80mm !important;height:auto !important;min-height:0 !important;max-height:none !important;overflow:hidden !important}'
        + '#recuEpsonStack,#ticketEpsonLibre{height:auto !important;max-height:none !important;overflow:hidden !important}'
        + '#printStatus,.recu-copy.is-print-skip{display:none !important;height:0 !important;margin:0 !important;padding:0 !important;overflow:hidden !important}'
        + '}';
}
function posEscaleWhenImages(root, cb) {
    var imgs = (root || document).querySelectorAll('img');
    if (!imgs.length) {
        setTimeout(cb, 150);
        return;
    }
    var left = imgs.length;
    var done = false;
    function one() {
        left--;
        if (left <= 0 && !done) {
            done = true;
            setTimeout(cb, 200);
        }
    }
    for (var i = 0; i < imgs.length; i++) {
        if (imgs[i].complete) one();
        else {
            imgs[i].addEventListener('load', one);
            imgs[i].addEventListener('error', one);
        }
    }
    setTimeout(function () {
        if (!done) {
            done = true;
            cb();
        }
    }, 2500);
}
function posEscalePrintCopies(homeUrl) {
    var copies = [];
    var idx = 0;
    var gone = false;
    var waiting = false;
    var timer = null;
    var printedAt = 0;

    function goHome() {
        if (gone) return;
        gone = true;
        window.location.replace(homeUrl);
    }
    function showOnly(i) {
        for (var n = 0; n < copies.length; n++) {
            if (n === i) copies[n].classList.remove('is-print-skip');
            else copies[n].classList.add('is-print-skip');
        }
    }
    function afterOne() {
        if (!waiting) return;
        waiting = false;
        if (timer) {
            clearTimeout(timer);
            timer = null;
        }
        idx++;
        if (idx >= copies.length) {
            setTimeout(goHome, 1800);
            return;
        }
        setTimeout(printNext, 400);
    }
    function printNext() {
        if (gone || idx >= copies.length) return;
        showOnly(idx);
        posEscaleFitPage(copies[idx]);
        waiting = true;
        setTimeout(function () {
            printedAt = Date.now();
            try {
                window.print();
            } catch (e) {
                return;
            }
            timer = setTimeout(function () {
                if (waiting) afterOne();
            }, 10000);
        }, 80);
    }
    if ('onafterprint' in window) {
        window.onafterprint = function () {
            if (!waiting) return;
            if (Date.now() - printedAt < 1200) return;
            afterOne();
        };
    }
    window.onload = function () {
        var stack = document.getElementById('recuEpsonStack');
        copies = stack ? Array.prototype.slice.call(stack.querySelectorAll('.recu-copy')) : [];
        if (!copies.length) {
            return;
        }
        posEscaleWhenImages(stack, printNext);
    };
}
</script>
