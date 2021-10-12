<?php

namespace App\Libraries;

use App\Events\EntityDelete;
use App\Events\EntitySave;
use App\Models\Structure\Node;
use App\Models\Structure\Page;
use App\Models\User\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

trait Dashboard
{
    protected $notifications = [
        'created'    => 'Элемент создан.',
        'updated'    => 'Элемент обновлен.',
        'deleted'    => 'Элемент удален.',
        'translated' => 'Элемент переведен.',
    ];
    protected $titles = [
        'index'     => 'Список',
        'view'      => 'Просмотр',
        'create'    => 'Создать',
        'edit'      => 'Редактировать',
        'delete'    => 'Удалить',
        'translate' => 'Перевод',
    ];
    public $baseRoute;
    public $filter;
    public $filterClear;
    public $permissions = [];
    public $entity = NULL;

    public function render($data = [])
    {
        $_page_class = [
            'uk-dashboard',
            'uk-position-relative',
        ];
        App::setLocale(DEFAULT_LOCALE);
        $_wrap = app('wrap');
        $_open_menu = isset($_COOKIE['open_dashboard_menu']) ? TRUE : FALSE;
        if ($_open_menu && wrap()->get('device.type') == 'pc') $_page_class[] = 'uk-open-menu';
        $data = array_merge($data, [
            'page.style_class' => $_page_class,
            'page.scripts'     => config('dashboard.scripts'),
            'page.styles'      => config('dashboard.styles'),
        ]);
        if ($data) {
            foreach ($data as $_key => $_value) {
                $_wrap->set($_key, $_value);
            }
        }

        return $_wrap->render();
    }

    public function __form()
    {
        return (object)[
            'title'      => NULL,
            'route'      => NULL,
            'route_tag'  => NULL,
            'method'     => 'POST',
            'theme'      => 'backend.forms.edit',
            'id'         => NULL,
            'class'      => 'uk-form-stacked',
            'relation'   => FALSE,
            'rollback'   => FALSE,
            'buttons'    => [],
            'permission' => [
                'read'      => NULL,
                'view'      => FALSE,
                'create'    => NULL,
                'update'    => NULL,
                'delete'    => FALSE,
                'translate' => FALSE,
            ],
            'tabs'       => [
            ],
            'contents'   => [
            ]
        ];
    }

    public function __view()
    {
        return (object)[
            'route_tag' => NULL,
            'theme'     => 'backend.partials.view',
            'contents'  => [
            ]
        ];
    }

    public function __form_tab_display_style($entity, ...$add)
    {
        $_fields[] = render_field('style_id', [
            'label'  => 'ID элемента на странице',
            'value'  => $entity->style_id,
            'prefix' => '<div class="uk-form-row"><div uk-grid class="uk-child-width-1-2"><div>',
            'suffix' => '</div>',
            'uikit'  => TRUE
        ]);
        $_fields[] = render_field('style_class', [
            'label'  => 'CLASS элемента на странице',
            'value'  => $entity->style_class,
            'prefix' => '<div>',
            'suffix' => '</div></div>',
            'uikit'  => TRUE
        ]);
        if (is_array($add) && in_array('background', $add)) {
            $_fields[] = render_field('background_fid', [
                'type'   => 'file_drop',
                'label'  => 'Фоновое изображение',
                'allow'  => 'jpg|jpeg|gif|png|svg',
                'values' => $entity->background_fid ? [$entity->_background] : NULL,
                'uikit'  => TRUE
            ]);
        }
        if (is_array($add) && in_array('prefix', $add)) {
            $_fields[] = render_field('prefix', [
                'type'       => 'textarea',
                'label'      => 'Prefix HTML',
                'value'      => $entity->prefix,
                'attributes' => [
                    'rows'  => 8,
                    'class' => 'uk-codeMirror',
                ],
                'uikit'      => TRUE
            ]);
        }
        if (is_array($add) && in_array('suffix', $add)) {
            $_fields[] = render_field('suffix', [
                'type'  => 'textarea',
                'label' => 'Suffix HTML',

                'value'      => $entity->suffix,
                'attributes' => [
                    'rows'  => 8,
                    'class' => 'uk-codeMirror',
                ],
                'uikit'      => TRUE
            ]);
        }

        return [
            'title'   => 'Стиль оформления',
            'content' => $_fields,
        ];
    }

