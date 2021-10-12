<?php

namespace App\Http\Controllers\Dashboard\Others;

use App\Libraries\BaseController;
use App\Libraries\Form;
use App\Models\Structure\Page;
use App\Models\Variables;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class SettingsController extends BaseController
{
    protected $formTheme = 'backend.forms.empty';
    protected $parent;
    protected $dashboard;

    public function __construct()
    {
        parent::__construct();
        $this->middleware([
            'permission:settings'
        ]);
        $this->notice = [
            'save_settings' => ''
        ];
        $this->dashboard = new Page();
        $this->dashboard->fill([
            'title'        => 'Панель управления',
            'generate_url' => _r('oleus')
        ]);
        $this->parent = new Page();
        $this->parent->fill([
            'title'        => 'Настройки',
            'generate_url' => NULL
        ]);
    }

    public function view(Request $request, $method)
    {
        if (method_exists($this, $method)) return $this->callAction($method, [$request]);

        return redirect()
            ->back()
            ->with('notices', [
                [
                    'message' => 'Страница не найдена',
                    'status'  => 'warning'
                ]
            ]);
    }

    public function translate(Request $request, $method, $action)
    {
        $commands = [];
        $_languages = config('laravellocalization.supportedLocales');
        $_form = new Form([
            'id'     => 'file-update-data-form',
            'class'  => 'uk-form',
            'title'  => 'Перевод настроек',
            'action' => _r('oleus.settings.option.translate', [
                $method,
                'save'
            ]),
            'tabs'   => TRUE,
            'prefix' => '<div class="uk-modal-body uk-padding-small"><button class="uk-modal-close-outside" type="button" uk-close></button>',
            'suffix' => '</div>',
        ]);
        $_tabs = [];
        switch ($action) {
            case 'edit':
                switch ($method) {
                    case 'overall':
                        foreach ($_languages as $_locale => $_data) {
                            if ($_locale != $this->defaultLocale) {
                                $_tabs[] = [
                                    'title'   => $_data['native'],
                                    'content' => [
                                        render_field("settings.{$_locale}.site_name", [
                                            'label' => 'Название сайта',
                                            'value' => config_data_load('seo', 'settings.*.site_name', $_locale),
                                            'uikit' => TRUE
                                        ]),
                                        render_field("settings.{$_locale}.site_slogan", [
                                            'type'       => 'textarea',
                                            'label'      => 'Слоган сайта',
                                            'value'      => config_data_load('seo', 'settings.*.site_slogan', $_locale),
                                            'attributes' => [
                                                'rows' => 2,
                                            ],
                                            'uikit'      => TRUE
                                        ]),
                                        render_field("settings.{$_locale}.site_copyright", [
                                            'label' => 'Копирайт в подвале',
                                            'value' => config_data_load('seo', 'settings.*.site_copyright', $_locale),
                                            'uikit' => TRUE
                                        ]),
                                    ]
                                ];
                            }
                        }
                        break;
                    case 'seo':
                        foreach ($_languages as $_locale => $_data) {
                            if ($_locale != $this->defaultLocale) {
                                $_tabs[] = [
                                    'title'   => $_data['native'],
                                    'content' => [
                                        render_field("settings.{$_locale}.description", [
                                            'type'       => 'textarea',
                                            'label'      => 'Description (по умолчанию)',
                                            'value'      => config_data_load('seo', 'settings.*.description', $_locale),
                                            'attributes' => [
                                                'rows' => 5
                                            ],
                                            'uikit'      => TRUE
                                        ]),
                                        render_field("settings.{$_locale}.keywords", [
                                            'type'       => 'textarea',
                                            'label'      => 'Keywords (по умолчанию)',
                                            'value'      => config_data_load('seo', 'settings.*.keywords', $_locale),
                                            'attributes' => [
                                                'rows' => 5
                                            ],
                                            'uikit'      => TRUE
                                        ]),
                                        render_field("settings.{$_locale}.suffix_title", [
                                            'label' => 'Окончание в заголовке',
                                            'value' => config_data_load('seo', 'settings.*.suffix_title', $_locale),
                                            'uikit' => TRUE
                                        ]),
                                        render_field("settings.{$_locale}.copyright", [
                                            'label' => 'Копирайт в &lt;head&gt;',
                                            'value' => config_data_load('seo', 'settings.*.copyright', $_locale),
                                            'uikit' => TRUE
                                        ]),
                                    ]
                                ];
                            }
                        }
                        break;
                    case 'contacts':
                        foreach ($_languages as $_locale => $_data) {
                            if ($_locale != $this->defaultLocale) {
                                $_tabs[] = [
                                    'title'   => $_data['native'],
                                    'content' => [
                                        render_field("working_hours.{$_locale}", [
                                            'type'       => 'textarea',
                                            'label'      => 'Время работы',
                                            'value'      => config_data_load('contacts', 'working_hours.*', $_locale),
                                            'attributes' => [
                                                'rows' => 5
                                            ],
                                            'uikit'      => TRUE
                                        ]),
                                        render_field("address.{$_locale}", [
                                            'type'       => 'textarea',
                                            'label'      => 'Юридический адрес',
                                            'value'      => config_data_load('contacts', 'address.*', $_locale),
                                            'attributes' => [
                                                'rows' => 5
                                            ],
                                            'uikit'      => TRUE
                                        ]),
                                        render_field("schema.{$_locale}", [
                                            'type'       => 'textarea',
                                            'label'      => 'Микроразметка',
                                            'value'      => config_data_load('contacts', 'schema.*', $_locale),
                                            'attributes' => [
                                                'rows'  => 30,
                                                'class' => 'uk-codeMirror',
                                            ],
                                            'uikit'      => TRUE,
                                        ]),
                                    ]
                                ];
                            }
                        }
                        break;
                }
                $_form->setAjax();
                $_form->setFields($_tabs);
                $_form->setButtonSubmitText('Сохранить');
                $_form->setButtonSubmitClass('uk-button uk-button-success');
                $commands['commands'][] = [
                    'command' => 'UK_modal',
                    'options' => [
                        'content' => $_form->_render()
                    ]
                ];
                break;
            case 'save':
                $_save = $request->except([
                    'submit_form',
                    'captcha',
                    'form',
                    '_token',
                    '_method',
                ]);
                switch ($method) {
                    case 'overall':
                    case 'seo':
                        config_file_save('seo', Arr::dot($_save));
                        break;
                    case 'contacts':
                        config_file_save('contacts', Arr::dot($_save));
                        break;
                }
                $commands['commands'][] = [
                    'command' => 'UK_notification',
                    'options' => [
                        'text'   => $this->notifications['translated'],
                        'status' => 'success',
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'UK_modalClose',
                    'options' => []
                ];
                break;
        }
        if (!count($commands)) {
            $commands['commands'][] = [
                'command' => 'UK_notification',
                'options' => [
                    'status' => 'danger',
                    'text'   => 'Ошибка! Что-то пошло не так.'
                ]
            ];
        }

        return response($commands, 200);
    }

    public function overall(Request $request)
    {
        $_page = new Page();
        $_page->fill([
            'title' => 'Общее'
        ]);
        $_wrap = $this->render([
            'page.title'  => 'Настройки',
            'seo.title'   => "Настройки. {$_page->title}",
            'breadcrumbs' => render_breadcrumb([
                'parent' => [
                    $this->dashboard,
                    $this->parent
                ],
                'entity' => $_page,
            ]),
        ]);
        $_config = config('seo');
        $_locale = DEFAULT_LOCALE;
        if ($request->method() == 'GET') {
            $_form = $this->__form();
            $_form->route_tag = _r('oleus.settings.option', ['setting' => 'overall']);
            $_form->id = 'settings-overall-form';
            $_form->theme = $this->formTheme;
            $_form->contents = [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Сайт</span></h3>',
                render_field("settings.{$_locale}.site_name", [
                    'label' => 'Название сайта',
                    'value' => $_config['settings'][$_locale]['site_name'],
                    'uikit' => TRUE
                ]),
                render_field("settings.{$_locale}.site_slogan", [
                    'type'       => 'textarea',
                    'label'      => 'Слоган сайта',
                    'value'      => $_config['settings'][$_locale]['site_slogan'],
                    'attributes' => [
                        'rows' => 2,
                    ],
                    'uikit'      => TRUE
                ]),
                render_field("settings.{$_locale}.site_copyright", [
                    'label' => 'Копирайт в подвале',
                    'value' => $_config['settings'][$_locale]['site_copyright'],
                    'help'  => ':year - автоматически подставит текущий год',
                    'uikit' => TRUE
                ]),
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Логотипы</span></h3>',
                '<div class="uk-grid uk-grid-small uk-child-width-1-3"><div>',
                render_field('logotype.top', [
                    'type'   => 'file_drop',
                    'label'  => 'В шапке',
                    'allow'  => 'jpg|jpeg|gif|png|svg',
                    'values' => $_config['logotype']['top'] ? [file_get($_config['logotype']['top'])] : NULL,
                    'uikit'  => TRUE
                ]),
                '</div><div>',
                render_field('logotype.footer', [
                    'type'   => 'file_drop',
                    'label'  => 'В подвале',
                    'allow'  => 'jpg|jpeg|gif|png|svg',
                    'values' => $_config['logotype']['footer'] ? [file_get($_config['logotype']['footer'])] : NULL,
                    'uikit'  => TRUE
                ]),
                '</div><div>',
                render_field('logotype.mobile', [
                    'type'   => 'file_drop',
                    'label'  => 'Для мобильной версии',
                    'allow'  => 'jpg|jpeg|gif|png|svg',
                    'values' => $_config['logotype']['mobile'] ? [file_get($_config['logotype']['mobile'])] : NULL,
                    'uikit'  => TRUE
                ]),
                '</div></div>',
            ];
            if (USE_MULTI_LANGUAGE) {
                $_form->buttons[] = _l('Добавить перевод', 'oleus.settings.option.translate', [
                    'p'          => [
                        'overall',
                        'edit'
                    ],
                    'attributes' => [
                        'class' => 'uk-button uk-button-primary uk-margin-small-right uk-button-small uk-text-uppercase use-ajax'
                    ]
                ]);;
            }
            $_form->buttons[] = '<button type="submit" name="save" value="1" class="uk-button uk-button-success uk-button-small uk-text-uppercase">Сохранить настройку</button>';

            return view($_form->theme, compact('_form', '_wrap'));
        }
        if ($logotype_top = $request->input('logotype.top')) {
            $_logotype_top = array_shift($logotype_top);
            Session::flash('logotype.top', json_encode([file_get($_logotype_top['id'])]));
        }
        if ($logotype_footer = $request->input('logotype.footer')) {
            $_logotype_footer = array_shift($logotype_footer);
            Session::flash('logotype.footer', json_encode([file_get($_logotype_footer['id'])]));
        }
        if ($logotype_mobile = $request->input('logotype.mobile')) {
            $_logotype_mobile = array_shift($logotype_mobile);
            Session::flash('logotype.mobile', json_encode([file_get($_logotype_mobile['id'])]));
        }
        $_config = $request->only([
            'settings',
            'logotype',
        ]);
        if (isset($_logotype_top)) $_config['logotype']['top'] = (int)$_logotype_top['id'];
        if (isset($_logotype_footer)) $_config['logotype']['footer'] = (int)$_logotype_footer['id'];
        if (isset($_logotype_mobile)) $_config['logotype']['mobile'] = (int)$_logotype_mobile['id'];
        $_config['last_modified_timestamp'] = time();
        config_file_save('seo', Arr::dot($_config));
        Session::forget([
            'logotype.top',
            'logotype.footer',
            'logotype.mobile',
        ]);

        return redirect()
            ->route('oleus.settings.option', ['setting' => 'overall'])
            ->with('notices', [
                [
                    'message' => $this->notifications['updated'],
                    'status'  => 'success'
                ]
            ]);
    }

    public function seo(Request $request)
    {
        $_page = new Page();
        $_page->fill([
            'title' => 'SEO'
        ]);
        $_wrap = $this->render([
            'page.title'  => 'Настройки',
            'seo.title'   => "Настройки. {$_page->title}",
            'breadcrumbs' => render_breadcrumb([
                'parent' => [
                    $this->dashboard,
                    $this->parent
                ],
                'entity' => $_page,
            ]),
        ]);
        $_config = config('seo');
        $_locale = DEFAULT_LOCALE;
        if ($request->method() == 'GET') {
            $_form = $this->__form();
            $_form->route_tag = _r('oleus.settings.option', ['setting' => 'seo']);
            $_form->id = 'settings-overall-form';
            $_form->theme = $this->formTheme;
            $_form->contents = [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Настройки</span></h3>',
                '<div class="uk-border-rounded uk-box-shadow-small-inset uk-padding-small uk-background-default uk-text-small"><h4 class="uk-margin-remove-top uk-text-primary uk-text-light uk-margin-small-bottom">Метки подстановки для применения</h4><ul class="uk-list uk-list-small uk-margin-remove"><li><strong>[:title]</strong> - заголовка материала.</li></ul></div>',
                render_field("settings.{$_locale}.description", [
                    'type'       => 'textarea',
                    'label'      => 'Description (по умолчанию)',
                    'value'      => $_config['settings'][$_locale]['description'],
                    'attributes' => [
                        'rows' => 5,
                    ],
                    'uikit'      => TRUE
                ]),
                render_field("settings.{$_locale}.keywords", [
                    'type'       => 'textarea',
                    'label'      => 'Keywords (по умолчанию)',
                    'value'      => $_config['settings'][$_locale]['keywords'],
                    'attributes' => [
                        'rows' => 5,
                    ],
                    'uikit'      => TRUE
                ]),
                render_field('robots', [
                    'type'       => 'select',
                    'label'      => 'Robots',
                    'value'      => $_config['robots'],
                    'values'     => [
                        'index, follow'     => 'index, follow',
                        'noindex, follow'   => 'noindex, follow',
                        'index, nofollow'   => 'index, nofollow',
                        'noindex, nofollow' => 'noindex, nofollow'
                    ],
                    'attributes' => [
                        'data-minimum-results-for-search' => 20
                    ],
                    'uikit'      => TRUE
                ]),
                render_field("settings.{$_locale}.suffix_title", [
                    'label' => 'Окончание в заголовке',
                    'value' => $_config['settings'][$_locale]['suffix_title'],
                    'uikit' => TRUE
                ]),
                render_field("settings.{$_locale}.copyright", [
                    'label' => 'Копирайт в блоке &lt;head&gt;',
                    'value' => $_config['settings'][$_locale]['copyright'],
                    'uikit' => TRUE
                ]),
                render_field("theme_color", [
                    'label' => 'Цвет брузера',
                    'type'  => 'color',
                    'value' => $_config['theme_color'],
                    'uikit' => TRUE
                ]),
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Дополнительно</span></h3>',
                render_field('use.last_modified', [
                    'type'   => 'checkbox',
                    'value'  => $_config['use']['last_modified'] ? 1 : 0,
                    'values' => [
                        1 => 'Включить "Last modified"'
                    ],
                    'uikit'  => TRUE
                ]),
                render_field('use.compress', [
                    'type'   => 'checkbox',
                    'value'  => $_config['use']['compress'] ? 1 : 0,
                    'values' => [
                        1 => 'Очистить и сжать код HTML'
                    ],
                    'uikit'  => TRUE
                ]),
                render_field('use.block_scanning', [
                    'type'     => 'checkbox',
                    'selected' => $_config['use']['block_scanning'],
                    'values'   => [
                        1 => 'Заблокировать сканирование'
                    ],
                    'uikit'    => TRUE
                ]),
                '<h3 class="uk-heading-line uk-text-uppercase"><span>ROBOTS.TXT</span></h3>',
                render_field('robots_txt', [
                    'type'       => 'textarea',
                    'label'      => 'robots.txt',
                    'value'      => robots(),
                    'attributes' => [
                        'rows' => 20,
                    ],
                    'uikit'      => TRUE
                ])
            ];
            if (USE_MULTI_LANGUAGE) {
                $_form->buttons[] = _l('Добавить перевод', 'oleus.settings.option.translate', [
                    'p'          => [
                        'seo',
                        'edit'
                    ],
                    'attributes' => [
                        'class' => 'uk-button uk-button-primary uk-margin-small-right uk-button-small uk-text-uppercase use-ajax'
                    ]
                ]);;
            }
            $_form->buttons[] = '<button type="submit" name="save" value="1" class="uk-button uk-button-success uk-button-small uk-text-uppercase">Сохранить настройку</button>';


            return view($_form->theme, compact('_form', '_wrap'));
        }
        $_config = $request->only([
            'theme_color',
            'use',
            'settings',
            'robots',
        ]);
        $_use = [
            'use' => [
                'last_modified'  => 0,
                'compress'       => 0,
                'block_scanning' => 0,
                'multi_language' => config('seo.use.multi_language'),
            ]
        ];
        $_config = array_merge_recursive_distinct($_use, $_config);
        $_config['last_modified_timestamp'] = time();
        config_file_save('seo', Arr::dot($_config));
        robots(TRUE);

        return redirect()
            ->route('oleus.settings.option', ['setting' => 'seo'])
            ->with('notice', [
                'message' => $this->notifications['updated'],
                'status'  => 'success'
            ]);
    }

    public function services(Request $request)
    {
        $_page = new Page();
        $_page->fill([
            'title' => 'Сервисы'
        ]);
        $_wrap = $this->render([
            'page.title'  => 'Настройки',
            'seo.title'   => "Настройки. {$_page->title}",
            'breadcrumbs' => render_breadcrumb([
                'parent' => [
                    $this->dashboard,
                    $this->parent
                ],
                'entity' => $_page,
            ]),
        ]);
        $_config = config('services');
        if ($request->method() == 'GET') {
            $_form = $this->__form();
            $_form->route_tag = _r('oleus.settings.option', ['setting' => 'services']);
            $_form->id = 'settings-overall-form';
            $_form->theme = $this->formTheme;
            $_form->contents = [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>GOOGLE</span></h3>',
                '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                render_field('google.gtag', [
                    'label' => 'GoogleAnalytics TAG ключ',
                    'value' => $_config['google']['gtag'],
                    'uikit' => TRUE,
                ]),
                '</div><div>',
                render_field('google.gtm', [
                    'label' => 'GoogleAnalytics GTM ключ',
                    'value' => $_config['google']['gtm'],
                    'uikit' => TRUE,
                ]),
                '</div></div>',
                '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                render_field('google.reCaptcha_public', [
                    'label' => 'Google reCaptcha публичный ключ',
                    'value' => $_config['google']['reCaptcha_public'],
                    'uikit' => TRUE,
                ]),
                '</div><div>',
                render_field('google.reCaptcha_secret', [
                    'label' => 'Google reCaptcha секретный ключ',
                    'value' => $_config['google']['reCaptcha_secret'],
                    'uikit' => TRUE,
                ]),
                '</div></div>',
                render_field('google.googleMap', [
                    'label' => 'GoogleMap API ключ',
                    'value' => $_config['google']['googleMap'],
                    'uikit' => TRUE,
                    'attributes' => [
                        'class' => 'uk-width-1-2'
                    ]
                ]),
                '<h3 class="uk-heading-line uk-text-uppercase"><span>FACEBOOK</span></h3>',
                render_field('facebook.pixel', [
                    'label' => 'Pixel ключ',
                    'value' => $_config['facebook']['pixel'],
                    'uikit' => TRUE,
                    'attributes' => [
                        'class' => 'uk-width-1-2'
                    ]
                ]),
            ];
            $_form->buttons = [
                '<button type="submit" name="save" value="1" class="uk-button uk-button-success uk-button-small uk-text-uppercase">Сохранить настройку</button>'
            ];

            return view($_form->theme, compact('_form', '_wrap'));
        }
        $_config = $request->only([
            'facebook',
            'google'
        ]);
        update_last_modified_timestamp();
        config_file_save('services', Arr::dot($_config));

        return redirect()
            ->route('oleus.settings.option', ['setting' => 'services'])
            ->with('notice', [
                'message' => $this->notifications['updated'],
                'status'  => 'success'
            ]);
    }

    public function contacts(Request $request)
    {
        $_page = new Page();
        $_page->fill([
            'title' => 'Контактная информация'
        ]);
        $_wrap = $this->render([
            'page.title'  => 'Настройки',
            'seo.title'   => "Настройки. {$_page->title}",
            'breadcrumbs' => render_breadcrumb([
                'parent' => [
                    $this->dashboard,
                    $this->parent
                ],
                'entity' => $_page,
            ]),
        ]);
        $_config = config('contacts');
        $_locale = DEFAULT_LOCALE;
        if ($request->method() == 'GET') {
            $_form = $this->__form();
            $_form->route_tag = _r('oleus.settings.option', ['setting' => 'contacts']);
            $_form->id = 'settings-overall-form';
            $_form->theme = $this->formTheme;
            $_form->contents = [
                '<div class="uk-grid uk-grid-small"><div class="uk-width-1-3">',
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Номера телефонов</span></h3>',
                render_field('phones.0', [
                    'label'      => 'Телефон 1',
                    'value'      => $_config['phones'][0],
                    'attributes' => [
                        'class' => 'field-phone-mask'
                    ],
                    'uikit'      => TRUE
                ]),
                render_field('phones.1', [
                    'label'      => 'Телефон 2',
                    'value'      => $_config['phones'][1],
                    'attributes' => [
                        'class' => 'field-phone-mask'
                    ],
                    'uikit'      => TRUE
                ]),
                render_field('phones.2', [
                    'label'      => 'Телефон 3',
                    'value'      => $_config['phones'][2],
                    'attributes' => [
                        'class' => 'field-phone-mask'
                    ],
                    'uikit'      => TRUE
                ]),
                render_field('phones.3', [
                    'label'      => 'Телефон 4',
                    'value'      => $_config['phones'][3],
                    'attributes' => [
                        'class' => 'field-phone-mask'
                    ],
                    'uikit'      => TRUE
                ]),
                render_field('phones.4', [
                    'label'      => 'Телефон 5',
                    'value'      => $_config['phones'][4],
                    'attributes' => [
                        'class' => 'field-phone-mask'
                    ],
                    'uikit'      => TRUE
                ]),
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Электронная почта</span></h3>',
                render_field('email', [
                    'label' => 'E-mail',
                    'value' => $_config['email'],
                    'uikit' => TRUE
                ]),
                '</div><div class="uk-width-2-3">',
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Время работы</span></h3>',
                render_field("working_hours.{$_locale}", [
                    'type'       => 'textarea',
                    'label'      => 'Время работы',
                    'value'      => $_config['working_hours'][$_locale],
                    'uikit'      => TRUE,
                    'attributes' => [
                        'rows' => 3
                    ],
                ]),
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Юридический адрес</span></h3>',
                render_field("address.{$_locale}", [
                    'type'       => 'textarea',
                    'label'      => 'Юридический адрес',
                    'value'      => $_config['address'][$_locale],
                    'uikit'      => TRUE,
                    'attributes' => [
                        'rows' => 3
                    ],
                ]),
                '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                render_field('locations.lat', [
                    'label' => 'Широта',
                    'value' => $_config['locations']['lat'],
                    'uikit' => TRUE,
                ]),
                '</div><div>',
                render_field('locations.lng', [
                    'label' => 'Долгота',
                    'value' => $_config['locations']['lng'],
                    'uikit' => TRUE,
                ]),
                '</div></div>',
                '</div></div>',
                '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Социальные сети</span></h3>',
                render_field('socials.facebook', [
                    'label' => 'Facebook',
                    'value' => $_config['socials']['facebook'],
                    'uikit' => TRUE,
                ]),
                render_field('socials.vk', [
                    'label' => 'VK',
                    'value' => $_config['socials']['vk'],
                    'uikit' => TRUE,
                ]),
                render_field('socials.linkedin', [
                    'label' => 'LinkedIn',
                    'value' => $_config['socials']['linkedin'],
                    'uikit' => TRUE,
                ]),
                render_field('socials.skype', [
                    'label' => 'Skype',
                    'value' => $_config['socials']['skype'],
                    'uikit' => TRUE,
                ]),
                render_field('socials.google', [
                    'label' => 'Google',
                    'value' => $_config['socials']['google'],
                    'uikit' => TRUE,
                ]),
                render_field('socials.instagram', [
                    'label' => 'Instagramm',
                    'value' => $_config['socials']['instagram'],
                    'uikit' => TRUE,
                ]),
                render_field('socials.twitter', [
                    'label' => 'Twitter',
                    'value' => $_config['socials']['twitter'],
                    'uikit' => TRUE,
                ]),
                render_field('socials.od', [
                    'label' => 'Одноклассники',
                    'value' => $_config['socials']['od'],
                    'uikit' => TRUE,
                ]),
                render_field('socials.youtube', [
                    'label' => 'YouTube',
                    'value' => $_config['socials']['youtube'],
                    'uikit' => TRUE,
                ]),
                '</div><div>',
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Мессенджеры</span></h3>',
                render_field('messengers.telegram', [
                    'label' => 'Telegram',
                    'value' => $_config['messengers']['telegram'],
                    'uikit' => TRUE,
                ]),
                render_field('messengers.skype', [
                    'label' => 'Skype',
                    'value' => $_config['messengers']['skype'],
                    'uikit' => TRUE,
                ]),
                render_field('messengers.viber', [
                    'label' => 'Viber',
                    'value' => $_config['messengers']['viber'],
                    'uikit' => TRUE,
                ]),
                render_field('messengers.whatsapp', [
                    'label' => 'Whatsapp',
                    'value' => $_config['messengers']['whatsapp'],
                    'uikit' => TRUE,
                ]),
                '</div></div>',
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Микроразметка</span></h3>',
                render_field("schema.{$_locale}", [
                    'type'       => 'textarea',
                    'value'      => $_config['schema'][$_locale],
                    'attributes' => [
                        'rows'  => 30,
                        'class' => 'uk-codeMirror',
                    ],
                    'uikit'      => TRUE,
                ]),
            ];
            if (USE_MULTI_LANGUAGE) {
                $_form->buttons[] = _l('Добавить перевод', 'oleus.settings.option.translate', [
                    'p'          => [
                        'contacts',
                        'edit'
                    ],
                    'attributes' => [
                        'class' => 'uk-button uk-button-primary uk-margin-small-right uk-button-small uk-text-uppercase use-ajax'
                    ]
                ]);;
            }
            $_form->buttons[] = '<button type="submit" name="save" value="1" class="uk-button uk-button-success uk-button-small uk-text-uppercase">Сохранить настройку</button>';

            return view($_form->theme, compact('_form', '_wrap'));
        }
        $_config = $request->only([
            'socials',
            'email',
            'phones',
            'working_hours',
            'messengers',
            'address',
            'locations',
            'schema',
        ]);
        update_last_modified_timestamp();
        config_file_save('contacts', Arr::dot($_config));

        return redirect()
            ->route('oleus.settings.option', ['setting' => 'contacts'])
            ->with('notices', [
                [
                    'message' => $this->notifications['updated'],
                    'status'  => 'success'
                ]
            ]);
    }
}
