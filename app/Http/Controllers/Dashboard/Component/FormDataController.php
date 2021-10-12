<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Models\Form\Forms;
use App\Models\Form\FormsData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormDataController extends BaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->middleware([
            'permission:forms_data_list_read'
        ]);
        $this->titles['index'] = 'Данные форм';
        $this->titles['create'] = 'Добавить форму';
        $this->titles['edit'] = 'Просмотр данных';
        $this->baseRoute = 'forms_data';
        $this->permissions = [
            'read'   => 'forms_data_list_read',
            'update' => 'forms_data_update',
            'delete' => 'forms_data_delete',
        ];
        $this->entity = new FormsData();
    }

    public function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_form->contents[] = '<dl class="uk-description-list">
<dt>Отправлено:</dt><dd>' . $entity->created_at->format('d.m.Y H:i') . '</dd>
<dt>Уведомлено по почте:</dt><dd>' . ($entity->notified ? '<span class="uk-text-success">Выполнено</span>' : '<span class="uk-text-danger">Не выполнено</span>') . '</dd>
<dt>URL страницы отправки:</dt><dd>' . ($entity->referer_path ? _l($entity->referer_path, $entity->referer_path, ['attributes' => ['target' => '_blank']]) : '-//-') . '</dd>';
        $_form->contents[] = '<hr class="uk-divider-icon">';
        foreach ($entity->data as $_field_id => $_data) {
            $_form->contents[] = '<dt>' . $_data->label . '</dt><dd>' . ($_data->data ? : '-//-') . '</dd>';
        }
        $_form->contents[] = '<hr class="uk-divider-icon">';
        $_form->contents[] = '<dt>Комментарий к данным формы:</dt><dd>';
        $_form->contents[] = render_field('comment', [
            'type'       => 'textarea',
            'value'      => $entity->comment,
            'attributes' => [
                'rows' => 5,
            ],
            'uikit'      => TRUE
        ]);
        $_form->contents[] = '</dd></dl>';

        return $_form;
    }

    protected function _items($_wrap)
    {
        $this->__filter();
        if ($this->filterClear) {
            return redirect()
                ->route("oleus.{$this->baseRoute}");
        }
        $_filter = $this->filter;
        $_items = collect([]);
        $_query = FormsData::with([
            '_form'
        ])
            ->when($_filter, function ($query) use ($_filter) {
                if ($_filter['form'] != 'all') $query->where('form_id', '=', $_filter['form']);
                if ($_filter['create_from']) $query->where('created_at', '>=', Carbon::parse($_filter['create_from'])->format('Y-m-d 00:00:00'));
                if ($_filter['create_to']) $query->where('created_at', '<=', Carbon::parse($_filter['create_to'])->format('Y-m-d 23:59:59'));
            })
            ->orderByDesc('created_at')
            ->select([
                '*'
            ])
            ->paginate($this->entity->getPerPage(), ['id']);
        $_buttons = [];
        $_headers = [
            [
                'class' => 'uk-width-xsmall uk-text-center',
                'data'  => 'ID',
            ],
            [
                'data' => 'Форма',
            ],
            [
                'style' => 'width: 120px;',
                'class' => 'uk-text-center',
                'data'  => '<span uk-icon="icon: timer">',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: mail_outline">',
            ],
            [
                'class' => 'uk-text-center',
                'style' => 'width: 34px;',
                'data'  => '<span uk-icon="icon: visibility">',
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
                    (string)$item->id,
                    _l($item->_form->title, 'oleus.forms.edit', [
                        'p'          => [$item->_form],
                        'attributes' => ['target' => '_blank']
                    ]),
                    $item->created_at->format('d.m.Y H:i'),
                    $item->notified ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
                    $item->status ? '<span class="uk-text-success" uk-icon="icon: done"></span>' : '<span class="uk-text-danger" uk-icon="icon: close"></span>',
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
        }
        $_all_forms = Forms::pluck('title', 'id');
        if ($_all_forms->isNotEmpty()) $_all_forms->prepend('Все формы', 'all');
        $_filters = [
            [
                'data' => render_field('form', [
                    'type'       => 'select',
                    'selected'   => $_filter['form'] ?? 'all',
                    'values'     => $_all_forms,
                    'uikit'      => TRUE,
                    'item_class' => [
                        'uk-margin-small-top uk-width-medium'
                    ],
                    'attributes' => [
                        'class' => [
                            'uk-form-small'
                        ]
                    ],
                ])
            ],
            [
                'class' => 'uk-width-small',
                'data'  => render_field('create_from', [
                    'value'      => $_filter['create_from'] ?? NULL,
                    'attributes' => [
                        'placeholder' => 'Дата с',
                        'class'       => 'field-datepicker uk-form-small'
                    ],
                    'uikit'      => TRUE,
                    'item_class' => [
                        'uk-margin-small-top uk-width-medium'
                    ],
                ])
            ],
            [
                'class' => 'uk-width-small',
                'data'  => render_field('create_to', [
                    'value'      => $_filter['create_to'] ?? NULL,
                    'attributes' => [
                        'placeholder' => 'Дата по',
                        'class'       => 'field-datepicker uk-form-small'
                    ],
                    'uikit'      => TRUE,
                    'item_class' => [
                        'uk-margin-small-top uk-width-medium'
                    ],
                ])
            ],
        ];
        $_items = $this->__items([
            'buttons'     => $_buttons,
            'headers'     => $_headers,
            'filters'     => $_filters,
            'use_filters' => $_filter ? TRUE : FALSE,
            'items'       => $_query,
        ]);

        return view('backend.partials.items', compact('_items', '_wrap'));
    }

    public function update(Request $request, FormsData $_item)
    {
        $_item->update($request->only([
            'comment',
        ]));

        return $this->__response_after_update($request, $_item);
    }
}
