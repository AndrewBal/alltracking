<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Models\Components\Gallery;
use App\Models\Components\GalleryItems;
use App\Models\Components\Slider;
use App\Models\Components\SliderItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Libraries\Fields;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class GalleryController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Список галерей';
        $this->titles['create'] = 'Добавить галерею';
        $this->titles['edit'] = 'Редактировать галерею';
        $this->titles['translate'] = 'Перевод галереи на :locale';
        $this->middleware([
            'permission:galleries_read'
        ]);
        $this->baseRoute = 'galleries';
        $this->permissions = [
            'read'   => 'galleries_read',
            'view'   => 'galleries_view',
            'create' => 'galleries_create',
            'update' => 'galleries_update',
            'delete' => 'galleries_delete'
        ];
        $this->entity = new Gallery();
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
                if (Str::is('gallery_*', $_preset_key)) {
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
                $_field_preset = render_field('preset_preview', [
                    'label'    => 'Формат отображаения изображения в списке',
                    'type'     => 'select',
                    'selected' => $entity->preset_preview,
                    'values'   => $_preset_values,
                    'uikit'    => TRUE,
                    'help'     => 'Первое значение ширина слайда, второе его высота. Auto - не учитывается значение параметра.'
                ]);
                $_field_preset = render_field('preset_full', [
                    'label'    => 'Формат отображаения полного изображения',
                    'type'     => 'select',
                    'selected' => $entity->preset_preview,
                    'values'   => $_preset_values,
                    'uikit'    => TRUE,
                    'help'     => 'Первое значение ширина слайда, второе его высота. Auto - не учитывается значение параметра.'
                ]);
            }
        }
        $_form->tabs = [
            [
                'title'   => 'Основное',
                'content' => [
                    render_field('title', [
                        'label'    => 'Название',
                        'value'    => $entity->title,
                        'required' => TRUE,
                        'uikit'    => TRUE
                    ]),
                    $_field_preset,
                    '<hr class="uk-divider-icon">',
                    render_field('hidden_title', [
                        'type'     => 'checkbox',
                        'selected' => $entity->exists ? $entity->hidden_title : 0,
                        'values'   => [
                            1 => 'Скрыть заголовок'
                        ],
                        'uikit'    => TRUE
                    ]),
                    render_field('status', [
                        'type'     => 'checkbox',
                        'selected' => $entity->exists ? $entity->status : 1,
                        'values'   => [
                            1 => 'Опубликовано'
                        ],
                        'uikit'    => TRUE
                    ]),
                ],
            ],
            [
                'title'   => 'Изображения',
                'content' => [
                    render_field('medias', [
                        'type'     => 'file_drop',
                        'label'    => 'Изображения в галереи',
                        'view'     => 'gallery',
                        'multiple' => TRUE,
                        'values'   => $entity->exists && ($_medias = $entity->_files_related()->wherePivot('type', 'medias')->orderBy('sort')->get()) ? $_medias : NULL,
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
                    'label'      => 'Заголовок',
                    'value'      => $entity->getTranslation('title', $entity->frontLocale, FALSE),
                    'attributes' => [
                        'autofocus' => TRUE,
                    ],
                    'required'   => TRUE,
                    'uikit'      => TRUE
                ]),
            ]
        ];

        return $_form;
    }

    protected function _items($wrap)
    {
        $_query = Gallery::orderByDesc('status')
            ->orderBy('title')
            ->select([
                '*'
            ])
            ->with([
                '_files_related'
            ])
            ->paginate($this->entity->getPerPage(), ['id']);
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
                'data' => 'Заголовок',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: photo_library">',
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
                    (string)$item->_files_related()->wherePivot('type', 'medias')->count(),
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

    protected function _view($item)
    {
        $_view = $this->__view();
        $_view->route_tag = $this->baseRoute;
        $_photos = NULL;
        if ($_medias = $item->_files_related()->wherePivot('type', 'medias')->orderBy('sort')->get()) {
            $_photos = '<div class="uk-preview uk-grid uk-grid-small uk-child-width-1-4@l uk-child-width-1-5@xl uk-child-width-1-3@m uk-child-width-1-2@s">';
            $_photos .= $_medias->map(function ($file) {
                return render_preview_file($file, [
                    'field' => 'file',
                    'view'  => 'view'
                ]);
            })->implode('');
            $_photos .= '</div>';
        }
        $_contents = [
            [
                'Заголовок',
                $item->title,
            ],
            [
                'Скрыть заголовок',
                $item->hidden_title ? 'да' : 'нет',
            ],
            [
                'Опубликовано',
                $item->status ? 'да' : 'нет',
            ],
            [
                'Фото',
                $_photos,
            ],
        ];
        $_view->contents = $_contents;

        return $_view;
    }

    public function store(Request $request)
    {
        if ($medias = $request->input('medias')) {
            $_media = file_get(array_keys($medias));
            Session::flash('medias', json_encode($_media->toArray()));
        }
        $this->validate($request, [
            'title'  => 'required',
            'medias' => 'required',
        ], [], [
            'title'  => 'Заголовок',
            'medias' => 'Изображения в галереи'
        ]);
        $_save = $request->only([
            'title',
            'preset_preview',
            'preset_full',
            'style_id',
            'style_class',
            'status',
            'hidden_title',
        ]);
        $_item = Gallery::create($_save);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Gallery $item)
    {
        if ($request->has('translate')) {
            $this->validate($request, [
                'title' => 'required'
            ], [], [
                'title' => 'Заголовок'
            ]);
            $_locale = $request->get('locale', $this->defaultLocale);
            $item->frontLocale = $_locale;
            $_save = $request->only([
                'title',
            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            if ($medias = $request->input('medias')) {
                $_media = file_get(array_keys($medias));
                Session::flash('medias', json_encode($_media->toArray()));
            }
            $this->validate($request, [
                'title'  => 'required',
                'medias' => 'required',
            ], [], [
                'title'  => 'Заголовок',
                'medias' => 'Изображения в галереи'
            ]);
            $_save = $request->only([
                'title',
                'preset_preview',
                'preset_full',
                'style_id',
                'style_class',
                'status',
                'hidden_title',
            ]);
            $item->update($_save);
            Session::forget([
                'medias',
            ]);
        }

        return $this->__response_after_update($request, $item);
    }

    public function item(Request $request, Gallery $entity, $action, $id = NULL)
    {
        $commands = [];
        switch ($action) {
            case 'add':
            case 'edit':
                $_item = $id ? GalleryItems::find($id) : new GalleryItems();
                $commands['commands'][] = [
                    'command' => 'UK_modal',
                    'options' => [
                        'content'     => view('backend.partials.galleries.item_modal', compact('_item', 'entity'))
                            ->render(),
                        'classDialog' => 'uk-width-1-2'
                    ]
                ];
                break;
            case 'save':
                $_default_locale = config('app.default_locale');
                $_save = $request->input('item');
                if ($background = $_save['background_fid']) {
                    $_background = array_shift($background);
                    Session::flash('item.background_fid', json_encode([file_get($_background['id'])]));
                }
                $validate_rules = [
                    'item.background_fid' => 'required'
                ];
                $validator = Validator::make($request->all(), $validate_rules, [], [
                    'item.background_fid' => 'Фон слайда'
                ]);
                $commands['commands'][] = [
                    'command' => 'removeClass',
                    'options' => [
                        'target' => '#modal-gallery-item-form input',
                        'data'   => 'uk-form-danger'
                    ]
                ];
                if ($validator->fails()) {
                    foreach ($validator->errors()->messages() as $field => $message) {
                        $commands['commands'][] = [
                            'command' => 'addClass',
                            'options' => [
                                'target' => '#' . Fields::render_field_id($field),
                                'data'   => 'uk-form-danger'
                            ]
                        ];
                    }
                    $commands['commands'][] = [
                        'command' => 'UK_notification',
                        'options' => [
                            'status' => 'danger',
                            'text'   => 'Ошибка! Запрос не прошел проверку'
                        ]
                    ];
                } else {
                    $_save['gallery_id'] = $entity->id;
                    if (isset($_background)) $_save['background_fid'] = (int)$_background['id'];
                    $_item_id = $_save['id'];
                    unset($_save['id']);
                    $_save['status'] = (int)($_save['status'] ?? 0);
                    $_item = $_item_id ? GalleryItems::find($_item_id) : new GalleryItems();
                    $_item->fill($_save);
                    $_item->save();
                    Session::forget([
                        'item.background_fid'
                    ]);
                    $items = $entity->_items()
                        ->orderBy('sort')
                        ->get();
                    $_items_output = View::make('backend.partials.galleries.items_table', compact('items'))
                        ->render(function ($view, $content) {
                            return minimize_html($content);
                        });
                    $commands['commands'][] = [
                        'command' => 'html',
                        'options' => [
                            'target' => '#list-galleries-items',
                            'data'   => $_items_output
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'removeClass',
                        'options' => [
                            'target' => '#uk-tab-body a.uk-button-save-sorting',
                            'data'   => 'uk-hidden'
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'UK_notification',
                        'options' => [
                            'text'   => 'Элемент сохранен',
                            'status' => 'success',
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'UK_modalClose',
                        'options' => []
                    ];
                }
                break;
            case 'destroy':
                GalleryItems::find($id)
                    ->delete();
                $items = $entity->_items()
                    ->orderBy('sort')
                    ->get();
                if ($items->isNotEmpty()) {
                    $commands['commands'][] = [
                        'command' => 'html',
                        'options' => [
                            'target' => '#list-galleries-items',
                            'data'   => view('backend.partials.galleries.items_table', compact('items'))
                                ->render()
                        ]
                    ];
                } else {
                    $commands['commands'][] = [
                        'command' => 'html',
                        'options' => [
                            'target' => '#list-galleries-items',
                            'data'   => '<div class="uk-alert uk-alert-warning uk-border-rounded" uk-alert>Список элементов пуст</div>'
                        ]
                    ];
                    $commands['commands'][] = [
                        'command' => 'addClass',
                        'options' => [
                            'target' => '#uk-tab-body a.uk-button-save-sorting',
                            'data'   => 'uk-hidden'
                        ]
                    ];
                }
                $commands['commands'][] = [
                    'command' => 'UK_notification',
                    'options' => [
                        'status' => 'success',
                        'text'   => 'Элемент удален'
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'UK_modalClose'
                ];
                break;
        }
        update_last_modified_timestamp();

        return response($commands, 200);
    }

    public function save_sort(Request $request, Gallery $entity)
    {
        $_sorting = $request->all();
        $entity->_items->each(function ($_item) use ($_sorting) {
            $_item->sort = $_sorting[$_item->id] ?? 0;
            $_item->save();
        });
        $items = $entity->_items()
            ->orderBy('sort')
            ->get();
        $commands['commands'][] = [
            'command' => 'html',
            'options' => [
                'target' => '#list-galleries-items',
                'data'   => View('backend.partials.galleries.items_table', compact('items'))
                    ->render(function ($view, $content) {
                        return minimize_html($content);
                    })
            ]
        ];

        return response($commands, 200);
    }
}