    public function __form_tab_media_files($entity)
    {
        return [
            'title'   => 'Медиа файлы',
            'content' => [
                render_field('medias', [
                    'type'     => 'file_drop',
                    'label'    => 'Вложенные изображения',
                    'view'     => 'gallery',
                    'multiple' => TRUE,
                    'values'   => $entity->exists && ($_medias = $entity->_files_related()->wherePivot('type', 'medias')->orderBy('sort')->get()) ? $_medias : NULL,
                    'uikit'    => TRUE
                ]),
                render_field('files', [
                    'type'     => 'file_drop',
                    'label'    => 'Вложенные файлы',
                    'multiple' => TRUE,
                    'allow'    => 'txt|doc|docx|xls|xlsx|pdf',
                    'values'   => $entity->exists && ($_files = $entity->_files_related()->wherePivot('type', 'files')->orderBy('sort')->get()) ? $_files : NULL,
                    'uikit'    => TRUE
                ])
            ]
        ];
    }

    public function __form_tab_seo($entity)
    {
        $_fields = [];
        if (($entity->exists && $entity->_alias->id) || !$entity->exists) {
            $_fields[] = render_field('url.alias', [
                'label' => 'URL',
                'value' => $entity->exists ? $entity->_alias->alias : request()->get('alias'),
                'help'  => 'Если оставить пустым, то URL будет сгенерирован из заголовка',
                'uikit' => TRUE
            ]);
            if ($entity->exists) {
                $_fields[] = render_field('url.re_render', [
                    'type'   => 'checkbox',
                    'value'  => NULL,
                    'values' => [
                        1 => 'Сгенерировать заново URL при сохранении'
                    ],
                    'uikit'  => TRUE
                ]);
            }
        }
        $_fields[] = render_field('meta_title', [
            'label' => 'Title',
            'value' => $entity->meta_title,
            'uikit' => TRUE
        ]);
        $_fields[] = render_field('meta_description', [
            'type'       => 'textarea',
            'label'      => 'Description',
            'value'      => $entity->meta_description,
            'attributes' => [
                'rows' => 5,
            ],
            'uikit'      => TRUE
        ]);
        $_fields[] = render_field('meta_keywords', [
            'type'       => 'textarea',
            'label'      => 'Keywords',
            'value'      => $entity->meta_keywords,
            'attributes' => [
                'rows' => 5,
            ],
            'uikit'      => TRUE
        ]);
        if (($entity->exists && $entity->_alias->id) || !$entity->exists) {
            $_fields[] = render_field('url.robots', [
                'type'   => 'select',
                'label'  => 'Robots',
                'value'  => $entity->exists ? $entity->_alias->robots : 'index, follow',
                'values' => [
                    'index, follow'     => 'index, follow',
                    'noindex, follow'   => 'noindex, follow',
                    'index, nofollow'   => 'index, nofollow',
                    'noindex, nofollow' => 'noindex, nofollow'
                ],
                'uikit'  => TRUE
            ]);
        }
        if (($entity->exists && $entity->_alias->id) || !$entity->exists) {
            $_fields[] = '<h3 class="uk-heading-line uk-text-uppercase"><span>XML карта сайта</span></h3>';
            $_fields[] = render_field('url.sitemap', [
                'type'   => 'checkbox',
                'name'   => 'sitemap',
                'value'  => $entity->exists ? $entity->_alias->sitemap : 1,
                'values' => [
                    1 => 'Опубликовать в карте сайта'
                ],
                'uikit'  => TRUE
            ]);
            $_fields[] = render_field('url.changefreq', [
                'type'   => 'select',
                'label'  => 'Частота изменения',
                'value'  => $entity->exists ? $entity->_alias->changefreq : 'monthly',
                'values' => [
                    'always'  => 'always',
                    'hourly'  => 'hourly',
                    'daily'   => 'daily',
                    'weekly'  => 'weekly',
                    'monthly' => 'monthly',
                    'yearly'  => 'yearly',
                    'never'   => 'never',
                ],
                'class'  => 'uk-select2',
                'prefix' => '<div class="uk-form-row"><div class="uk-grid uk-child-width-1-2 uk-grid-small"><div>',
                'suffix' => '</div>',
                'uikit'  => TRUE
            ]);
            $i = 0;
            $_values = [];
            while ($i <= 1) {
                $_values[(string)$i] = $i;
                $i = $i + 0.1;
            }
            $_fields[] = render_field('url.priority', [
                'type'   => 'select',
                'label'  => 'Приоритет',
                'value'  => $entity->exists ? $entity->_alias->priority : '0.5',
                'values' => $_values,
                'prefix' => '<div>',
                'suffix' => '</div></div>',
                'uikit'  => TRUE
            ]);
        }

        return [
            'title'   => 'SEO',
            'content' => $_fields
        ];
    }

