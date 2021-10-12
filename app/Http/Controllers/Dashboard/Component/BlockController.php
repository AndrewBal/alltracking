<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Models\Components\Block;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BlockController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware([
            'permission:blocks_read'
        ]);
        $this->titles['index'] = 'Список блоков';
        $this->titles['create'] = 'Добавить блок';
        $this->titles['edit'] = 'Редактировать блок';
        $this->titles['translate'] = 'Перевод блока на :locale';
        $this->baseRoute = 'blocks';
        $this->permissions = [
            'read'   => 'blocks_read',
            'view'   => 'blocks_view',
            'create' => 'blocks_create',
            'update' => 'blocks_update',
            'delete' => 'blocks_delete',
        ];
        $this->entity = new Block();
    }

    protected function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_form->tabs = [
            [
                'title'   => 'Основное',
                'content' => [
                    render_field('title', [
                        'label'      => 'Заголовок',
                        'value'      => $entity->title,
                        'attributes' => [
                            'autofocus' => TRUE
                        ],
                        'required'   => TRUE,
                        'uikit'      => TRUE
                    ]),
                    render_field('sub_title', [
                        'label' => 'Под заголовок',
                        'value' => $entity->sub_title,
                        'uikit' => TRUE
                    ]),
                    render_field('body', [
                        'label'      => 'Содержимое',
                        'type'       => 'textarea',
                        'editor'     => TRUE,
                        'value'      => $entity->body,
                        'attributes' => [
                            'rows' => 8,
                        ],
                        'required'   => FALSE,
                        'uikit'      => TRUE
                    ]),
                    '<hr class="uk-divider-icon">',
                    render_field('hidden_title', [
                        'type'     => 'checkbox',
                        'selected' => $entity->hidden_title,
                        'values'   => [
                            1 => 'Скрыть заголовок при выводе на страницу',
                        ],
                        'uikit'    => TRUE
                    ]),
                    render_field('status', [
                        'type'     => 'checkbox',
                        'selected' => $entity->exists ? $entity->status : 1,
                        'values'   => [
                            1 => 'Опубликовано',
                        ],
                        'uikit'    => TRUE
                    ])
                ]
            ],
            $this->__form_tab_display_style($entity, 'background'),
            $this->__form_tab_media_files($entity),
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
                render_field('sub_title', [
                    'label' => 'Под заголовок',
                    'value' => $entity->getTranslation('sub_title', $entity->frontLocale, FALSE),
                    'uikit' => TRUE
                ]),
                render_field('body', [
                    'label'      => 'Содержимое',
                    'type'       => 'textarea',
                    'editor'     => TRUE,
                    'value'      => $entity->getTranslation('body', $entity->frontLocale, FALSE),
                    'attributes' => [
                        'rows' => 8,
                    ],
                    'uikit'      => TRUE
                ]),
            ]
        ];

        return $_form;
    }

    protected function _view($item)
    {
        $_view = $this->__view();
        $_view->route_tag = $this->baseRoute;
        $_contents = [
            [
                'Заголовок',
                $item->title,
            ],
            [
                'Описание',
                $item->body,
            ],
            [
                'Скрыть заголовок при выводе на страницу',
                $item->hidden_title ? 'да' : 'нет',
            ],
            [
                'Опубликовано',
                $item->status ? 'да' : 'нет',
            ],
        ];
        if ($_medias = $item->_files_related()->wherePivot('type', 'medias')->orderBy('sort')->get()) {
            $_output = '<div class="uk-preview uk-grid uk-grid-small uk-child-width-1-4@l uk-child-width-1-5@xl uk-child-width-1-3@m uk-child-width-1-2@s">';
            $_output .= $_medias->map(function ($file) {
                return render_preview_file($file, [
                    'field' => 'file',
                    'view'  => 'view'
                ]);
            })->implode('');
            $_output .= '</div>';
            $_contents[] = [
                'Вложенные изображения',
                $_output,
            ];
        }
        if ($_files = $item->_files_related()->wherePivot('type', 'files')->orderBy('sort')->get()) {
            $_output = '<div class="uk-preview uk-grid uk-grid-small uk-child-width-1-4@l uk-child-width-1-5@xl uk-child-width-1-3@m uk-child-width-1-2@s">';
            $_output .= $_files->map(function ($file) {
                return render_preview_file($file, [
                    'field' => 'file',
                    'view'  => 'view'
                ]);
            })->implode('');
            $_output .= '</div>';
            $_contents[] = [
                'Вложенные файлы',
                $_output,
            ];
        }
        $_view->contents = $_contents;

        return $_view;
    }

    protected function _items($wrap)
    {
        $_buttons = [];
        $_query = Block::orderBy('id')
            ->select([
                '*'
            ])
            ->paginate($this->entity->getPerPage(), ['id']);
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
        if ($medias = $request->input('medias')) {
            $_media = file_get(array_keys($medias));
            Session::flash('medias', json_encode($_media->toArray()));
        }
        if ($files = $request->input('files')) {
            $_files = file_get(array_keys($files));
            Session::flash('files', json_encode($_files->toArray()));
        }
        $this->validate($request, [
            'title' => 'required',

        ], [], [
            'title' => 'Заголовок',
            'body'  => 'Содержимое',
        ]);
        $_save = $request->only([
            'title',
            'sub_title',
            'body',
            'style_id',
            'style_class',
            'status',
            'hidden_title',
        ]);
        $_save['background_fid'] = $_background_fid['id'] ?? NULL;
        $_item = Block::create($_save);
        Session::forget([
            'background_fid',
            'medias',
            'files'
        ]);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Block $item)
    {
        if ($request->has('translate')) {
            $_locale = $request->get('locale', $this->defaultLocale);
            $item->frontLocale = $_locale;
            $this->validate($request, [
                'title' => 'required'
            ], [], [
                'title' => 'Заголовок'
            ]);
            $_save = $request->only([
                'title',
                'sub_title',
                'body',
            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            if ($background_fid = $request->input('background_fid')) {
                $_background_fid = array_shift($background_fid);
                Session::flash('background_fid', json_encode([file_get($_background_fid['id'])]));
            }
            if ($medias = $request->input('medias')) {
                $_media = file_get(array_keys($medias));
                Session::flash('medias', json_encode($_media->toArray()));
            }
            if ($files = $request->input('files')) {
                $_files = file_get(array_keys($files));
                Session::flash('files', json_encode($_files->toArray()));
            }
            $this->validate($request, [
                'title' => 'required',

            ], [], [
                'title' => 'Заголовок',
                'body'  => 'Содержимое',
            ]);
            $_save = $request->only([
                'title',
                'sub_title',
                'body',
                'style_id',
                'style_class',
                'hidden_title',
                'status',
            ]);
            $_save['background_fid'] = $_background_fid['id'] ?? NULL;
            $item->update($_save);
        }
        Session::forget([
            'background_fid',
            'medias',
            'files'
        ]);

        return $this->__response_after_update($request, $item);
    }
}
