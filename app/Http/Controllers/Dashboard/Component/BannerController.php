<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Models\Components\Banner;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class BannerController extends BaseController
{
    use Authorizable;

    public function __construct()
    {
        parent::__construct();
        $this->middleware([
            'permission:banners_read'
        ]);
        $this->baseRoute = 'banners';
        $this->permissions = [
            'read'   => 'banners_read',
            'view'   => 'banners_view',
            'create' => 'banners_create',
            'update' => 'banners_update',
            'delete' => 'banners_delete',
        ];
        $this->titles['index'] = 'Список баннеров';
        $this->titles['create'] = 'Добавить баннер';
        $this->titles['edit'] = 'Редактировать баннер';
        $this->titles['translate'] = 'Перевод баннера на :locale';
        $this->entity = new Banner();
    }

    protected function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_field_preset = NULL;
        if ($_presets = config('preset_images')) {
            $_preset_values = [];
            foreach ($_presets as $_preset_key => $_preset_value) {
                if (Str::is('banner_*', $_preset_key)) {
                    $_label = $_preset_key;
                    if (isset($_preset_value['width']) && isset($_preset_value['height'])) {
                        $_label = "{$_preset_value['width']}px * {$_preset_value['height']}px";
                    } elseif (isset($_preset_value['width'])) {
                        $_label = "{$_preset_value['width']}px * auto";
                    } elseif (isset($_preset_value['height'])) {
                        $_label = "auto * {$_preset_value['height']}px";
                    }
                    $_preset_values[$_preset_key] = "{$_preset_key} ({$_label})";
                }
            }
            if (count($_preset_values)) {
                array_unshift($_preset_values, '-- Выбрать --');
                $_field_preset = render_field('preset', [
                    'label'    => 'Формат фонового изображения',
                    'type'     => 'select',
                    'selected' => $entity->preset,
                    'values'   => $_preset_values,
                    'uikit'    => TRUE,
                    'help'     => 'Первое значение ширина, второе высота. Auto - не учитывается значение параметра.'
                ]);
            }
        }
        $_form->tabs = [
            [
                'title'   => 'Основные параметры',
                'content' => [
                    render_field('title', [
                        'label'    => 'Название',
                        'value'    => $entity->title,
                        'required' => TRUE,
                        'uikit'    => TRUE
                    ]),
                    render_field('background_fid', [
                        'type'   => 'file_drop',
                        'label'  => 'Фоновое изображение',
                        'allow'  => 'jpg|jpeg|png',
                        'values' => $entity->exists && $entity->_background ? [$entity->_background] : NULL,
                        'uikit'  => TRUE,
                    ]),
                    $_field_preset,
                    render_field('body', [
                        'label'      => 'Содержимое',
                        'type'       => 'textarea',
                        'editor'     => TRUE,
                        'value'      => $entity->body,
                        'attributes' => [
                            'rows' => 4,
                        ],
                        'uikit'      => TRUE
                    ]),
                    '<h3 class="uk-heading-line uk-text-uppercase uk-margin-remove-top"><span>Ссылка для перехода</span></h3>',
                    render_field('link', [
                        'label' => 'Ссылка для перехода по клику',
                        'value' => $entity->link,
                        'uikit' => TRUE
                    ]),
                    render_field('link_attributes', [
                        'type'       => 'textarea',
                        'label'      => 'Дополнительные атрибуты',
                        'value'      => $entity->link_attributes,
                        'attributes' => [
                            'rows' => 2,
                        ],
                        'uikit'      => TRUE
                    ]),
                    '<hr class="uk-divider-icon">',
                    render_field('status', [
                        'type'     => 'checkbox',
                        'selected' => $entity->exists ? $entity->status : 1,
                        'values'   => [
                            1 => 'Опубликовано',
                        ],
                        'uikit'    => TRUE
                    ]),
                ]
            ],
            $this->__form_tab_display_style($entity),
            $this->__form_tab_translate($entity, $this->baseRoute, 'title')
        ];

        return $_form;
    }

    protected function _form_translate($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, [
            'translate' => $this->permissions['update']
        ]);
        $_form->use_multi_language = FALSE;
        $_form->tabs[] = [
            'title'   => 'Параметры перевода',
            'content' => [
                render_field('locale', [
                    'type'  => 'hidden',
                    'value' => $entity->frontLocale
                ]),
                render_field('translate', [
                    'type'  => 'hidden',
                    'value' => 1
                ]),
                render_field('title', [
                    'label'    => 'Заголовок',
                    'value'    => $entity->getTranslation('title', $entity->frontLocale, FALSE),
                    'required' => TRUE,
                    'uikit'    => TRUE
                ]),
                render_field('background_fid', [
                    'type'   => 'file_drop',
                    'label'  => 'Фоновое изображение',
                    'allow'  => 'jpg|jpeg|png',
                    'values' => $entity->exists && $entity->_background ? [$entity->_background] : NULL,
                    'uikit'  => TRUE,
                ]),
                render_field('body', [
                    'label'      => 'Содержимое',
                    'type'       => 'textarea',
                    'value'      => $entity->getTranslation('body', $entity->frontLocale, FALSE),
                    'attributes' => [
                        'rows' => 4,
                    ],
                    'uikit'      => TRUE
                ]),
                render_field('link', [
                    'label' => 'Ссылка для перехода по клику',
                    'value' => $entity->getTranslation('link', $entity->frontLocale, FALSE),
                    'uikit' => TRUE
                ]),
            ]
        ];

        return $_form;
    }

    protected function _view($item)
    {
        $_view = $this->__view();
        $_view->route_tag = $this->baseRoute;
        $_view->contents = [
            [
                'Фоновое изображение',
                render_image($item->_background, 'thumb_preview_view')
            ],
            [
                'Название',
                $item->title,
            ],
            [
                'Описание',
                $item->body,
            ],
            [
                'Ссылка для перехода по клику',
                $item->link,
            ],
            [
                'Опубликовано',
                $item->status ? 'да' : 'нет',
            ],
        ];

        return $_view;
    }

    protected function _items($wrap)
    {
        $_query = Banner::orderByDesc('status')
            ->paginate();
        $_buttons = [];
        if ($this->__can_permission('create')) {
            $_buttons[] = _l('Добавить', "oleus.{$this->baseRoute}.create", [
                'attributes' => [
                    'class' => 'uk-button uk-button-success uk-button-small',
                ]
            ]);
        }
        $_headers = [
            [
                'class' => 'uk-width-xsmall uk-text-center',
                'data'  => 'ID',
            ],
            [
                'data' => 'Название',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: link">',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: visibility">',
            ]
        ];
        if ($this->__can_permission('view')) {
            $_headers[] = [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: desktop">',
            ];
        }
        if ($this->__can_permission('update')) {
            $_headers[] = [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: edit">',
            ];
        }
        if ($_query->isNotEmpty()) {
            $_query->getCollection()->transform(function ($item) {
                $_response = [
                    "<div class='uk-text-center uk-text-bold'>{$item->id}</div>",
                    $item->title,
                    $item->link ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                    $item->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                ];
                if ($this->__can_permission('view')) {
                    $_response[] = _l('', "oleus.{$this->baseRoute}.show", [
                        'p'          => [
                            $item
                        ],
                        'attributes' => [
                            'class'   => 'uk-button-icon uk-button uk-button-success uk-button-xsmall',
                            'uk-icon' => 'icon: desktop'
                        ]
                    ]);
                }
                if ($this->__can_permission('update')) {
                    $_response[] = _l('', "oleus.{$this->baseRoute}.edit", [
                        'p'          => [
                            $item
                        ],
                        'attributes' => [
                            'class'   => 'uk-button-icon uk-button uk-button-primary uk-button-xsmall',
                            'uk-icon' => 'icon: edit'
                        ]
                    ]);
                }

                return $_response;
            });
        }
        $_items = $this->__items([
            'buttons' => $_buttons,
            'headers' => $_headers,
            'items'   => $_query,
        ]);
        $_wrap = $wrap;

        return view('backend.partials.items', compact('_items', '_wrap'));
    }

    public function store(Request $request)
    {
        if ($background_fid = $request->input('background_fid')) {
            $_background_fid = array_shift($background_fid);
            Session::flash('background_fid', json_encode([file_get($_background_fid['id'])]));
        }
        $this->validate($request, [
            'title'          => 'required|string',
            'body'           => 'required_without:background_fid',
            'background_fid' => 'required_without:body',
        ], [], [
            'title'          => 'Заголовок',
            'body'           => 'Содержимое',
            'background_fid' => 'Фоновое изображение',
        ]);
        $_save = $request->only([
            'title',
            'body',
            'link',
            'style_id',
            'style_class',
            'background_fid',
            'preset',
            'link_attributes',
            'status',
            'hidden_title',
        ]);
        $_save['background_fid'] = $_background_fid['id'] ?? NULL;
        $_item = Banner::create($_save);
        Session::forget([
            'background_fid'
        ]);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Banner $item)
    {
        if ($background_fid = $request->input('background_fid')) {
            $_background_fid = array_shift($background_fid);
            Session::flash('background_fid', json_encode([file_get($_background_fid['id'])]));
        }
        if ($request->has('translate')) {
            $_locale = $request->get('locale', $this->defaultLocale);
            $item->frontLocale = $_locale;
            $this->validate($request, [
                'title'          => 'required|string',
                'body'           => 'required_without:background_fid',
                'background_fid' => 'required_without:body',
            ], [], [
                'title'          => 'Заголовок',
                'body'           => 'Содержимое',
                'background_fid' => 'Фоновое изображение',
            ]);
            $_save = $request->only([
                'title',
                'body',
            ]);
            $_save['background_fid'] = $_background_fid['id'] ?? NULL;
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            $this->validate($request, [
                'title'          => 'required|string',
                'body'           => 'required_without:background_fid',
                'background_fid' => 'required_without:body',
            ], [], [
                'title'          => 'Заголовок',
                'body'           => 'Содержимое',
                'background_fid' => 'Фоновое изображение',
            ]);
            $_save = $request->only([
                'title',
                'link',
                'body',
                'style_id',
                'style_class',
                'preset',
                'link_attributes',
                'status',
                'hidden_title',
            ]);
            $_save['background_fid'] = $_background_fid['id'] ?? NULL;
            $item->update($_save);
        }
        Session::forget([
            'background_fid'
        ]);

        return $this->__response_after_update($request, $item);
    }

    public function destroy(Request $request, Banner $item)
    {
        $item->delete();

        return $this->__response_after_destroy($request, $item);
    }
}