    public function __form_tab_seo_for_translation($entity)
    {
        $_fields = [];
        if ($entity->_base_alias->exists) {
            $_fields[] = render_field('url.alias', [
                'label' => 'URL',
                'value' => $entity->_alias->alias ?? NULL,
                'uikit' => TRUE
            ]);
        }
        $_fields[] = render_field('meta_title', [
            'label' => 'Title',
            'value' => $entity->getTranslation('meta_title', $entity->frontLocale, FALSE),
            'uikit' => TRUE
        ]);
        $_fields[] = render_field('meta_description', [
            'type'       => 'textarea',
            'label'      => 'Description',
            'value'      => $entity->getTranslation('meta_description', $entity->frontLocale, FALSE),
            'attributes' => [
                'rows' => 5,
            ],
            'uikit'      => TRUE
        ]);
        $_fields[] = render_field('meta_keywords', [
            'type'       => 'textarea',
            'label'      => 'Keywords',
            'value'      => $entity->getTranslation('meta_keywords', $entity->frontLocale, FALSE),
            'attributes' => [
                'rows' => 5,
            ],
            'uikit'      => TRUE
        ]);
        if ($entity->_base_alias->exists) {
            $_fields[] = render_field('url.robots', [
                'type'   => 'select',
                'label'  => 'Robots',
                'value'  => $entity->_alias->robots ?? $entity->_base_alias->robots,
                'values' => [
                    'index, follow'     => 'index, follow',
                    'noindex, follow'   => 'noindex, follow',
                    'index, nofollow'   => 'index, nofollow',
                    'noindex, nofollow' => 'noindex, nofollow'
                ],
                'uikit'  => TRUE
            ]);
            $_fields[] = '<h3 class="uk-heading-line uk-text-uppercase"><span>XML карта сайта</span></h3>';
            $_fields[] = render_field('url.sitemap', [
                'type'   => 'checkbox',
                'name'   => 'sitemap',
                'value'  => $entity->_alias->sitemap ?? $entity->_base_alias->sitemap,
                'values' => [
                    1 => 'Опубликовать в карте сайта'
                ],
                'uikit'  => TRUE
            ]);
            $_fields[] = render_field('url.changefreq', [
                'type'   => 'select',
                'label'  => 'Частота изменения',
                'value'  => $entity->_alias->changefreq ?? $entity->_base_alias->changefreq,
                'values' => [
                    'always'  => 'always',
                    'hourly'  => 'hourly',
                    'daily'   => 'daily',
                    'weekly'  => 'weekly',
                    'monthly' => 'monthly',
                    'yearly'  => 'yearly',
                    'never'   => 'never',
                ],
                'class'  => 'uk-select2',
                'prefix' => '<div class="uk-form-row"><div class="uk-grid uk-child-width-1-2 uk-grid-small"><div>',
                'suffix' => '</div>',
                'uikit'  => TRUE
            ]);
            $i = 0;
            $_values = [];
            while ($i <= 1) {
                $_values[(string)$i] = $i;
                $i = $i + 0.1;
            }
            $_fields[] = render_field('url.priority', [
                'type'   => 'select',
                'label'  => 'Приоритет',
                'value'  => $entity->_alias->priority ?? $entity->_base_alias->priority,
                'values' => $_values,
                'prefix' => '<div>',
                'suffix' => '</div></div>',
                'uikit'  => TRUE
            ]);
        }

        return [
            'title'   => 'SEO',
            'content' => $_fields
        ];
    }

