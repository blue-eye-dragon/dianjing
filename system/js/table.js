/******************************************************************************
Copyright 2012 - 2020 Intel Corporation

For licensing information, see the file 'LICENSE' in the root folder of
                           this software module.
******************************************************************************/


var util_table = (function() {

    var _page = {
        MAX_LINKS: 5,
        count: 0,
        current: 1,
        size: 1
    };

    var _$table, _$search_text, _$search_btn, _$page_control;
    var _input_rows, _filtered_rows = [],
        _filter, _sort_up, _sort_index;
    var _fn_checked, _fn_paging_done, _fn_post_load;

    var current_page = 0;
    var comparators = {};

    var create = function($table) {
        _$table = $table;
    }

    var _thead_checkbox = function(checked) {
        _$table.find('thead input').prop('checked', checked);
    }

    var _on_checked = function() {
        if (_fn_checked) {
            _fn_checked(checked());
        }
    }

    var filter = function(data) {
        return $.map(data, function(item) {
            var found_keyword = true;
            if (_filter) {
                found_keyword = false;
            }
            $.map(item.row, function(cell) {
                if (!found_keyword) {
                    $.map(_filter, function(keyword) {
                        if(cell !== true  && cell){
                            
                            if (cell.search(keyword) > -1) {
                                found_keyword = true;
                            }
                        }
                        
                    })
                }
            });

            if (found_keyword) {
                return item
            }
            return null;
        });
    }

    var create_pages = function(count) {
        var $ul = _$page_control.find('ul.pagination');
        $ul.empty();

        _page.count = Math.ceil(count * 1.0 / _page.size);

        // calculate page begin index
        var page_begin = 1;
        if (_page.current > _page.MAX_LINKS) {
            page_begin = _page.current - _page.MAX_LINKS + 1;
        }
        // calculate page end index
        var page_end = page_begin + _page.MAX_LINKS - 1;
        page_end = Math.min(page_end, _page.count);

        if (_page.current > 1) {
            $ul.append('<li><a href="javascript:util_table.next(-1)"><span>&laquo;</span></a></li>');
        } else {
            $ul.append('<li class="disabled"><a href="javascript:util_table.next(-1)"><span>&laquo;</span></a></li>');
        }
        for (var i = page_begin; i <= page_end; ++i) {
            var $li = $('<li><a href="javascript:util_table.page(' + i + ')">' + i + '</a></li>');
            if (_page.current == i) {
                $li.addClass('active');
            }
            $ul.append($li);
        }
        if (_page.count > _page.current) {
            $ul.append('<li><a href="javascript:util_table.next(1)"><span>&raquo;</span></a></li>');
        } else {
            $ul.append('<li class="disabled"><a href="javascript:util_table.next(1)"><span>&raquo;</span></a></li>');
        }
    }

    var next = function(number) {
        // should clear the checkbox in the header in case
        _thead_checkbox(false);

        _page.current += parseInt(number);

        if (_page.current < 1) {
            _page.current = 1;
        } else if (_page.current > _page.count) {
            _page.current = _page.count;
        } else {
            refresh();
            _on_checked();
        }
    }

    var page = function(page) {
        // should clear the checkbox in the header in case
        _thead_checkbox(false);

        _page.current = parseInt(page);
        refresh();
        _on_checked();
        if (_fn_paging_done) {
            _fn_paging_done(_$table);
        }
        // $("#table_user_list thead tr th input").css("display",'none');
        // $("#table_user_list tbody input").css("display",'none');
    }

    var fill_page = function(index_begin) {
        var rows = [],
            index_end = _filtered_rows.length;

        $.each(_filtered_rows, function(index) {
            if (index >= index_begin && index < index_end && (index - index_begin) < _page.size) {
                rows.push(_filtered_rows[index]);
            }
        });

        _debug_print({
            _sort_index: _sort_index,
            _sort_up: _sort_up
        });
        _debug_print(JSON.stringify(rows));

        _$table.find('tbody').empty();
        if(rows.length>0){
            $.map(rows, function(r) {
                var $tr = $('<tr data-key="' + r.key + '"></tr>').data('key', r.key).data('bag', r);
                if (r.checkable) {
                    $tr.append('<td><input type="checkbox" ></td>');
                } else {
                    $tr.append('<td><input type="checkbox" disabled ></td>');
                }
    
                $.map(r.row, function(cell) {
                    $('<td></td>').text(cell).appendTo($tr);
                });
                $tr.click(_on_checked);
                $tr.attr('title', r.title);
                $tr.appendTo(_$table.find('tbody'));
            });
        }
        else{
            var $tr =  $('<tr style="text-align: center;"><td colspan="6" style="border-bottom:0"><img src="/tc/images/no_data.png" style="width:100px;margin-top:20px"/><p style="margin-top: 20px;margin-right: 23px;"><span style="color:#bfbfbf">' + find_i18n('s_no_data') +'</span></p></td></tr>')
            $tr.appendTo(_$table.find('tbody'));
        }
        
    }

    var refresh = function() {
        _thead_checkbox(false);

        var index_begin = 0;

        // create page controls, including page index links and some labels
        if (_$page_control) {
            create_pages(_filtered_rows.length);
            // update total count of rows
            _$page_control.find('[data-id="total"]').text(_filtered_rows.length);
            // skip rows according to page number
            index_begin = (_page.current - 1) * _page.size;
        } else {
            _page.size = _filtered_rows.length;
        }

        fill_page(index_begin);

        if(_fn_post_load) {
            _fn_post_load();
        }
    }

    var load = function(data, post_load) {
        // search filter
        _input_rows = data;
        _filtered_rows = filter(_input_rows);
        _fn_post_load = post_load;

        __sort_underlying();
        refresh();

        _$table.find('thead input').on('click', function() {
            var checked = $(this).prop('checked');
            _$table.find('input').each(function() {
                if ($(this).prop('disabled')) {
                    $(this).prop('checked', false);
                } else {
                    $(this).prop('checked', checked);
                }
            });
            _on_checked();
        });
    }

    var search_rows = function(keywords) {
        keywords = keywords.replace(/([()[{*+.$^\\|?])/g, '\\$1');
        _filter = keywords.split(' ');
        // reset current page number to display filtered results
        _page.current = 1;

        load(_input_rows);
    }

    var checked = function(items) {
        if (items === undefined) {
            var checked = [];
            _$table.find('tr')
                .filter(':has(:checkbox:checked)')
                .each(function() {
                    var key = $(this).data('bag');
                    if (key)
                        checked.push(key);
                });
            return checked;
        }
        $.map(items, function(item) {
            _$table.find('tr[data-key="' + item.key + '"] input[type="checkbox"]')
                .prop('checked', true);
        });
    }

    var string_comparator = function(a, b) {
        return ('' + a).localeCompare('' + b);
    }

    var __sort_underlying = function() {
        if (_sort_index) {
            var sorter = comparators[_sort_index];
            if (sorter === undefined) {
                sorter = string_comparator;
            }
            // sort index always contains a check box column at the beginning
            var data_index = _sort_index - 1;
            _filtered_rows.sort(function(a, b) {
                return sorter(a.row[data_index], b.row[data_index]);
            });

            if (_sort_up) {
                _filtered_rows = _filtered_rows.reverse();
            }
        }
    };

    var enable_sort = function() {
        _$table.find('thead th:gt(0)').click(function() {
            var _$th_sorted = $(this);
            var $span = $('<span class="glyphicon"></span>');
            if (_$th_sorted.find('span').hasClass('glyphicon-arrow-down')) {
                $span.removeClass('glyphicon-arrow-down');
                $span.addClass('glyphicon-arrow-up');
                _sort_up = true;
            } else {
                $span.addClass('glyphicon-arrow-down');
                _sort_up = false;
            }

            _$table.find('thead span.glyphicon').remove();
            _sort_index = _$th_sorted.index();
            _$th_sorted.append($span);

            __sort_underlying();
            page(1);
        });
    };

    var bind_sort = function(index, comparator) {
        comparators[index] = comparator;
    }

    var integer_comparator = function(a, b) {
        return parseInt(b) - parseInt(a);
    }

    var size_comparator = function(a, b) {
        var calc_size = function(value, unit) {
            var ratio = {
                'B': 1.0,
                'KiB': 1.0 * 1024,
                'MiB': 1.0 * 1024 * 1024,
                'GiB': 1.0 * 1024 * 1024 * 1024,
                'TiB': 1.0 * 1024 * 1024 * 1024 * 1024
            }
            return parseFloat(value) * ratio[unit];
        }

        if (a) {
            if (b) {
                var left = a.split(" "),
                    right = b.split(" ");
                return calc_size(right[0], right[1]) - calc_size(left[0], left[1]);
            }
            return -1;
        }
        return 1;
    }

    var enable_search = function($input, $btn) {
        _$search_text = $input;
        _$search_btn = $btn;

        $btn.click(function() {
            search_rows($input.val());
        });

        $input.keyup(function(e) {
            if (e.keyCode == 13) {
                $btn.trigger("click");
            }
        });
    }

    var bind_checked = function(fn) {
        _fn_checked = fn;
    }

    var enable_pagination = function($page_control, fn_done) {
        _$page_control = $page_control;
        _page.size = parseInt(_$page_control.find('select').val());
        _fn_paging_done = fn_done;

        _$page_control
            .find('select')
            .change(function(e) {
                _page.size = parseInt($(this).val());
                page(1);
            });
        refresh();
    }

    var unittest = function() {
        var data = [{
            key: 6,
            row: ['R6C1', 'R6C2'],
            checkable: true
        }, {
            key: 7,
            row: ['R7C1', 'R7C2'],
            checkable: true
        }, {
            key: 8,
            row: ['R8C1', 'R8C2'],
            checkable: true
        }, {
            key: 9,
            row: ['R9C1', 'R9C2'],
            checkable: true
        }, {
            key: 10,
            row: ['R10C1', 'R10C2'],
            checkable: true
        }, {
            key: 11,
            row: ['R11C1', 'R11C2'],
            checkable: true
        }, {
            key: 12,
            row: ['R12C1', 'R12C2'],
            checkable: true
        }, {
            key: 13,
            row: ['R13C1', 'R13C2'],
            checkable: true
        }, {
            key: 14,
            row: ['R14C1', 'R14C2'],
            checkable: true
        }, {
            key: 15,
            row: ['R15C1', 'R15C2'],
            checkable: true
        }, {
            key: 16,
            row: ['R16C1', 'R16C2'],
            checkable: true
        }, {
            key: 17,
            row: ['R17C1', 'R17C2'],
            checkable: true
        }];

        load(data);

        function assert(condition, message) {
            if (!condition) {
                throw message;
            }
        }

        if (_$page_control) {
            assert(data.length == _input_rows.length, "load fail");
            assert(_filtered_rows.length == data.length, "filter fail");
            var back_size = _page.size;

            _page.size = 3;
            refresh();
            assert(_page.count == 4, "fail, page size 3");
            _page.size = 5;
            refresh();
            assert(_page.count == 3, "fail, page size 5");
            _page.size = 2;
            refresh();
            assert(_page.count == 6, "fail, page size 2");
            _page.size = back_size;
            refresh();

            console.log("PAGE control: " + JSON.stringify(_page));
        }
    }

    // debug and log functions
    var _debug_logger;

    var enable_debug = function(logger) {
        _debug_logger = logger;
    }

    var _debug_print = function(line) {
        if (_debug_logger) {
            _debug_logger(line);
        }
    }

    // debug and log functions end

    return {
        unittest: unittest,
        enable_debug: enable_debug,
        enable_search: enable_search,
        enable_pagination: enable_pagination,
        enable_sort: enable_sort,
        bind_checked: bind_checked,
        bind_sort: bind_sort,
        size_comparator: size_comparator,
        string_comparator: string_comparator,
        integer_comparator: integer_comparator,
        page: page,
        next: next,
        checked: checked,
        refresh: refresh,
        create: create,
        load: load
    };

})();