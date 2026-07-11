/**
 * Filtre instantané pour les listes utilisateurs (cartes ou lignes de tableau).
 */
(function ($) {
    'use strict';

    function normalize(text) {
        return (text || '').toString().toLowerCase();
    }

    function initCardFilter() {
        $('[data-user-filter-input]').each(function () {
            var $input = $(this);
            var listId = $input.attr('data-user-filter-input');
            var $list = $('#' + listId);
            if (!$list.length) {
                return;
            }

            var $items = $list.find('[data-user-filter-item]');
            var $count = $('#' + listId + '-count');
            var $empty = $('#' + listId + '-empty');
            var label = $input.attr('data-user-filter-label') || 'utilisateur(s)';

            function applyFilter() {
                var query = normalize($.trim($input.val()));
                var visible = 0;

                $items.each(function () {
                    var $item = $(this);
                    var haystack = normalize($item.attr('data-search') || $item.text());
                    var match = !query || haystack.indexOf(query) !== -1;
                    $item.toggle(match);
                    if (match) {
                        visible++;
                    }
                });

                if ($count.length) {
                    if (query) {
                        $count.text(visible + ' / ' + $items.length + ' ' + label);
                    } else {
                        $count.text($items.length + ' ' + label);
                    }
                }

                if ($empty.length) {
                    $empty.toggle(!!query && visible === 0);
                }
            }

            $input.on('input keyup search', applyFilter);
            applyFilter();
        });
    }

    function initTableFilter() {
        $('[data-user-table-filter]').each(function () {
            var $input = $(this);
            var selector = $input.attr('data-user-table-filter');
            var $table = $(selector);
            if (!$table.length) {
                return;
            }

            var $rows = $table.find('tbody tr[data-user-filter-item]');
            var $empty = $($input.attr('data-user-table-empty') || '');
            var label = $input.attr('data-user-filter-label') || 'ligne(s)';
            var $count = $($input.attr('data-user-table-count') || '');

            function applyFilter() {
                var query = normalize($.trim($input.val()));
                var visible = 0;

                $rows.each(function () {
                    var $row = $(this);
                    var haystack = normalize($row.attr('data-search') || $row.text());
                    var match = !query || haystack.indexOf(query) !== -1;
                    $row.toggle(match);
                    if (match) {
                        visible++;
                    }
                });

                if ($count.length) {
                    if (query) {
                        $count.text(visible + ' / ' + $rows.length + ' ' + label);
                    } else {
                        $count.text($rows.length + ' ' + label);
                    }
                }

                if ($empty.length) {
                    $empty.toggle(!!query && visible === 0);
                }
            }

            $input.on('input keyup search', applyFilter);
            applyFilter();
        });
    }

    $(function () {
        initCardFilter();
        initTableFilter();
    });
}(jQuery));
