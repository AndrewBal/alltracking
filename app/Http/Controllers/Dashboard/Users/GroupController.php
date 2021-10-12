<?php

namespace App\Http\Controllers\Dashboard\Users;

//use App\Exports\UsersExport;
use App\Exports\UsersExport;
use App\Imports\GroupUsers;
use App\Libraries\BaseController;
use App\Libraries\Form;
use App\Models\User\Group;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class GroupController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware([
            'permission:user_groups_read'
        ]);
        $this->titles['index'] = 'Группы пользователей';
        $this->baseRoute = 'groups';
        $this->permissions = [
            'read'   => 'user_groups_read',
            'view'   => 'user_groups_view',
            'create' => 'user_groups_create',
            'update' => 'user_groups_update',
            'delete' => 'user_groups_delete',
        ];
        $this->entity = new Group();
    }

    protected function _form($entity)
    {
        $_form = $this->__form();
        $_form->use_multi_language = FALSE;
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_form->tabs = [
            [
                'title'   => 'Основное',
                'content' => [
                    '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                    render_field('name', [
                        'label'      => 'Название группы',
                        'value'      => $entity->name,
                        'attributes' => [
                            'autofocus' => TRUE,
                        ],
                        'required'   => TRUE,
                        'uikit'      => TRUE
                    ]),
                    '</div><div>',
                    render_field('discount', [
                        'type'       => 'number',
                        'label'      => 'Процент скидки, %',
                        'value'      => $entity->discount ? : 0,
                        'attributes' => [
                            'step' => 0.5,
                        ],
                        'required'   => TRUE,
                        'uikit'      => TRUE
                    ]),
                    '</div></div>'
                ],
            ]
        ];
        if ($entity->exists && $this->__can_permission('users_export_data')) {
            $_form->tabs[] = [
                'title'   => 'Пользователи',
                'content' => [
                    '<div class="uk-margin">',
                    _l('Скачать список пользователей', 'oleus.groups.export', [
                        'attributes' => [
                            'class' => 'uk-button uk-button-warning uk-button-small'
                        ]
                    ]),
                    render_field('file', [
                        'type'  => 'file',
                        'label' => 'Выбор списка пользоватлей с указанием группы',
                        'uikit' => TRUE
                    ]),
                    '</div>'
                ]
            ];
        }
        $_form->tabs[] = $this->__form_tab_translate($entity, $this->baseRoute, 'name');

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
                    render_field('name', [
                        'label'    => 'Название группы',
                        'value'    => $entity->getTranslation('name', $entity->frontLocale),
                        'required' => TRUE,
                        'uikit'    => TRUE
                    ]),
                ]
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
                'Название группы',
                $item->name,
            ],
            [
                'Процент скидки, %',
                $item->discount,
            ],
            [
                'Кол-во пользователей группы',
                $item->count_users,
            ]
        ];

        return $_view;
    }

    protected function _items($wrap)
    {
        $_query = Group::select([
            'id',
            'name',
            'discount'
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
                'data' => 'Название группы',
            ],
            [
                'class' => 'uk-width-small uk-text-center',
                'data'  => 'Процент скидки, %',
            ],
            [
                'class' => 'uk-width-small uk-text-center',
                'data'  => 'Кол-во пользователей',
            ],
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
                    $item->name,
                    (string)$item->discount,
                    (string)$item->count_users
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
        $this->validate($request, [
            'name'     => 'required',
            'discount' => 'required|numeric|min:0|max:100',
        ], [], [
            'name'     => 'Название группы',
            'discount' => 'Процент скидки',
        ]);
        $_save = $request->only([
            'name',
            'discount',
        ]);
        $_item = $this->entity->create($_save);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Group $item)
    {
        if ($request->has('translate')) {
            $_locale = $request->get('locale', $this->defaultLocale);
            $this->validate($request, [
                'name' => 'required',
            ], [], [
                'name' => 'Название группы',
            ]);
            $_save = $request->only([
                'name',
            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            $_validate_rules = [
                'name'     => "required|unique:user_groups,name,{$item->id},id",
                'discount' => 'required|numeric|min:0|max:100',
            ];
            $_validate_attrs = [
                'name'     => 'Название группы',
                'discount' => 'Процент скидки',
            ];
            if ($request->hasFile('file')) {
                $_file = $request->file('file');
                $request->request->add([
                    'extension' => strtolower($_file->getClientOriginalExtension())
                ]);
                $_validate_rules['extension'] = 'required|in:xlsx,xls';
                $_validate_attrs['extension'] = 'Расширение файла';
            }
            $this->validate($request, $_validate_rules, [], $_validate_attrs);
            $_save = $request->only([
                'name',
                'discount',
            ]);
            $item->update($_save);
            if ($request->hasFile('file')) {
                $_file = $request->file('file');
                Excel::import(new GroupUsers($item), $_file->getRealPath());
            }
        }

        return $this->__response_after_update($request, $item);
    }

    public function users(Request $request)
    {
        ini_set('memory_limit', '1024M');
        if ($this->__can_permission('users_export_data') == FALSE) abort(403);

        return Excel::download(new UsersExport(TRUE), 'export_users.xlsx');
    }
}
