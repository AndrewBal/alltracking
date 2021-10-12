<?php

namespace App\Http\Controllers\Dashboard\Users;

use App\Exports\UsersExport;
use App\Libraries\BaseController;
use App\Models\User\Group;
use App\Models\User\Role;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware([
            'permission:users_read'
        ]);
        $this->titles['index'] = 'Пользователи';
        $this->titles['create'] = 'Добавить пользователя';
        $this->titles['edit'] = 'Редактировать пользователя';
        $this->baseRoute = 'users';
        $this->permissions = [
            'read'   => 'users_read',
            'view'   => 'users_view',
            'create' => 'users_create',
            'update' => 'users_update',
            'delete' => 'users_delete',
        ];
        $this->entity = new User();
    }

    protected function _form($entity)
    {
        global $wrap;
        $_user = $wrap['user'];
        $_roles = Role::all();
        $_groups = Group::all();
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_field_roles = NULL;
        $_field_groups = NULL;
        $_field_blocked = NULL;
        if ($_user->can('roles_assignment')) {
            $_roles = $_roles->filter(function ($_role) use ($_user) {
                if (!$_user->hasRole('admin')) {
                    return $_role->name != 'admin';
                }

                return TRUE;
            })
                ->pluck('display_name', 'name')
                ->toArray();
            $_field_roles = render_field('role', [
                'type'     => 'select',
                'label'    => 'Роль пользователя',
                'value'    => $entity->exists ? $entity->getRoleNames()->first() : 'user',
                'values'   => $_roles,
                'required' => TRUE,
                'uikit'    => TRUE
            ]);
        } else {
            $_field_roles = render_field('role', [
                'type'  => 'hidden',
                'value' => $entity->exists ? $entity->getRoleNames()->first() : 'user',
            ]);
        }
        if ($_groups->isNotEmpty()) {
            $_groups = $_groups->pluck('name', 'id')
                ->prepend('- Не указана -', '')
                ->toArray();
            $_field_groups = render_field('group_id', [
                'type'   => 'select',
                'label'  => 'Группа пользователя',
                'value'  => $entity->group_id,
                'values' => $_groups,
                'uikit'  => TRUE
            ]);
        }
        if ($entity->exists) {
            if ($_user->can('users_create', 'users_update') && $_user->hasRole('admin')) {
                $_field_blocked .= render_field('blocked', [
                    'type'     => 'checkbox',
                    'selected' => $entity->blocked,
                    'values'   => [
                        1 => 'Заблокировать аккаунт'
                    ],
                    'help'     => 'Заблокирует доступ к аккаунту пользователя. Аккаунт при этом не удаляется.',
                    'uikit'    => TRUE
                ]);
            }
        }
        $_form->tabs = [
            [
                'title'   => 'Основное',
                'content' => [
                    '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                    render_field('email', [
                        'type'       => 'email',
                        'label'      => 'E-mail',
                        'value'      => $entity->email,
                        'attributes' => [
                            'autofocus' => TRUE,
                        ],
                        'required'   => TRUE,
                        'uikit'      => TRUE
                    ]),
                    '</div><div>',
                    render_field('password', [
                        'type'       => 'password',
                        'label'      => 'Пароль',
                        'value'      => NULL,
                        'attributes' => [
                            'autocomplete' => 'new-password',
                        ],
                        'required'   => $entity->exists ? FALSE : TRUE,
                        'uikit'      => TRUE
                    ]),
                    '</div></div>',
                    '<hr class="uk-divider-icon">',
                    render_field('active', [
                        'type'     => 'checkbox',
                        'values'   => [
                            1 => 'Активировать аккаунт'
                        ],
                        'selected' => $entity->email_verified_at ? 1 : 0,
                        'help'     => 'Активация аккаунта после подтвержения email.',
                        'uikit'    => TRUE
                    ]),
                    $_field_blocked
                ],
            ],
            [
                'title'   => 'Профиль',
                'content' => [
                    '<div class="uk-grid uk-grid-small uk-form-column">',
                    '<div class="uk-width-1-3">',
                    render_field('avatar_fid', [
                        'type'   => 'file_drop',
                        'label'  => 'Аватарка',
                        'allow'  => 'jpg|jpeg|gif|png|svg',
                        'view'   => 'avatar',
                        'values' => $entity->avatar_fid ? [$entity->_avatar] : NULL,
                        'uikit'  => TRUE
                    ]),
                    '</div><div class="uk-width-2-3">',
                    '<div class="uk-grid uk-grid-small uk-child-width-1-3"><div>',
                    render_field('name', [
                        'label' => 'Имя',
                        'value' => $entity->name,
                        'uikit' => TRUE
                    ]),
                    '</div><div>',
                    render_field('surname', [
                        'label' => 'Фамилия',
                        'value' => $entity->surname,
                        'uikit' => TRUE
                    ]),
                    '</div><div>',
                    render_field('patronymic', [
                        'label' => 'Отчество',
                        'value' => $entity->patronymic,
                        'uikit' => TRUE,
                    ]),
                    '</div></div>',
                    $_field_roles,
                    $_field_groups,
                    render_field('phone', [
                        'label'      => 'Номер телефона',
                        'value'      => $entity->phone,
                        'attributes' => [
                            'class' => 'field-phone-mask'
                        ],
                        'uikit'      => TRUE
                    ]),
                    render_field('sex', [
                        'type'   => 'radio',
                        'label'  => 'Пол',
                        'value'  => $entity->sex ?? 0,
                        'values' => [
                            0 => 'Не указано',
                            1 => 'Мужчина',
                            2 => 'Женщина',
                        ],
                        'uikit'  => TRUE
                    ]),
                    render_field('birthday', [
                        'label'      => 'Дата рождения',
                        'value'      => $entity->birthday,
                        'attributes' => [
                            'data-position' => 'top left',
                            'class'         => 'field-datepicker',
                        ],
                        'uikit'      => TRUE
                    ]),
                    render_field('comment', [
                        'type'       => 'textarea',
                        'label'      => 'Коментарий',
                        'value'      => $entity->comment,
                        'attributes' => [
                            'rows' => 5,
                        ],
                        'uikit'      => TRUE
                    ]),
                    '</div></div>'
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
                'Аватар',
                render_image($item->_avatar, 'thumb_avatar')
            ],
            [
                'Фамилия',
                $item->surname,
            ],
            [
                'Имя',
                $item->name,
            ],
            [
                'Отчество',
                $item->patronymic,
            ],
            [
                'E-mail',
                $item->email,
            ],
            [
                'Подтвержденный e-mail',
                $item->email_verified_at,
            ],
            [
                'Номер телефона',
                $item->phone,
            ],
            [
                'Роль',
                $item->view_role,
            ],
            [
                'Пол',
                $item->view_sex,
            ],
            [
                'Дата рождения',
                $item->birthday,
            ],
            [
                'Коментарий',
                $item->comment,
            ],
            [
                'Зарегистрирован',
                $item->created_at,
            ],
            [
                'Заблокирован администратором',
                $item->blocked ? 'да' : 'нет',
            ]
        ];

        return $_view;
    }

    protected function _items($wrap)
    {
        $this->__filter();
        if ($this->filterClear) {
            return redirect()
                ->route("oleus.{$this->baseRoute}");
        }
        $_filter = $this->filter;
        $_query = User::from('users as u')
            ->leftJoin('model_has_roles as r', 'r.model_id', '=', 'u.id')
            ->orderByDesc('u.created_at')
            ->when($_filter, function ($query) use ($_filter) {
                if ($_filter['email']) {
                    $query->where('u.email', 'like', "%{$_filter['email']}%");
                }
                if ($_filter['phone']) {
                    $query->where('u.phone', 'like', "%{$_filter['phone']}%");
                }
                if ($_filter['blocked'] != 'all') {
                    $query->where('u.blocked', $_filter['blocked']);
                }
                if ($_filter['role'] != 'all') {
                    $query->where('r.role_id', $_filter['role']);
                }
                if ($_filter['group'] != 'all') {
                    $query->where('u.group_id', $_filter['group']);
                }
            })
            ->with([
                '_group'
            ])
            ->select([
                'u.id',
                'u.surname',
                'u.patronymic',
                'u.name',
                'u.email',
                'u.blocked',
                'u.group_id',
                'u.email_verified_at',
            ])
            ->paginate($this->entity->getPerPage(), ['u.id']);
        $_buttons = [];
        $_filters = [];
        if ($_query->isNotEmpty() && $this->__can_permission('users_export_data')) {
            $_buttons[] = _l('Экспорт пользователей', 'oleus.users.export', [
                'attributes' => [
                    'class' => 'uk-button uk-button-warning uk-button-small'
                ]
            ]);
        }
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
                'data' => 'ФИО',
            ],
            [
                'class' => 'uk-width-small',
                'data'  => 'E-mail',
            ],
            [
                'class' => 'uk-width-small',
                'data'  => 'Номер телефона',
            ],
            [
                'class' => 'uk-width-small',
                'data'  => 'Роль',
            ],
            [
                'class' => 'uk-width-small',
                'data'  => 'Группа',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: verified_user">',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: block_reverse">',
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
            array_unshift($_headers, [
                'class' => 'uk-text-center',
                'style' => 'width: 18px;',
                'data'  => "<input type='checkbox' name='items_all' class='uk-checkbox uk-margin-remove'>",
            ]);
        }
        if ($_query->isNotEmpty()) {
            $_query->getCollection()->transform(function ($item) {
                $_response = [
                    "<div class='uk-text-center uk-text-bold'>{$item->id}</div>",
                    $item->full_name,
                    $item->email,
                    $item->phone ?? '-',
                    $item->view_role,
                    $item->_group ? _l($item->_group->name, 'oleus.groups.show', [
                        'p'          => [$item->_group],
                        'attributes' => ['target' => '_blank']
                    ]) : '-',
                    $item->active ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                    $item->blocked ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
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
                    array_unshift($_response, "<input type='checkbox' name='items[{$item->id}]' class='uk-checkbox uk-margin-remove'>");
                }

                return $_response;
            });
            $_filters = [
                [
                    'class' => 'uk-width-large',
                    'data'  => render_field('email', [
                        'value'      => $_filter['email'] ?? NULL,
                        'attributes' => [
                            'placeholder' => 'E-mail',
                            'class'       => [
                                'uk-form-small'
                            ]
                        ],
                        'item_class' => [
                            'uk-margin-small-top uk-width-medium'
                        ],
                        'uikit'      => TRUE
                    ])
                ],
                [
                    'class' => 'uk-width-medium',
                    'data'  => render_field('phone', [
                        'value'      => $_filter['phone'] ?? NULL,
                        'attributes' => [
                            'placeholder' => 'Номер телефона',
                            'class'       => [
                                'uk-form-small'
                            ]
                        ],
                        'item_class' => [
                            'uk-margin-small-top uk-width-medium'
                        ],
                        'uikit'      => TRUE
                    ])
                ],
                [
                    'class' => 'uk-width-medium',
                    'data'  => render_field('blocked', [
                        'type'       => 'select',
                        'value'      => $_filter['blocked'] ?? 'all',
                        'values'     => [
                            'all' => 'Любой статус',
                            0     => 'Активен',
                            1     => 'Заблокирован',
                        ],
                        'attributes' => [
                            'class' => [
                                'uk-form-small'
                            ]
                        ],
                        'item_class' => [
                            'uk-margin-small-top uk-width-small'
                        ],
                        'uikit'      => TRUE
                    ])
                ],
                [
                    'class' => 'uk-width-medium',
                    'data'  => render_field('role', [
                        'type'       => 'select',
                        'value'      => $_filter['role'] ?? 'all',
                        'values'     => Role::all()
                            ->pluck('display_name', 'id')
                            ->prepend('Любая роль', 'all')
                            ->toArray(),
                        'attributes' => [
                            'class' => [
                                'uk-form-small'
                            ]
                        ],
                        'item_class' => [
                            'uk-margin-small-top uk-width-small'
                        ],
                        'uikit'      => TRUE
                    ])
                ],
                [
                    'class' => 'uk-width-medium',
                    'data'  => render_field('group', [
                        'type'       => 'select',
                        'value'      => $_filter['group'] ?? 'all',
                        'values'     => Group::all()
                            ->pluck('name', 'id')
                            ->prepend('Любая группа', 'all')
                            ->toArray(),
                        'attributes' => [
                            'class' => [
                                'uk-form-small'
                            ]
                        ],
                        'item_class' => [
                            'uk-margin-small-top uk-width-small'
                        ],
                        'uikit'      => TRUE
                    ])
                ]
            ];
        }
        $_items = $this->__items([
            'filters'     => $_filters,
            'use_filters' => $_filter ? TRUE : FALSE,
            'actions'     => [
                'publish'    => 'Опубликовать',
                'no_publish' => 'Снять с публикации',
                'delete'     => 'Удалить',
            ],
            'buttons'     => $_buttons,
            'headers'     => $_headers,
            'items'       => $_query,
        ]);
        $_wrap = $wrap;

        return view('backend.partials.items', compact('_items', '_wrap'));
    }

    public function store(Request $request)
    {
        if ($avatar_fid = $request->input('avatar_fid')) {
            $_avatar_fid = array_shift($avatar_fid);
            Session::flash('avatar_fid', json_encode([file_get($_avatar_fid['id'])]));
        }
        $this->validate($request, [
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required',
        ], [], [
            'email'    => 'E-mail',
            'password' => 'Пароль',
        ]);
        $_save = $request->only([
            'name',
            'surname',
            'patronymic',
            'password',
            'phone',
            'sex',
            'birthday',
            'comment',
            'email',
            'language',
            'blocked',
            'group_id',
        ]);
        $_save['password'] = bcrypt($_save['password']);
        $_save['email_verified_at'] = $request->input('active') ? Carbon::now() : NULL;
        $_save['avatar_fid'] = $_avatar_fid['id'] ?? NULL;
        $_item = $this->entity->create($_save);
        $_item->syncRoles($request->input('role'));
        Session::forget([
            'avatar_fid',
        ]);
        $_item->entityEventSave = FALSE;
        $_item->entityEventDelete = FALSE;

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, User $item)
    {
        if ($avatar_fid = $request->input('avatar_fid')) {
            $_avatar_fid = array_shift($avatar_fid);
            Session::flash('avatar_fid', json_encode([file_get($_avatar_fid['id'])]));
        }
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'role'  => 'required',
        ]);
        $_save = $request->only([
            'name',
            'surname',
            'patronymic',
            'phone',
            'sex',
            'birthday',
            'comment',
            'email',
            'language',
            'blocked',
            'group_id',
        ]);
        if ($_password = $request->input('password')) $_save['password'] = bcrypt($_password);
        $_save['email_verified_at'] = $request->input('active') ? Carbon::now() : NULL;
        $_save['avatar_fid'] = $_avatar_fid['id'] ?? NULL;
        $item->update($_save);
        $_old_role = $item->role;
        $_new_role = $request->input('role');
        if (($_old_role && $_old_role->name != $_new_role) || (!$_old_role && $_new_role)) {
            if ($_old_role) $item->removeRole($_old_role);
            $item->syncRoles($_new_role);
        }
        Session::forget([
            'avatar_fid',
        ]);

        return $this->__response_after_update($request, $item);
    }

    public function export(Request $request)
    {
        ini_set('memory_limit', '1024M');
        if ($this->__can_permission('users_export_data') == FALSE) abort(403);

        return Excel::download(new UsersExport(), 'export_users.xlsx');
    }
}
