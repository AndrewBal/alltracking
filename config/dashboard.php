<?php

return [
    'styles'  => [
        [
            'url'        => '//fonts.googleapis.com/css?family=Roboto:300,400,500,700,900&amp;subset=cyrillic',
            'attributes' => [
                'async' => TRUE
            ],
            'position'   => 'header'
        ],
        [
            'url'        => 'dashboard/css/uikit.min.css',
            'attributes' => [],
            'position'   => 'header'
        ],
    ],
    'scripts' => [
        [
            'url'        => 'dashboard/js/uikit.min.js',
            'attributes' => [],
            'position'   => 'header'
        ],
        [
            'url'        => 'dashboard/js/jquery.min.js',
            'attributes' => [
            ],
            'position'   => 'header'
        ],
        [
            'url'        => 'dashboard/js/uikit-icons.min.js',
            'attributes' => [],
            'position'   => 'footer'
        ],
        [
            'url'        => 'dashboard/js/select2.min.js',
            'attributes' => [
            ],
            'position'   => 'footer'
        ],
        [
            'url'      => 'dashboard/libraries/ckeditor_sdk/ckeditor/ckeditor.js',
            'position' => 'footer'
        ],
        [
            'url'      => 'dashboard/js/inputmask.min.js',
            'position' => 'footer'
        ],
        [
            'url'        => 'dashboard/js/air-datepicker.min.js',
            'attributes' => [
            ],
            'position'   => 'footer'
        ],
        [
            'url'        => 'dashboard/js/jquery.easy-autocomplete.min.js',
            'attributes' => [
            ],
            'position'   => 'footer'
        ],
        [
            'url'        => 'dashboard/js/upload-file.ajax.js',
            'attributes' => [
            ],
            'position'   => 'footer'
        ],
        [
            'url'        => 'dashboard/js/use.ajax.js',
            'attributes' => [
            ],
            'position'   => 'footer'
        ],
        [
            'url'        => 'dashboard/js/app.js',
            'attributes' => [
            ],
            'position'   => 'footer'
        ],
    ],
    'menu'    => [
        [
            'link'       => 'Панель управления',
            'route'      => 'oleus',
            'icon'       => 'home',
            'permission' => 'access_dashboard'
        ],
        [
            'link'       => 'Пользователи',
            'icon'       => 'person',
            'permission' => [
                'roles_read',
                'users_read',
                'user_groups_read',
            ],
            'children'   => [
                [
                    'link'       => 'Пользователи',
                    'route'      => 'oleus.users',
                    'permission' => 'users_read'
                ],
                [
                    'link'       => 'Роли пользователей',
                    'route'      => 'oleus.roles',
                    'permission' => 'roles_read'
                ],
                [
                    'link'       => 'Группы пользователей',
                    'route'      => 'oleus.groups',
                    'permission' => 'user_groups_read'
                ],
            ]
        ],
        [
            'link'       => 'Структура',
            'icon'       => 'assignment',
            'permission' => [
                'pages_read',
                'nodes_read',
                'tags_read',
            ],
            'children'   => [
                [
                    'link'       => 'Страницы',
                    'route'      => 'oleus.pages',
                    'permission' => 'pages_read'
                ],
                [
                    'link'       => 'Страницы тегов',
                    'route'      => 'oleus.tags',
                    'permission' => 'tags_read'
                ],
                [
                    'link'       => 'Материалы',
                    'route'      => 'oleus.nodes',
                    'permission' => 'nodes_read'
                ],
                [
                    'link'       => 'Теги материалов',
                    'route'      => 'oleus.node_tags',
                    'permission' => 'tags_read'
                ],
            ]
        ],
        [
            'link'       => 'Компоненты',
            'icon'       => 'add_box',
            'permission' => [
                'menus_read',
                'blocks_read',
                'banners_read',
                'variables',
                'advantages_read',
                'sliders_read',
                'galleries_read',
                'faqs_read',
                //            'journal_read'
            ],
            'children'   => [
                [
                    'link'       => 'Меню',
                    'route'      => 'oleus.menus',
                    'permission' => 'menus_read'
                ],
                [
                    'link'       => 'Блоки',
                    'route'      => 'oleus.blocks',
                    'permission' => 'blocks_read'
                ],
                [
                    'link'       => 'Баннеры',
                    'route'      => 'oleus.banners',
                    'permission' => 'banners_read'
                ],
                [
                    'link'       => 'Преимущества',
                    'route'      => 'oleus.advantages',
                    'permission' => 'advantages_read'
                ],
                [
                    'link'       => 'Слайд-шоу',
                    'route'      => 'oleus.sliders',
                    'permission' => 'sliders_read'
                ],
                [
                    'link'       => 'Галереи',
                    'route'      => 'oleus.galleries',
                    'permission' => 'galleries_read'
                ],
                [
                    'link'       => 'Переменные',
                    'route'      => 'oleus.variables',
                    'permission' => 'variables'
                ],
                [
                    'link'       => 'Вопрос / Ответ',
                    'route'      => 'oleus.faqs',
                    'permission' => 'faqs_read'
                ]
                //                [
                //                    'link'       => 'Журнал событий',
                //                    'route'      => 'oleus.journal',
                //                    'permission' => 'journal_read'
                //                ]
                //            ]
            ],
        ],
        [
            'link'       => 'Формы',
            'icon'       => 'inbox',
            'permission' => [
                'forms_read',
                'forms_data_list_read',
            ],
            'children'   => [
                [
                    'link'       => 'Конструктор форм',
                    'route'      => 'oleus.forms',
                    'params'     => [],
                    'permission' => 'forms_read'
                ],
                [
                    'link'       => 'Данные форм',
                    'route'      => 'oleus.forms_data',
                    'permission' => 'forms_data_list_read'
                ]
            ]
        ],
        [
            'link'       => 'Настройки',
            'icon'       => 'settings',
            'permission' => [
                'settings',
            ],
            'children'   => [
                [
                    'link'       => 'Общие',
                    'route'      => 'oleus.settings.option',
                    'params'     => [
                        'setting' => 'overall'
                    ],
                    'permission' => 'settings'
                ],
                [
                    'link'       => 'SEO',
                    'route'      => 'oleus.settings.option',
                    'params'     => [
                        'setting' => 'seo'
                    ],
                    'permission' => 'settings'
                ],
                [
                    'link'       => 'Сервисы',
                    'route'      => 'oleus.settings.option',
                    'params'     => [
                        'setting' => 'services'
                    ],
                    'permission' => 'settings'
                ],
                [
                    'link'       => 'Контакты',
                    'route'      => 'oleus.settings.option',
                    'params'     => [
                        'setting' => 'contacts'
                    ],
                    'permission' => 'settings'
                ],
                //                [
                //                    'link'       => 'Редиректы',
                //                    'route'      => 'oleus.redirects',
                //                    'permission' => 'settings_control'
                //                ]
            ]
        ],
    ]
];
