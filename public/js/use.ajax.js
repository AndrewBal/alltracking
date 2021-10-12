window.ajaxLoad = false
window.activatedSubmitButton = false
window.reCaptchaValid = null

cmd_addClass = (options) => {
    let o = options || {}
    if (o.target != undefined && o.data != undefined) $(o.target).addClass(o.data)
}

cmd_removeClass = (options) => {
    let o = options || {}
    if (o.target != undefined && o.data != undefined) $(o.target).removeClass(o.data)
}

cmd_toggleClass = (options) => {
    let o = options || {}
    if (o.target != undefined && o.data != undefined) $(o.target).toggleClass(o.data)
}

cmd_css = (options) => {
    let o = options || {}
    if (o.target != undefined && o.data != undefined) $(o.target).css(o.data)
}

cmd_animate = (options) => {
    let o = options || {}
    if (o.target != undefined && o.data != undefined) $(o.target).animate(o.data, (o.options || {}))
}

cmd_val = (options) => {
    let o = options || {}
    let v = o.data != undefined && o.data ? o.data : ''
    if (o.target != undefined) $(o.target).val(v)
}

cmd_data = (options) => {
    let o = options || {}
    let v = o.data != undefined && o.data ? o.data : ''
    if (o.target != undefined && o.attr != undefined) $(o.target).data(o.attr, v)
}

cmd_removeData = (options) => {
    let o = options || {}
    if (o.target != undefined && o.attr != undefined) $(o.target).removeData(o.attr)
}

cmd_attr = (options) => {
    let o = options || {}
    let v = o.data != undefined && o.data ? o.data : ''
    if (o.target != undefined && o.attr != undefined) $(o.target).attr(o.attr, v)
}

cmd_removeAttr = (options) => {
    let o = options || {}
    if (o.target != undefined && o.attr != undefined) $(o.target).removeAttr(o.attr)
}

cmd_html = (options) => {
    let o = options || {}
    let v = o.data != undefined && o.data ? o.data : ''
    if (o.target != undefined) $(o.target).html(v)
}

cmd_text = (options) => {
    let o = options || {}
    let v = o.data != undefined && o.data ? o.data : ''
    if (o.target != undefined) $(o.target).text(v)
}

cmd_replaceWith = (options) => {
    let o = options || {}
    let v = o.data != undefined && o.data ? o.data : ''
    if (o.target != undefined) $(o.target).replaceWith(v)
}

cmd_append = (options) => {
    let o = options || {}
    if (o.target != undefined && o.data != undefined) $(o.target).append(o.data)
}

cmd_prepend = (options) => {
    let o = options || {}
    if (o.target != undefined && o.data != undefined) $(o.target).prepend(o.data)
}

cmd_clearForm = (options) => {
    let o = options || {}
    if (o.target != undefined) {
        $(o.target).find('input[type="text"], input[type="email"], input[type="password"], input[type="file"], textarea').val('')
        $(o.target).find('input[type="checkbox"]').prop('checked', false)
        $(o.target).find('input[type="checkbox"].is-default').prop('checked', true)
        $(o.target).find('input[type="radio"].is-default').prop('checked', true)
        $(o.target).find('select').find('option').prop('selected', false)
    }
}

cmd_slideUp = (options) => {
    let o = options || {}
    if (o.target != undefined) {
        $(o.target).slideUp((o.duration || 500), function () {
            if (o.callback != 'undefined') eval(o.callback)
        })
    }
}

cmd_slideDown = (options) => {
    let o = options || {}
    if (o.target != undefined) {
        $(o.target).slideDown((o.duration || 500), function () {
            if (o.callback != 'undefined') eval(o.callback)
        })
    }
}

cmd_trigger = (options) => {
    let o = options || {}
    if (o.target != undefined && o.callback != undefined) $(o.target).trigger(o.callback)
}

cmd_remove = (options) => {
    let o = options || {}
    if (o.target != undefined) $(o.target).remove()
}

cmd_scrollToTop = (options) => {
    let o = options || {}
    let ot = o.offsetTop || 0
    if (o.target != undefined) {
        let t = o.duration || 500
        $('html, body').animate({
            scrollTop: $(o.target).offset().top - ot
        }, t)
    }
}

cmd_eval = (options) => {
    let o = options || {}
    if (o.data != undefined) eval(o.data)
}

