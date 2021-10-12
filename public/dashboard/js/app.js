var cookie = new cookie()
var $ckEditorObject = {}
var dm

(function ($) {
    $(document).delegate('.uk-menu-hamburger', 'click', function (event) {
        dm = cookie.get('open_dashboard_menu', false)
        $(this).toggleClass('active')
        $('.uk-dashboard').toggleClass('uk-open-menu')
        if (Laravel.device == 'pc') {
            if (dm) {
                cookie.delete('open_dashboard_menu', {path: '/'})
            } else {
                cookie.set('open_dashboard_menu', true, {path: '/'})
            }
        } else {
            cookie.delete('open_dashboard_menu', {path: '/'})
        }
    })

    $('body').delegate('.uk-button-delete-entity', 'click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        var b = $(this);
        var i = b.data('item');
        var fd = $('#form-delete-' + i + '-object');
        var fdd = fd.serialize();
        if (b.hasClass('use-ajax')) {
            $.ajax({
                url: fd.attr('action'),
                method: 'DELETE',
                data: fdd,
                headers: {
                    'X-CSRF-TOKEN': window.Laravel.csrfToken
                },
                success: function (r) {
                    if (r) {
                        for (var t = 0; t < r.length; ++t) {
                            command_action(r[t]);
                        }
                    }
                },
            });
        } else {
            if (fd.length && fd.get(0).tagName == 'FORM') fd.submit();
        }
    })

    $('body').delegate('#form-field-field-type', 'change', function (event) {
        var v = $(this).val()
        if (v == 'file_drop') {
            $('#page-fields-form-file-settings-box').removeClass('uk-hidden')
            $('#page-fields-form-relation-settings-box, #page-fields-form-table-settings-box').addClass('uk-hidden')
        } else if (v == 'relation') {
            $('#page-fields-form-relation-settings-box').removeClass('uk-hidden')
            $('#page-fields-form-file-settings-box, #page-fields-form-table-settings-box').addClass('uk-hidden')
        } else if (v == 'table') {
            $('#page-fields-form-table-settings-box').removeClass('uk-hidden')
            $('#page-fields-form-file-settings-box, #page-fields-form-relation-settings-box').addClass('uk-hidden')
        } else {
            $('#page-fields-form-relation-settings-box, #page-fields-form-file-settings-box, #page-fields-form-table-settings-box').addClass('uk-hidden')
            $('#form-field-field-multiple-0').prop('checked', false)
            $('#form-field-field-allow').val('')
            $('#form-field-field-cols').val(2)
        }
    });

    $('body').delegate(".field-table-remove-row", "click touch", function (event) {
        event.preventDefault()
        event.stopPropagation()
        var t = $(this)
        var tb = t.parents('table')
        t.parents('tr.field-table-row').remove()
        if (!tb.find('tbody tr').length) {
            tb.find('thead').addClass('uk-hidden')
        }
    });

    $('body').delegate(".uk-button-save-sorting", "click touch", function (event) {
        event.preventDefault()
        event.stopPropagation()
        var t = $(this)
        var i = {}
        $('input[name="items_sort[]"]').each(function () {
            i[$(this).data("id")] = $(this).val()
        })
        _ajax_post(t, t.attr("href"), i)
    });

    $('body').delegate(".uk-button-relation-save-sorting", "click touch", function (event) {
        event.preventDefault()
        event.stopPropagation()
        var t = $(this)
        var f = t.data('field')
        var i = {}
        $('input[name="items_sort[' + f + '][]"]').each(function () {
            i[$(this).data("id")] = $(this).val()
        })
        _ajax_post(t, t.attr("href"), i)
    });

    $('body').delegate(".uk-table-row-delete", "click touch", function (event) {
        event.preventDefault()
        event.stopPropagation()
        var t = $(this)
        $.ajax({
            url: t.data('path'),
            method: 'POST',
            data: {_method: 'DELETE'},
            beforeSend: function () {
                window.ajaxLoad = true
                $('body').addClass('ajax-load')
                t.attr('disabled', 'disabled').addClass('load')
                t.append('<div class="uk-ajax-spinner"><div uk-spinner></div></div>')
            },
            success: function (result, status, xhr) {
                window.ajaxLoad = false
                $('body').removeClass('ajax-load ajax-not-visible-load')
                t.removeAttr('disabled').removeClass('load')
                t.find('.uk-ajax-spinner').remove()
            },
            error: function (xhr, status, error) {
                window.ajaxLoad = false
                $('body').removeClass('ajax-load ajax-not-visible-load')
                t.removeAttr('disabled').removeClass('load')
                cmd_UK_notification({
                    text: error,
                    status: 'danger'
                })
                t.find('.uk-ajax-spinner').remove()
            }
        })
    });

    $('body').delegate("input[name=items_all]", "click touch", function (event) {
        var t = $(this)
        if (t.prop('checked')) {
            $('table td input[name*=items]').prop('checked', true)
        } else {
            $('table td input[name*=items]').prop('checked', false)
        }
        console.log(t.prop('checked'));
    });

    $('body').delegate("table td input[name*=items]", "click touch", function (event) {
        var t = $(this)
        if (t.prop('checked')) {
            if ($("table td input[name*=items]:checked").length == $("table td input[name*=items]").length) {
                $('table th input[name=items_all]').prop('checked', true)
            }
        } else {
            $('table th input[name=items_all]').prop('checked', false)
        }
        console.log(t.prop('checked'));
    });

    $('body').delegate('#form-items-action', 'submit', function (event) {
        event.preventDefault()
        event.stopPropagation()
        var f = $(this)
        var d = [];
        var a = $('#items-actions').val()
        if (a == 'no') {
            cmd_UK_notification({
                text: 'Выберите действие с пунктами.',
                status: 'warning'
            })
            return false;
        }
        if ($("table td input[name*=items]:checked").length) {
            $.each($("table td input[name*=items]:checked"), function () {
                d.push($(this).val());
            });
            _ajax_post(f.find('button[type=submit]'), f.attr('action'), {action: a, items: d})
        } else {
            cmd_UK_notification({
                text: 'Выберите пункты для изменения.',
                status: 'warning'
            })
        }
    });

    $('body').delegate('#category-param-form-field-param-id', 'change', function (event) {
        var v = parseInt($(this).val())
        var s = window.shop_param_select
        if (typeof s == 'object' && v && s.includes(v)) {
            $('#category-param-form-select-settings-box').removeClass('uk-hidden')
        } else {
            $('#category-param-form-select-settings-box').addClass('uk-hidden')
        }
    });

    $('body').delegate('#form-field-categories', 'change', function (event) {
        event.preventDefault();
        event.stopPropagation();
        $('#form-field-categories-selection-button').removeAttr('disabled').addClass('uk-animation-shake');
        setTimeout(function () {
            $('#form-field-categories-selection-button').removeClass('uk-animation-shake');
        }, 1500);
    });

    $('body').delegate('#form-field-categories-selection-button', 'click touch', function (event) {
        event.preventDefault();
        event.stopPropagation();
        _ajax_post($(this), $(this).data('path'), {categories: $('#form-field-categories').val()});
    });

    $('body').delegate('#product-modify-form-field-type-1, #product-modify-form-field-type-0', 'click touch', function (event) {
        if ($(this).val() == 'new') {
            $('#product-modify-form-select-exists-box').addClass('uk-hidden')
            $('#product-modify-form-select-new-box').removeClass('uk-hidden')
        } else {
            $('#product-modify-form-select-exists-box').removeClass('uk-hidden')
            $('#product-modify-form-select-new-box').addClass('uk-hidden')
        }
    });

    // $('body').delegate('.button-manager-in-basket', 'click', function (event) {
    //     event.preventDefault();
    //     event.stopPropagation();
    //     let $button = $(this);
    //     let $drug_row = $button.parents('.manager-prices-table-row');
    //     let $drug_count = parseInt($drug_row.find('input[type="number"]').val());
    //     let $ajaxHref = '/oleus/action-drug-on-basket';
    //     let $ajaxData = $button.data();
    //     $ajaxData.count = $drug_count ? $drug_count : 1;
    //     _ajax_post($button, $ajaxHref, $ajaxData);
    // });
    //
    // $('body').delegate('#catalog-search-form', 'submit', function (event) {
    //     $('#load-update-order-lists').addClass('load');
    //     $('#catalog-search-result').css({opacity: .5});
    // });
    //
    // $('body').delegate('.showing-network-availability', 'click', function (event) {
    //     let $id = $(this).data('id');
    //     $('.box-prices-table .prices-table:not(.prices-table-' + $id + ')').attr('hidden', 'hidden');
    // });

    $(document).ajaxComplete(function (event, request, settings) {
        after_load();
    })

    $(document).ready(function () {
        after_load();
    })

    useEasyAutocomplete($);
})(jQuery);

