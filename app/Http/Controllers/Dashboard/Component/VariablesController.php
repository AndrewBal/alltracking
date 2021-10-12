<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Models\Components\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VariablesController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware([
            'permission:variables'
        ]);
        $this->titles['index'] = 'Список переменных';
        $this->titles['create'] = 'Добавить переменную';
        $this->titles['edit'] = 'Редактировать переменную';
        $this->titles['translate'] = 'Перевод переменной на :locale';
        $this->baseRoute = 'variables';
        $this->permissions = [
            'read'   => 'variables',
            'view'   => 'variables',
            'create' => 'variables',
            'update' => 'variables',
            'delete' => 'variables',
        ];
        $this->entity = new Variable();
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
                    '<div class="uk-grid uk-child-width-1-2"><div>',
                    render_field('name', [
                        'label'      => 'Название переменной',
                        'value'      => $entity->name,
                        'attributes' => [
                            'autofocus' => TRUE,
                        ],
                        'help'       => 'Название переменной (используется только в панели для осозная, что это за переменная и, что она в себе хранит)',
                        'required'   => TRUE,
                        'uikit'      => TRUE
                    ]),
                    '</div><div>',
                    render_field('key', [
                        'label'      => 'Машинное имя (ключ по которому она будет доступна)',
                        'value'      => $entity->key,
                        'attributes' => [
                            'readonly' => $entity->exists ? TRUE : FALSE
                        ],
                        'help'       => $entity->exists ? NULL : 'При заполнении можно использовать символы латиского алфавита в нижнем регистре, цифры и знак подчеркивания.',
                        'required'   => !$entity->exists ? TRUE : FALSE,
                        'uikit'      => TRUE
                    ]),
                    '</div></div>',
                    render_field('value', [
                        'label'      => 'Значение переменной',
                        'type'       => 'textarea',
                        'class'      => 'uk-codeMirror',
                        'value'      => $entity->value,
                        'attributes' => [
                            'rows' => 12,
                        ],
                        'help'       => 'Задайте значение переменной. Поле воспринимает код',
                        'required'   => TRUE,
                        'uikit'      => TRUE
                    ]),
                    render_field('comment', [
                        'label'      => 'Комментарий',
                        'type'       => 'textarea',
                        'value'      => $entity->comment,
                        'attributes' => [
                            'rows' => 4,
                        ],
                        'uikit'      => TRUE
                    ]),
                    render_field('use_php', [
                        'type'   => 'checkbox',
                        'value'  => $entity->use_php,
                        'values' => [
                            1 => 'Исполняемы код <span class="uk-text-bold">&lt;PHP&gt;</span>'
                        ],
                        'uikit'  => TRUE
                    ])
                ]
            ],
        ];
        $_form->tabs[] = $this->__form_tab_translate($entity, $this->baseRoute, 'value');

        return $_form;
    }

    public function _form_translate($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, [
            'translate' => $this->permissions['update']
        ]);
        $_form->tabs = [
            [
                'title'   => 'Основное',
                'content' => [
                    render_field('locale', [
                        'type'  => 'hidden',
                        'value' => $entity->frontLocale
                    ]),
                    render_field('translate', [
                        'type'  => 'hidden',
                        'value' => 1
                    ]),
                    render_field('value', [
                        'label'      => 'Название',
                        'value'      => $entity->getTranslation('value', $entity->frontLocale),
                        'required'   => TRUE,
                        'uikit'      => TRUE,
                        'attributes' => [
                            'class' => 'uk-codeMirror',
                            'rows'  => 12,
                        ],
                        'type'       => 'textarea',
                    ]),
                ]
            ]
        ];

        return $_form;
    }

    protected function _items($wrap)
    {
        $this->__filter();
        if ($this->filterClear) {
            return redirect()
                ->route("oleus.{$this->baseRoute}");
        }
        $_filter = $this->filter;
        $_query = Variable::when($_filter, function ($query) use ($_filter) {
            if ($_filter['name']) $query->where('name', 'like', "%{$_filter['name']}%");
        })
            ->orderBy('name')
            ->select([
                '*'
            ])
            ->paginate($this->entity->getPerPage(), ['id']);
        $_buttons = [];
        $_filters = [];
        if ($this->__can_permission('create')) {
            $_buttons[] = _l('Добавить', "oleus.{$this->baseRoute}.create", [
                'attributes' => [
                    'class' => 'uk-button uk-button-success uk-button-small',
                ]
            ]);
        }
        $_headers = [
            [
                'class' => 'uk-width-small uk-text-center',
                'data'  => 'KEY',
            ],
            [
                'data' => 'Название переменной',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: code">',
            ]
        ];
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
                    $item->key,
                    $item->name,
                    $item->use_php ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                ];
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
            $_filters[] = [
                'data' => render_field('name', [
                    'value'      => $_filter['name'] ?? NULL,
                    'attributes' => [
                        'placeholder' => 'Название переменной',
                        'uk-form-small'
                    ],
                    'item_class' => [
                        'uk-margin-small-top uk-width-medium'
                    ],
                    'uikit'      => TRUE
                ])
            ];
        }
        $_items = $this->__items([
            'filters'     => $_filters,
            'use_filters' => $_filter ? TRUE : FALSE,
            'buttons'     => $_buttons,
            'headers'     => $_headers,
            'items'       => $_query,
        ]);
        $_wrap = $wrap;

        return view('backend.partials.items', compact('_items', '_wrap'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'key'   => 'sometimes|required|unique:variables|regex:/^[a-z_0-9]+$/u',
            'name'  => 'sometimes|required',
            'value' => 'required',
        ], [], [
            'key'   => 'Машинное имя',
            'title' => 'Название переменной',
            'value' => 'Значение переменной'
        ]);
        $_save = $request->only([
            'key',
            'name',
            'value',
            'comment',
            'use_php'
        ]);
        $_item = Variable::create($_save);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Variable $item)
    {
        if ($request->has('translate')) {
            $_locale = $request->get('locale', $this->defaultLocale);
            $this->validate($request, [
                'value' => 'required',
            ], [], [
                'value' => 'Значение переменной'
            ]);
            $_save = $request->only([
                'value',
            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            $this->validate($request, [
                'name'  => "sometimes|required",
                'value' => 'required',
            ], [], [
                'name'  => 'Название переменной',
                'value' => 'Значение переменной'
            ]);
            $_save = $request->only([
                'name',
                'value',
                'comment',
                'use_php',
            ]);
            $item->update($_save);
        }

        return $this->__response_after_update($request, $item);
    }
}