cmd_UK_modal = (options) => {
    let o = options || {}
    o.bgClose = o.bgClose || true
    o.clsPage = 'uk-modal-page view-ajax-modal'
    let _id = o.id || 'ajax-modal'
    let _cl = o.classDialog || ''
    let _clModal = o.classModal || ''
    if (o.content != undefined) {
        let modal = UIkit.modal((`<div class="uk-modal ${_clModal}" id="${_id}"><div class="uk-modal-dialog ${_cl}">${o.content}</div></div>`), o)
        modal.show()
        $('body').delegate(`#${_id}`, 'hidden', function (event) {
            if (event.target === event.currentTarget) modal.$destroy(true)
        })
    }
    if (o.timeout != undefined) {
        setTimeout(function () {
            UIkit.modal(`#${_id}`).hide()
        }, o.timeout)
    }
}

cmd_UK_modalClose = (options) => {
    let o = options || {}
    if (o.target != undefined && $(`body div${o.target}`).length) {
        UIkit.modal(o.target).hide()
    } else if ($(`body div#ajax-modal`).length) {
        UIkit.modal('#ajax-modal').hide()
    }
}

cmd_UK_modalOpen = (options) => {
    let o = options || {}
    if (o.target != undefined && $(`body div${o.target}`).length) {
        UIkit.modal(o.target).show()
    }
}

cmd_UK_notification = (options) => {
    let o = options || {}
    if (o.text != undefined) {
        let option = {}
        option.status = o.status || 'primary'
        option.pos = o.pos || 'top-center'
        option.timeout = o.timeout || 5000
        UIkit.notification(o.text, option)
    }
}

cmd_analyticsGtag = (options) => {
    let o = options || {}
    if (typeof (gtag) === 'function') gtag('event', (o.action || 'SEND'), {
        event_category: (o.category || 'FORM'),
        event_label: (o.event || 'SENDING'),
        value: (o.value || '')
    })
}

cmd_analyticsFbq = (options) => {
    let o = options || {}
    if (typeof (fbq) === 'function') fbq('track', (o.event || 'FORM_SENDING'))
}

cmd_eCommerce = (options) => {
    let o = options || {}
}

cmd_changeUrl = (options) => {
    let o = options || {}
    if (o.url != undefined) history.pushState(null, null, o.url)
}

cmd_changeTitle = (options) => {
    let o = options || {}
    if (o.title != undefined) document.title = o.title
}

cmd_redirect = (options) => {
    let o = options || {}
    if (o.url != undefined) {
        setTimeout(function () {
            window.location.href = o.url
        }, (o.time || 0))
    }
}

cmd_reload = (options) => {
    let o = options || {}
    $('body').addClass('reload-page')
    setTimeout(function () {
        location.reload()
    }, (o.time || 0))
}

cmd_easyAutocomplete = (options) => {
    if (typeof useEasyAutocomplete == "function") useEasyAutocomplete(jQuery)
}

function _ajax_post(obj, ajaxHref, ajaxData, hideLoad) {
    if (ajaxData instanceof FormData && window.reCaptchaKey && window.reCaptchaValid === null) {
        $.getScript('https://www.google.com/recaptcha/api.js?render=' + window.reCaptchaKey, function (data, textStatus, jqxhr) {
            if (typeof grecaptcha == "object") {
                grecaptcha.ready(function () {
                    grecaptcha.execute(window.reCaptchaKey, {action: 'validate_reCaptcha'})
                        .then(function (token) {
                            var data = new FormData()
                            data.append("action", 'validate_reCaptcha')
                            data.append("token", token)
                            fetch('/callback/checked-reCaptcha', {
                                method: 'POST',
                                body: data,
                                headers: {
                                    'X-CSRF-TOKEN': window.Laravel.csrfToken
                                }
                            }).then(function (response, d) {
                                response.json().then(function (data) {
                                    window.reCaptchaValid = data.token
                                    return _ajax_post(obj, ajaxHref, ajaxData, hideLoad)
                                })
                            })
                        })
                })
            } else {
                window.reCaptchaValid = false
                return _ajax_post(obj, ajaxHref, ajaxData, hideLoad)
            }
        })
    } else {
        if (ajaxData instanceof FormData) {
            ajaxData.append('captcha', window.reCaptchaValid)
        } else {
            ajaxData.captcha = window.reCaptchaValid
        }
        let ajaxOptions = {
            url: ajaxHref,
            method: 'POST',
            data: ajaxData,
            beforeSend: function () {
                window.ajaxLoad = true
                $('body').addClass('ajax-load')
                obj.attr('disabled', 'disabled').addClass('load')
                if (hideLoad) {
                    $('body').addClass('ajax-not-visible-load')
                } else {
                    obj.append('<div class="uk-ajax-spinner"><div uk-spinner></div></div>')
                }
            },
            success: function (result, status, xhr) {
                window.ajaxLoad = false
                $('body').removeClass('ajax-load ajax-not-visible-load')
                obj.removeAttr('disabled').removeClass('load')
                if (!hideLoad) obj.find('.uk-ajax-spinner').remove()
            },
            error: function (xhr, status, error) {
                window.ajaxLoad = false
                $('body').removeClass('ajax-load ajax-not-visible-load')
                obj.removeAttr('disabled').removeClass('load')
                cmd_UK_notification({
                    text: error,
                    status: 'danger'
                })
                if (!hideLoad) obj.find('.uk-ajax-spinner').remove()
            }
        }
        if (ajaxData instanceof FormData) {
            ajaxOptions.enctype = 'multipart/form-data'
            ajaxOptions.processData = false
            ajaxOptions.contentType = false
            ajaxOptions.cache = false
        }
        return $.ajax(ajaxOptions)
    }
}