    //    public function __form_tab_display_rules($entity, ...$exclude)
    //    {
    //        if (!($entity instanceof Node)) return NULL;
    //        if ($_display_rules = $entity->_display_rules) $_display_rules = $_display_rules->groupBy('rule');
    //        //        if (config('os_seo.use.multi_language')) {
    //        //            if (!$exclude || ($exclude && !in_array('languages', $exclude))) {
    //        //                $_languages = config('laravellocalization.supportedLocales');
    //        //                if (count($_languages) > 1) {
    //        //                    $_selected = ['all'];
    //        //                    if ($_display_rules->has('languages')) {
    //        //                        $_selected = $_display_rules->get('languages')->map(function ($_item) {
    //        //                            return $_item->value;
    //        //                        })->toArray();
    //        //                    }
    //        //                    $_languages_select = [
    //        //                        'all' => 'Все'
    //        //                    ];
    //        //                    foreach ($_languages as $_code => $_data) $_languages_select[$_code] = $_data['native'];
    //        //                    $_tab[] = field_render('display_rules.languages', [
    //        //                        'type'     => 'checkbox',
    //        //                        'label'    => 'Языки интерфейса',
    //        //                        'class'    => 'uk-checkboxes-used-all',
    //        //                        'values'   => $_languages_select,
    //        //                        'selected' => $_selected
    //        //                    ]);
    //        //                }
    //        //            }
    //        //        }
    //        if (!$exclude || ($exclude && !in_array('user_roles', $exclude))) {
    //            $_roles = Role::all();
    //            $_selected = ['all'];
    //            if ($_display_rules->has('user_roles')) {
    //                $_selected = $_display_rules->get('user_roles')->map(function ($_item) {
    //                    return $_item->value;
    //                })->toArray();
    //            }
    //            $_roles_select = [
    //                'all'  => 'Все',
    //                'anon' => 'Анонимный пользователь'
    //            ];
    //            foreach ($_roles as $_role) if ($_role->name != 'super_admin') $_roles_select[$_role->name] = $_role->display_name;
    //            $_tab[] = field_render('display_rules.user_roles', [
    //                'type'     => 'checkbox',
    //                'label'    => 'Роли пользователей',
    //                'values'   => $_roles_select,
    //                'class'    => 'uk-checkboxes-used-all',
    //                'selected' => $_selected
    //            ]);
    //        }
    //        //        if (!$exclude || ($exclude && !in_array('pages', $exclude))) {
    //        //            $_values = NULL;
    //        //            if ($_display_rules->has('pages')) {
    //        //                $_values = $_display_rules->get('pages')->map(function ($_item) {
    //        //                    return $_item->value;
    //        //                })->implode("\r\n");
    //        //            }
    //        //            $_tab[] = field_render('display_rules.pages', [
    //        //                'type'       => 'textarea',
    //        //                'label'      => 'Страницы',
    //        //                'value'      => $_values,
    //        //                'attributes' => [
    //        //                    'rows' => 5
    //        //                ],
    //        //                'help'       => 'Список URL станиц, на которых будет выводиться объект. Правила формирования:<ul><li>&lt;front&gt; - главная страница</li><li>articles/article-1 - доступно только для страницы с указаным URL</li><li>articles/* - доступно для всех страниц URL которых начинающихся с маски</li><li>*articles* - доступно для всех страниц URL которых содержит маску</li></ul>'
    //        //            ]);
    //        //        }
    //
    //        return [
    //            'title'   => 'Доступ к просмотру',
    //            'content' => $_tab
    //        ];
    //    }
    //