function after_load() {
    useSelect2($);
    usePhoneMask($);
    useDatePicker($);
    useFieldUpload($);
    useCkEditor($);
    useSortable($);
}

function exists(o) {
    return o.length ? true : false
}

function cookie() {
    this.get = function (name, default_value) {
        var m = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return m ? decodeURIComponent(m[1]) : default_value
    };

    this.set = function (name, value, options) {
        var o = options || {}
        var v = encodeURIComponent(value)
        var c = name + "=" + v
        var e = o.expires
        if (typeof e == "number" && e) {
            var d = new Date()
            d.setTime(d.getTime() + e * 1000)
            e = o.expires = d
        }
        if (e && e.toUTCString) {
            o.expires = e.toUTCString();
        }
        for (var pn in o) {
            c += ";" + pn;
            var pv = o[pn];
            if (pv !== true) {
                c += "=" + pv;
            }
        }
        document.cookie = c;
    };

    this.delete = function (name, options) {
        var o = options || {}
        o.expires = -1
        this.set(name, '', o)
    };
}

function getRelationProductPath(d) {
    var s = $('#product-relation-form-field-relation')
    return $('input[name="field[product_relation_path]"]').val().replace(':replace', s.val()).replace(':type', d.type)
}

function useSelect2($) {
    $('select.uk-select').select2({
        width: '100%'
    });
}