(function ($) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': window.Laravel.csrfToken,
            'LOCALE': window.Laravel.locale,
            'DEVICE': window.Laravel.device
        }
    })
    $(document).ajaxComplete(function (event, xhr, settings) {
        if (xhr.status == 200 && typeof xhr.responseJSON !== 'undefined' && typeof xhr.responseJSON.commands !== 'undefined') {
            if (xhr.responseJSON.commands !== null) {
                for (let $i = 0; $i < xhr.responseJSON.commands.length; ++$i) {
                    let command = xhr.responseJSON.commands[$i]
                    if (window['cmd_' + command.command] != undefined) window['cmd_' + command.command](command.options)
                }
            }
        }
    })
    $('body').delegate('.use-ajax', 'click touch', function (event) {
        let th = $(this)
        let skipAjaxLoad = th.data('skip_load') != undefined ? 1 : 0
        if (window.ajaxLoad === false || skipAjaxLoad) {
            let elTagName = th.get(0).tagName, ajaxHref = '', ajaxData = ''
            if (elTagName == 'A' && !th.hasClass('load')) {
                event.preventDefault()
                event.stopPropagation()
                let attrDisabled = th.attr('disabled')
                if (attrDisabled === undefined) {
                    ajaxHref = th.data('path') != undefined && th.data('path') ? th.data('path') : th.attr('href')
                    ajaxData = th.data()
                }
            } else if (elTagName == 'BUTTON' && th.attr('type') == 'button') {
                event.preventDefault()
                event.stopPropagation()
                let attrDisabled = th.attr('disabled')
                if (attrDisabled === undefined) {
                    ajaxHref = th.data('path')
                    ajaxData = th.data()
                }
            } else if (elTagName == 'INPUT' && (th.attr('type') == 'checkbox' || th.attr('type') == 'radio')) {
                ajaxHref = th.data('path')
                ajaxData = th.data()
                ajaxData.option = th.val()
                if (th.attr('type') == 'checkbox') {
                    ajaxData.state = th.prop('checked')
                }
            }
            setTimeout(function () {
                let hideLoad = th.data('hide_load') != undefined ? true : false
                if (ajaxHref && ajaxData) _ajax_post(th, ajaxHref, ajaxData, hideLoad)
            }, 200)
        }
    })
    $('body').delegate('button[type=submit], input[type=submit]', 'click touch', function (event) {
        window.activatedSubmitButton = $(this)
    })
    $('body').delegate('select.use-ajax', 'change', function (event) {
        event.preventDefault()
        event.stopPropagation()
        let th = $(this)
        let p = th.data('path')
        let o = {}
        th.on('select2:select', function (e) {
            o.option = th.val()
        });
        o.option = th.val()
        setTimeout(function () {
            if (p && o) _ajax_post(th, p, o)
        }, 300)
    })
    $('body').delegate('form.use-ajax', 'submit', function (event) {
        event.preventDefault()
        event.stopPropagation()
        if (window.ajaxLoad === false) {
            let $this = $(this),
                ajaxHref = $this.attr('action'),
                ajaxLoad = window.activatedSubmitButton ? window.activatedSubmitButton : $this,
                hideLoad = ajaxLoad.data('hide_load') != undefined ? true : false
            window.activatedSubmitButton = false
            let ajaxData = new FormData($this[0])
            if (ajaxLoad.is('[name]')) ajaxData.append(ajaxLoad.attr('name'), ajaxLoad.val())
            setTimeout(function () {
                if (ajaxHref && ajaxData) _ajax_post(ajaxLoad, ajaxHref, ajaxData, hideLoad)
            }, 300)
        }
    })
})(jQuery)