    public function __form_tab_translate($entity, $route, $field = 'title')
    {
        if (!USE_MULTI_LANGUAGE || !$entity->exists) return NULL;
        $_output = '<table class="uk-table uk-table-bordered uk-table-xsmall uk-table-hover uk-table-middle uk-margin-remove"><thead><tr>';
        $_output .= '<th class="uk-width-expand">Язык</th>';
        $_output .= '<th class="uk-width-auto uk-text-nowrap">Наличие перевода</th>';
        $_output .= '</tr></thead><tbody>';
        $_locales = config('laravellocalization.supportedLocales');
        foreach ($_locales as $_code => $_data) {
            if ($_code != DEFAULT_LOCALE) {
                $_exists_translate = $entity->getTranslation($field, $_code, FALSE);
                $_output .= '<tr>';
                $_output .= '<td>' . _l($_data['native'], "oleus.{$route}.translate", [
                        'p' => [
                            $entity,
                            $_code
                        ]
                    ]) . '</td>';
                $_output .= '<td class="uk-text-center">' . ($_exists_translate ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>') . '</td>';
                $_output .= '</tr>';
            }
        }
        $_output .= '</tbody></table>';

        return [
            'title'   => '<span class="uk-text-primary"><span uk-icon="icon: translate"></span> Переводы</span>',
            'content' => [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Языки доступные для перевода</span></h3>',
                $_output
            ]
        ];
    }

    public function __items($options = [])
    {
        $_options = [
            'base_route'  => $this->baseRoute,
            'buttons'     => [],
            'headers'     => [],
            'filters'     => [],
            'actions'     => [],
            'use_filters' => FALSE,
            'items'       => collect([]),
            'apiPath'     => NULL,
            'before'      => NULL,
            'after'       => NULL,
        ];

        return (object)array_merge_recursive_distinct($_options, $options);
    }

    public function __filter()
    {
        $this->filter = request()->all();
        if (isset($this->filter['page'])) unset($this->filter['page']);
        if ($this->filter) {
            Session::put("{$this->baseRoute}_filter", $this->filter);
        } else {
            $this->filter = Session::get("{$this->baseRoute}_filter");
        }
        if (isset($this->filter['clear'])) {
            Session::forget("{$this->baseRoute}_filter");
            $this->filterClear = TRUE;
        }
    }

    public function __can_permission($action = 'read')
    {
        if (isset($this->permissions[$action]) && $this->permissions[$action]) {
            return Auth::user()->can($this->permissions[$action]);
        }

        return TRUE;
    }

    public function __response_after_store(Request $request, $item)
    {
        if ($item->entityEventSave) event(new EntitySave($item));
        if ($this->baseRoute) {
            if ($request->input('save_and_create')) {
                return redirect()
                    ->route("oleus.{$this->baseRoute}.create")
                    ->with('notices', [
                        [
                            'message' => $this->notifications['created'],
                            'status'  => 'success'
                        ]
                    ]);
            }

            return redirect()
                ->route("oleus.{$this->baseRoute}.edit", [$item])
                ->with('notices', [
                    [
                        'message' => $this->notifications['created'],
                        'status'  => 'success'
                    ]
                ]);
        }

        return redirect()
            ->back();
    }

    public function __response_after_update(Request $request, $item)
    {
        if ($item->entityEventSave) event(new EntitySave($item));
        if ($this->baseRoute) {
            if ($request->has('translate')) {
                if ($request->input('save_close')) {
                    return redirect()
                        ->route("oleus.{$this->baseRoute}.edit", [$item])
                        ->with('notices', [
                            [
                                'message' => $this->notifications['updated'],
                                'status'  => 'success'
                            ]
                        ]);
                }

                return redirect()
                    ->route("oleus.{$this->baseRoute}.translate", [
                        $item,
                        $request->get('locale')
                    ])
                    ->with('notices', [
                        [
                            'message' => $this->notifications['translated'],
                            'status'  => 'success'
                        ]
                    ]);
            } else {
                if ($request->input('save_close')) {
                    return redirect()
                        ->route("oleus.{$this->baseRoute}")
                        ->with('notices', [
                            [
                                'message' => $this->notifications['updated'],
                                'status'  => 'success'
                            ]
                        ]);
                }

                return redirect()
                    ->route("oleus.{$this->baseRoute}.edit", [$item])
                    ->with('notices', [
                        [
                            'message' => $this->notifications['updated'],
                            'status'  => 'success'
                        ]
                    ]);
            }
        }

        return redirect()
            ->back();
    }

    public function __response_after_destroy(Request $request, $item)
    {
        if ($item->entityEventDelete) event(new EntityDelete($item));
        if ($this->baseRoute) {
            return redirect()
                ->route("oleus.{$this->baseRoute}")
                ->with('notices', [
                    [
                        'message' => $this->notifications['deleted'],
                        'status'  => 'success'
                    ]
                ]);
        }

        return redirect()
            ->back();
    }
}
