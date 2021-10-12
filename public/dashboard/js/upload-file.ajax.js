(function ($) {
    $('body').delegate('.uk-form-controls-file .uk-file-remove-button', 'click', function (event) {
        event.preventDefault();
        var t = $(this),
            p = t.parents('.uk-form-controls-file');
        t.parents('.file-img, .file').remove();
        if (p.hasClass('uk-one-file')) p.removeClass('loaded-file');
    });
})(jQuery);

function useFieldUpload($) {
    $('.js-upload').each(function () {
        var b = $(this);
        var p = $(this).next();
        var f = $(this).find('.file-upload-field');
        if (!f.hasClass('applied')) {
            if (!p.hasClass('uk-progress')) p = undefined
            var fc = f.parents('.uk-form-controls-file');
            var fv = fc.data('view');
            var fp = fv == 'gallery' ? fc.find('.uk-preview > div') : fc.find('.uk-preview');
            var o = {
                url: f.data('url'),
                allow: f.data('allow'),
                multiple: f.data('multiple'),
                type: 'post',
                name: 'file',
                params: {
                    field: f.data('field'),
                    view: f.data('view')
                },
                beforeSend: function (e) {
                    e.headers = {
                        'X-CSRF-TOKEN': window.Laravel.csrfToken,
                        'LOCALE': window.Laravel.locale,
                        'DEVICE': window.Laravel.device
                    };
                    fc.addClass('load');
                },
                loadStart: function (e) {
                    if (p != undefined) {
                        p.removeAttr('hidden');
                        p.attr('max', e.total);
                        p.val(e.loaded);
                    }
                },
                progress: function (e) {
                    if (p != undefined) {
                        p.attr('max', e.total);
                        p.val(e.loaded);
                    }

                },
                loadEnd: function (e) {
                    if (e.currentTarget.status != 200) alert('Error - ' + e.currentTarget.status);
                    fc.removeClass('load');
                    if (p != undefined) {
                        p.attr('max', e.total);
                        p.val(e.loaded);
                    }
                    console.log(e);
                },
                complete: function (e) {
                    fc.removeClass('load');
                    var s = arguments[0].status,
                        r = arguments[0].responseText;
                    if (s == 200) {
                        fp.append($(r));
                        if (fc.hasClass('uk-one-file')) fc.addClass('loaded-file');
                    } else {
                        cmd_UK_notification({
                            text: r,
                            status: 'danger',
                        });
                    }
                },
                completeAll: function (e) {
                    fc.removeClass('load');
                    if (p != undefined) {
                        p.val(e.total);
                        p.attr('hidden', 'hidden');
                    }
                },
                fail: function () {
                    fc.removeClass('load');
                    cmd_UK_notification({
                        text: 'Upload File extension is incorrect.',
                        status: 'danger',
                    });
                }
            };
            f.addClass('applied');
            UIkit.upload(b, o);
        }
    });
}