function usePhoneMask($) {
    $('input.field-phone-mask, input[type="phone"]').inputmask(window.Laravel.phone_mask);
}

function useDatePicker($) {
    $('input.field-datepicker').datepicker();
}

function useCkEditor($) {
    $('.ckEditor').each(function () {
        var f = null,
            e = {};
        if (f = $(this).attr('id')) {
            if ($(this).hasClass('editor-short')) {
                e = {
                    height: 150,
                    customConfig: '/dashboard/js/ck_config_short.js'
                };
            } else {
                e = {
                    height: 250,
                    customConfig: '/dashboard/js/ck_config_full.js'
                };
            }
            if (!$('#cke_' + f).length) {
                $ckEditorObject[f] = CKEDITOR.replace(f, e);
                $ckEditorObject[f].on('change', function (ck) {
                    $('#' + f).val(ck.editor.getData());
                });
                // CKEDITOR.config.contentsCss = '/dashboard/css/uikit.min.css';
                CKEDITOR.config.startupOutlineBlocks = true;
            }
        }
    });
}

function useEasyAutocomplete($) {
    $('input.uk-autocomplete').each(function () {
        var i = $(this),
            pi = i.parents('.uk-form-controls-autocomplete'),
            iv = pi.find('input[type="hidden"]'),
            d = i.data()
        if (i.data('path')) {
            i.easyAutocomplete({
                // url: i.data('path'),
                url: function (phrase) {
                    if (i.data('callback')) {
                        return window[i.data('callback')](d)
                    } else {
                        return i.data('path')
                    }
                },
                ajaxSettings: {
                    dataType: 'json',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': window.Laravel.csrfToken
                    },
                    data: {
                        dataType: 'json'
                    },
                    beforeSend: function () {
                        pi.append('<div class="uk-ajax-spinner"><div uk-spinner></div></div>')
                    }
                },
                getValue: i.data('value'),
                requestDelay: 500,
                template: {
                    type: 'custom',
                    method: function (value, item) {
                        return item.view !== undefined ? (value + ' - <span>' + item.view + '</span>') : value;
                    }
                },
                list: {
                    onChooseEvent: function () {
                        var item = i.getSelectedItemData();
                        iv.val(item.data).trigger("change");
                    },
                    onLoadEvent: function () {
                        pi.find('.uk-ajax-spinner').remove()
                        var item = i.getItems();
                        iv.val('').trigger("change");
                        if (!item.length) {
                            pi.find('.easy-autocomplete').append('<div class="easy-autocomplete-no-result">No result</div>');
                            setTimeout(function () {
                                pi.find('.easy-autocomplete-no-result').remove();
                            }, 1500);
                        } else {
                            pi.find('.easy-autocomplete-no-result').remove();
                        }
                    },
                    maxNumberOfElements: 10,
                    match: {
                        enabled: true
                    }
                },
                preparePostData: function (data) {
                    d.search = i.val()
                    data = Object.assign(i.data(), d)
                    return data;
                }
            });
        }
    });
}

function useSortable($) {
    var s = $(".sortable-list")
    if (exists(s)) {
        if (s.data('load') == undefined) {
            s.data('load', 1)
            UIkit.sortable(s, {
                group: 'sortable-group',
                handle: '.uk-sortable-handle'
            });
            s.on('stop', function () {
                var si = []
                s.find('.sort-item').each(function () {
                    var i = $(this)
                    si[i.data('id')] = i.index()
                });
                _ajax_post(s, s.data("path"), Object.assign({}, si), true)
            })
        }
    }
}
