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
            'link'       => '???????????? ????????????????????',
            'route'      => 'oleus',
            'icon'       => 'home',
            'permission' => 'access_dashboard'
        ],
        [
            'link'       => '????????????????????????',
            'icon'       => 'person',
            'permission' => [
                'roles_read',
                'users_read',
                'user_groups_read',
            ],
            'children'   => [
                [
                    'link'       => '????????????????????????',
                    'route'      => 'oleus.users',
                    'permission' => 'users_read'
                ],
                [
                    'link'       => '???????? ??????????????????????????',
                    'route'      => 'oleus.roles',
                    'permission' => 'roles_read'
                ],
                [
                    'link'       => '???????????? ??????????????????????????',
                    'route'      => 'oleus.groups',
                    'permission' => 'user_groups_read'
                ],
            ]
        ],
        [
            'link'       => '??????????????????',
            'icon'       => 'assignment',
            'permission' => [
                'pages_read',
                'nodes_read',
                'tags_read',
            ],
            'children'   => [
                [
                    'link'       => '????????????????',
                    'route'      => 'oleus.pages',
                    'permission' => 'pages_read'
                ],
                [
                    'link'       => '???????????????? ??????????',
                    'route'      => 'oleus.tags',
                    'permission' => 'tags_read'
                ],
                [
                    'link'       => '??????????????????',
                    'route'      => 'oleus.nodes',
                    'permission' => 'nodes_read'
                ],
                [
                    'link'       => '???????? ????????????????????',
                    'route'      => 'oleus.node_tags',
                    'permission' => 'tags_read'
                ],
            ]
        ],
        [
            'link'       => '????????????????????',
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
                    'link'       => '????????',
                    'route'      => 'oleus.menus',
                    'permission' => 'menus_read'
                ],
                [
                    'link'       => '??????????',
                    'route'      => 'oleus.blocks',
                    'permission' => 'blocks_read'
                ],
                [
                    'link'       => '??????????????',
                    'route'      => 'oleus.banners',
                    'permission' => 'banners_read'
                ],
                [
                    'link'       => '????????????????????????',
                    'route'      => 'oleus.advantages',
                    'permission' => 'advantages_read'
                ],
                [
                    'link'       => '??????????-??????',
                    'route'      => 'oleus.sliders',
                    'permission' => 'sliders_read'
                ],
                [
                    'link'       => '??????????????',
                    'route'      => 'oleus.galleries',
                    'permission' => 'galleries_read'
                ],
                [
                    'link'       => '????????????????????',
                    'route'      => 'oleus.variables',
                    'permission' => 'variables'
                ],
                [
                    'link'       => '???????????? / ??????????',
                    'route'      => 'oleus.faqs',
                    'permission' => 'faqs_read'
                ]
                //                [
                //                    'link'       => '???????????? ??????????????',
                //                    'route'      => 'oleus.journal',
                //                    'permission' => 'journal_read'
                //                ]
                //            ]
            ],
        ],
        [
            'link'       => '??????????',
            'icon'       => 'inbox',
            'permission' => [
                'forms_read',
                'forms_data_list_read',
            ],
            'children'   => [
                [
                    'link'       => '?????????????????????? ????????',
                    'route'      => 'oleus.forms',
                    'params'     => [],
                    'permission' => 'forms_read'
                ],
                [
                    'link'       => '???????????? ????????',
                    'route'      => 'oleus.forms_data',
                    'permission' => 'forms_data_list_read'
                ]
            ]
        ],
        [
            'link'       => '??????????????????',
            'icon'       => 'settings',
            'permission' => [
                'settings',
            ],
            'children'   => [
                [
                    'link'       => '??????????',
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
                    'link'       => '??????????????',
                    'route'      => 'oleus.settings.option',
                    'params'     => [
                        'setting' => 'services'
                    ],
                    'permission' => 'settings'
                ],
                [
                    'link'       => '????????????????',
                    'route'      => 'oleus.settings.option',
                    'params'     => [
                        'setting' => 'contacts'
                    ],
                    'permission' => 'settings'
                ],
                //                [
                //                    'link'       => '??????????????????',
                //                    'route'      => 'oleus.redirects',
                //                    'permission' => 'settings_control'
                //                ]
            ]
        ],
    ]
];
