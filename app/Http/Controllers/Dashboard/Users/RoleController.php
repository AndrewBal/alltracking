<?php

namespace App\Http\Controllers\Dashboard\Users;

use App\Libraries\BaseController;
use App\Models\User\Permission;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Роли';
        $this->titles['create'] = 'Создать роль';
        $this->titles['edit'] = 'Редактировать роль';
        $this->titles['translate'] = 'Перевод роли на :locale';
        $this->middleware([
            'permission:roles_read'
        ]);
        $this->permissions = [
            'read'   => 'roles_read',
            'view'   => 'roles_view',
            'create' => 'roles_create',
            'update' => 'roles_update',
            'delete' => 'roles_delete',
        ];
        $this->baseRoute = 'roles';
        $this->entity = new Role();
    }

    public function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_form->tabs = [
            [
                'title'   => 'Основное',
                'content' => [
                    '<div class="uk-grid uk-grid-small uk-child-width-1-2"><div>',
                    render_field('name', [
                        'label'      => 'Машинное имя',
                        'value'      => $entity->name,
                        'required'   => TRUE,
                        'attributes' => $entity->exists ? ['readonly' => TRUE] : ['autofocus' => TRUE],
                        'help'       => !$entity->exists ? 'При заполнении можно использовать символы латиского алфавита в нижнем регистре и знак подчеркивания.' : NULL,
                        'uikit'      => TRUE
                    ]),
                    '</div><div>',
                    render_field('display_name', [
                        'label'    => 'Название',
                        'value'    => $entity->display_name,
                        'required' => TRUE,
                        'uikit'    => TRUE
                    ]),
                    '</div></div>'
                ]
            ]
        ];
        if ($entity->exists) {
            $_permissions = Permission::orderBy('group')
                ->orderBy('group')
                ->orderBy('name')
                ->get([
                    'display_name',
                    'group',
                    'name'
                ])
                ->keyBy('name')
                ->transform(function ($_p) {
                    return $_p->group ? "<span class='uk-text-primary uk-text-bold'>{$_p->group}</span>. {$_p->display_name}" : $_p->display_name;
                })
                ->toArray();
            $_form->tabs[] = [
                'title'   => 'Доступные права',
                'content' => [
                    render_field('permissions', [
                        'label'    => 'Права доступа',
                        'type'     => 'checkbox',
                        'selected' => $entity->exists ? $entity->permissions->pluck('name')->toArray() : NULL,
                        'values'   => $_permissions,
                        'uikit'    => TRUE
                    ])
                ],
            ];
        }
        $_form->tabs[] = $this->__form_tab_translate($entity, $this->baseRoute, 'display_name');

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
                    render_field('display_name', [
                        'label'    => 'Название',
                        'value'    => $entity->getTranslation('display_name', $entity->frontLocale),
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
        $_permissions_view = NULL;
        $_view->route_tag = $this->baseRoute;
        $_permissions = $item->getAllPermissions();
        if ($_permissions->isNotEmpty()) {
            $_permissions->sortBy('group')
                ->groupBy('group')
                ->transform(function ($p, $g) use (&$_permissions_view) {
                    $_permissions = $p->sortBy('display_name')
                        ->pluck('display_name')
                        ->implode('<br>');
                    $_permissions_view .= "<div class='uk-grid uk-grid-small'>
                            <div class='uk-width-1-4 uk-text-bold uk-text-right'>{$g}</div>
                            <div class='uk-width-3-4'>{$_permissions}</div>
                        </div>";
                });
        }
        $_view->contents = [
            [
                'Машинное имя',
                $item->name,
            ],
            [
                'Название',
                $item->display_name,
            ],
            [
                '<h3 class="uk-heading-line uk-text-uppercase"><span>Права доступа</span></h3>' . $_permissions_view,
            ],
        ];

        return $_view;
    }

    protected function _items($wrap)
    {
        $_query = Role::with('permissions')
            ->select([
                'id',
                'display_name'
            ])
            ->with([
                'permissions'
            ])
            ->paginate($this->entity->getPerPage(), ['id']);
        $_buttons = [];
        if ($_query->isNotEmpty()) {
            $_query->getCollection()->transform(function ($_item) {
                $_response = [
                    "<div class='uk-text-center uk-text-bold'>{$_item->id}</div>",
                    $_item->display_name,
                    "<div class='uk-text-center uk-text-bold'>{$_item->permissions->count()}</div>",
                ];
                if ($this->__can_permission('view')) {
                    $_response[] = _l('', "oleus.{$this->baseRoute}.show", [
                        'p'          => [
                            $_item
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
                            $_item
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
        $_headers = [
            [
                'class' => 'uk-width-xsmall uk-text-center',
                'data'  => 'ID',
            ],
            [
                'data' => 'Название роли',
            ],
            [
                'class' => 'uk-width-small uk-text-center',
                'data'  => 'Кол-во разрешений',
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
        if ($this->__can_permission('create')) {
            $_buttons[] = _l('Добавить', "oleus.{$this->baseRoute}.create", [
                'attributes' => [
                    'class' => 'uk-button uk-button-success uk-button-small'
                ]
            ]);
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
            'name'         => 'required|regex:/^[a-z_]+$/|unique:roles|max:191',
            'display_name' => 'required|max:191',
        ], [], [
            'name'         => 'Машинное имя',
            'display_name' => 'Название'
        ]);
        $_save = $request->only([
            'name',
            'display_name',
        ]);
        $_save['guard_name'] = Role::$defaultGuardName;
        $_item = Role::create($_save);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Role $item)
    {
        if ($request->has('translate')) {
            $_locale = $request->get('locale', $this->defaultLocale);
            $this->validate($request, [
                'display_name' => 'required|max:191',
            ], [], [
                'display_name' => 'Название'
            ]);
            $_save = $request->only([
                'display_name',
            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            $this->validate($request, [
                'permissions'  => 'sometimes|array',
                'display_name' => 'required|max:191',
            ], [], [
                'permissions'  => 'Права доступа',
                'display_name' => 'Название'
            ]);
            $_save = $request->only([
                'display_name',
            ]);
            $item->update($_save);
            if ($permissions = $request->input('permissions')) {
                $_permissions = Permission::whereIn('name', $permissions)
                    ->get();
                $item->syncPermissions($_permissions);
            } else {
                $item->syncPermissions([]);
            }
        }

        return $this->__response_after_update($request, $item);
    }

    public function destroy(Request $request, Role $item)
    {
        if ($this->__can_permission('delete') == FALSE) abort(403);
        $_role_name = $item->name;
        $item->syncPermissions([]);
        $_role_users = User::role($item->name)->get([
            'id'
        ]);
        if ($_role_users->isNotEmpty()) {
            $_role_users->map(function ($user) use ($_role_name) {
                $user->removeRole($_role_name);
                $user->assignRole('user');
            });
        }
        $_item->delete();

        return $this->__response_after_destroy(request(), $_item);
    }
}
