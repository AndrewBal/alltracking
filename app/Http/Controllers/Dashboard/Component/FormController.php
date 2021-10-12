<?php

namespace App\Http\Controllers\Dashboard\Component;

use App\Libraries\BaseController;
use App\Libraries\Fields;
use App\Libraries\Form;
use App\Models\Components\MenuItems;
use App\Models\Form\FormFields;
use App\Models\Form\Forms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class FormController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware([
            'permission:forms_read'
        ]);
        $this->titles['index'] = 'Формы';
        $this->titles['create'] = 'Добавить форму';
        $this->titles['edit'] = 'Редактировать форму';
        $this->titles['translate'] = 'Перевод формы на :locale';
        $this->baseRoute = 'forms';
        $this->permissions = [
            'read'   => 'forms_read',
            'view'   => 'forms_view',
            'create' => 'forms_create',
            'update' => 'forms_update',
            'delete' => 'forms_delete',
        ];
        $this->entity = new Forms();
    }

    public function _form($entity)
    {
        $_form = $this->__form();
        $_form->route_tag = $this->baseRoute;
        $_form->permission = array_merge($_form->permission, $this->permissions);
        $_form->tabs = [
            [
                'title'   => 'Основные параметры',
                'content' => [
                    render_field('title', [
                        'label'    => 'Заголовок',
                        'value'    => $entity->title,
                        'required' => TRUE,
                        'uikit'    => TRUE
                    ]),
                    render_field('sub_title', [
                        'label' => 'Под заголовок',
                        'value' => $entity->sub_title,
                        'uikit' => TRUE
                    ]),
                    render_field('body', [
                        'label'      => 'Описание к форме',
                        'type'       => 'textarea',
                        'editor'     => TRUE,
                        'value'      => $entity->body,
                        'attributes' => [
                            'rows' => 8,
                        ],
                        'uikit'      => TRUE
                    ]),
                    '<hr class="uk-divider-icon">',
                    render_field('hidden_title', [
                        'type'     => 'checkbox',
                        'selected' => $entity->hidden_title,
                        'values'   => [
                            1 => 'Скрыть заголовок при выводе на страницу'
                        ],
                        'uikit'    => TRUE
                    ]),
                    render_field('status', [
                        'type'     => 'checkbox',
                        'name'     => 'status',
                        'selected' => $entity->exists ? $entity->status : 1,
                        'values'   => [
                            1 => 'опубликовано'
                        ],
                        'uikit'    => TRUE
                    ])
                ],
            ],
            [
                'title'   => 'Настройка формы',
                'content' => [
                    render_field('attributes', [
                        'label'      => 'Дополнительные аттрибуты формы',
                        'type'       => 'textarea',
                        'value'      => $entity->attributes,
                        'attributes' => [
                            'rows' => 3,
                        ],
                        'uikit'      => TRUE
                    ]),
                    '<h3 class="uk-heading-line"><span>Кнопки</span></h3>',
                    '<div uk-grid class="uk-grid-small uk-child-width-1-2">',
                    '<div>',
                    render_field('button_send', [
                        'label' => '"Отправить"',
                        'value' => $entity->button_send,
                        'help'  => 'Отправка данных формы.',
                        'uikit' => TRUE
                    ]),
                    render_field('settings.button_send.class', [
                        'label' => 'CLASS элемента',
                        'value' => $entity->settings->button_send->class ?? NULL,
                        'uikit' => TRUE
                    ]),
                    '</div><div>',
                    render_field('button_open_form', [
                        'label' => '"Открыть форму"',
                        'value' => $entity->button_open_form,
                        'help'  => 'Открыть форму в модальном окне.',
                        'uikit' => TRUE
                    ]),
                    render_field('settings.button_open_form.class', [
                        'label' => 'CLASS элемента',
                        'value' => $entity->settings->button_open_form->class ?? NULL,
                        'uikit' => TRUE
                    ]),
                    '</div></div>',
                    '<h3 class="uk-heading-line"><span>Завершение отправки</span></h3>',
                    render_field('completion_type', [
                        'type'   => 'radio',
                        'label'  => 'Действие после сохранение результата формы',
                        'value'  => $entity->exists ? $entity->completion_type : 1,
                        'values' => [
                            1 => 'Переход на страницу благодарности',
                            2 => 'Показать модальное окно',
                        ],
                        'uikit'  => TRUE
                    ]),
                    '<div uk-grid class="uk-grid-small">',
                    '<div class="uk-first-column uk-width-1-3">',
                    render_field('completion_page_id', [
                        'label' => 'ID страницы благодарности',
                        'value' => $entity->completion_page_id,
                        'help'  => 'Если не указано либо страница будет удалена, то будет перенаправление на главную страницу',
                        'uikit' => TRUE
                    ]),
                    '</div>',
                    '<div class="uk-width-2-3">',
                    render_field('completion_modal_text', [
                        'label'      => 'Сообщение в модальном окне',
                        'type'       => 'textarea',
                        'editor'     => TRUE,
                        'value'      => $entity->completion_modal_text,
                        'class'      => 'editor-short',
                        'attributes' => [
                            'rows' => 8,
                        ],
                        'uikit'      => TRUE
                    ]),
                    '</div></div>',
                    '<h3 class="uk-heading-line"><span>Рассылка писем</span></h3>',
                    render_field('email_to_receive', [
                        'label' => 'Email получателей письма',
                        'value' => $entity->email_to_receive,
                        'help'  => 'Список через запятую email получателей. Если поле оставить пустым, то рассылка провдиться не будет.',
                        'uikit' => TRUE
                    ]),
                    render_field('email_subject', [
                        'label' => 'Тема письма',
                        'value' => $entity->email_subject,
                        'uikit' => TRUE
                    ]),
                    render_field('user_email_field_id', [
                        'label' => 'ID поля в форме с email пользователя',
                        'value' => $entity->user_email_field_id,
                        'help'  => 'Указать ID поля из вкладки "Поля формы" в котором хранится email пользовател для отправки ему копии письма. Если оставить пустым письмо пользователю отправляться не будет.',
                        'uikit' => TRUE
                    ]),
                ]
            ]
        ];
        if ($entity->exists) {
            $_form->tabs[] = [
                'title'   => 'Поля формы',
                'content' => [
                    view('backend.partials.insert_items', [
                        'entity' => $entity,
                        'route'  => $this->baseRoute,
                        'items'  => $entity->items,
                        'button' => '<div class="uk-float-right"><button class="uk-button uk-button-small uk-button-success" type="button" aria-expanded="false">Добавить элемент</button><div id="form-fields-menu" uk-dropdown="mode: click; pos: bottom-right;" class="uk-dropdown uk-dropdown-top-right uk-padding-xsmall-top uk-padding-xsmall-bottom"><ul class="uk-nav uk-dropdown-nav uk-text-left uk-text-small"><li>' . _l('Текстовое поле', "oleus.{$this->baseRoute}.item", [
                                'p'          => [
                                    $entity,
                                    'add',
                                    'text'
                                ],
                                'attributes' => ['class' => 'use-ajax']
                            ]) . '</li><li>' . _l('Текстовая область', "oleus.{$this->baseRoute}.item", [
                                'p'          => [
                                    $entity,
                                    'add',
                                    'textarea'
                                ],
                                'attributes' => ['class' => 'use-ajax']
                            ]) . '</li><li>' . _l('Числовое поле', "oleus.{$this->baseRoute}.item", [
                                'p'          => [
                                    $entity,
                                    'add',
                                    'number'
                                ],
                                'attributes' => ['class' => 'use-ajax']
                            ]) . '</li><li>' . _l('Скрытое поле', "oleus.{$this->baseRoute}.item", [
                                'p'          => [
                                    $entity,
                                    'add',
                                    'hidden'
                                ],
                                'attributes' => ['class' => 'use-ajax']
                            ]) . '</li><li>' . _l('Элементы списка', "oleus.{$this->baseRoute}.item", [
                                'p'          => [
                                    $entity,
                                    'add',
                                    'select'
                                ],
                                'attributes' => ['class' => 'use-ajax']
                            ]) . '</li><li>' . _l('Флажки выбора', "oleus.{$this->baseRoute}.item", [
                                'p'          => [
                                    $entity,
                                    'add',
                                    'checkbox'
                                ],
                                'attributes' => ['class' => 'use-ajax']
                            ]) . '</li><li>' . _l('Переключатели', "oleus.{$this->baseRoute}.item", [
                                'p'          => [
                                    $entity,
                                    'add',
                                    'radio'
                                ],
                                'attributes' => ['class' => 'use-ajax']
                            ]) . '</li><li>' . _l('Разметка', "oleus.{$this->baseRoute}.item", [
                                'p'          => [
                                    $entity,
                                    'add',
                                    'markup'
                                ],
                                'attributes' => ['class' => 'use-ajax']
                            ]) . '</li><li>' . _l('Выбор файла', "oleus.{$this->baseRoute}.item", [
                                'p'          => [
                                    $entity,
                                    'add',
                                    'file'
                                ],
                                'attributes' => ['class' => 'use-ajax']
                            ]) . '</li></ul></div></div>',
                    ])
                        ->render(function ($view, $content) {
                            return clear_html($content);
                        })
                ]
            ];
        }
        $_form->tabs[] = $this->__form_tab_display_style($entity, 'prefix', 'suffix');
        $_form->tabs[] = $this->__form_tab_translate($entity, $this->baseRoute, 'title');

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
        $_form->tabs = [
            [
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
                ],
            ],
            [
                'title'   => 'Настройка формы',
                'content' => [
                    '<h3 class="uk-heading-line"><span>Кнопки</span></h3>',
                    '<div uk-grid class="uk-grid-divider uk-grid-small uk-child-width-1-2">',
                    '<div>',
                    render_field('button_send', [
                        'label' => '"Отправить"',
                        'value' => $entity->getTranslation('button_send', $entity->frontLocale, FALSE),
                        'uikit' => TRUE
                    ]),
                    '</div>',
                    '<div>',
                    render_field('button_open_form', [
                        'label' => '"Открыть форму"',
                        'value' => $entity->getTranslation('button_open_form', $entity->frontLocale, FALSE),
                        'uikit' => TRUE
                    ]),
                    '</div>',
                    '</div>',
                    '<h3 class="uk-heading-line"><span>Завершение отправки</span></h3>',
                    render_field('completion_modal_text', [
                        'label'      => 'Сообщение в модальном окне',
                        'type'       => 'textarea',
                        'editor'     => TRUE,
                        'value'      => $entity->getTranslation('completion_modal_text', $entity->frontLocale, FALSE),
                        'class'      => 'editor-short',
                        'attributes' => [
                            'rows' => 8,
                        ],
                        'uikit'      => TRUE
                    ]),
                ]
            ]
        ];

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
        $_query = Forms::orderBy('title')
            ->select([
                '*'
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
                'data' => 'Форма',
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
                    (string)$item->id,
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
        $_filters = [];
        $_items = $this->__items([
            'buttons'     => $_buttons,
            'headers'     => $_headers,
            'filters'     => $_filters,
            'use_filters' => $_filter ? TRUE : FALSE,
            'items'       => $_query,
        ]);

        return view('backend.partials.items', compact('_items', '_wrap'));
    }

    public function store(Request $request)
    {
        if ($background_fid = $request->input('background_fid')) {
            $_background_fid = array_shift($background_fid);
            Session::flash('background_fid', json_encode([f_get($_background_fid['id'])]));
        }
        $this->validate($request, [
            'title' => 'required',
        ], [], [
            'title' => 'Заголовок'
        ]);
        $_save = $request->only([
            'title',
            'sub_title',
            'body',
            'attributes',
            'style_id',
            'style_class',
            'prefix',
            'suffix',
            'status',
            'hidden_title',
            'completion_type',
            'completion_modal_text',
            'completion_page_id',
            'button_send',
            'button_open_form',
            'email_to_receive',
            'email_subject',
            'attributes',
            'settings',
        ]);
        $_save['background_fid'] = $_background_fid['id'] ?? NULL;
        $_item = Forms::create($_save);
        Session::forget([
            'background_fid'
        ]);

        return $this->__response_after_store($request, $_item);
    }

    public function update(Request $request, Forms $item)
    {
        if ($request->has('translate')) {
            $_locale = $request->get('locale', $this->defaultLocale);
            $item->frontLocale = $_locale;
            $this->validate($request, [
                'title' => 'required',
            ], [], [
                'title' => 'Заголовок',
            ]);
            $_save = $request->only([
                'title',
                'sub_title',
                'body',
                'button_send',
                'button_open_form',
                'completion_modal_text',
            ]);
            foreach ($_save as $_key => $_value) $item->setTranslation($_key, $_locale, $_value);
            $item->save();
        } else {
            if ($background_fid = $request->input('background_fid')) {
                $_background_fid = array_shift($background_fid);
                Session::flash('background_fid', json_encode([f_get($_background_fid['id'])]));
            }
            $this->validate($request, [
                'title'               => 'required',
                'completion_page_id'  => 'sometimes|nullable|exists_data:pages,id,' . $request->get('completion_page_id'),
                'user_email_field_id' => 'sometimes|nullable|exists_data:form_fields,id,' . $request->get('user_email_field_id')
            ], [], [
                'title' => 'Заголовок',
            ]);
            $_save = $request->only([
                'title',
                'sub_title',
                'body',
                'attributes',
                'style_id',
                'style_class',
                'prefix',
                'suffix',
                'button_send',
                'button_open_form',
                'completion_type',
                'completion_page_id',
                'completion_modal_text',
                'settings',
                'email_subject',
                'email_to_receive',
                'user_email_field_id',
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

    public function field(Request $request, Forms $entity, $action, $key = NULL)
    {
        $commands = [];
        switch ($action) {
            case 'add':
            case 'edit':
                if (is_numeric($key)) {
                    $_item = FormFields::find($key);
                } elseif (is_string($key)) {
                    $_item = new FormFields();
                    $_item->type = $key;
                }
                $_form = new Form([
                    'id'     => 'form-items-form',
                    'class'  => 'uk-form',
                    'title'  => $_item->exists ? 'Редактирование пункта меню' : 'Добавление пункта меню',
                    'action' => _r('oleus.forms.item', [
                        $entity,
                        'save',
                        is_numeric($key) ? $key : NULL
                    ]),
                    'tabs'   => TRUE,
                    'prefix' => '<div class="uk-modal-body uk-padding-small"><button class="uk-modal-close-outside" type="button" uk-close></button>',
                    'suffix' => '</div>',
                ]);
                $_locale = DEFAULT_LOCALE;
                $_tabs = FormFields::getFormField($entity, $_item, $_locale);
                $_form->setAjax();
                $_form->setFields($_tabs);
                $_form->setButtonSubmitText('Сохранить');
                $_form->setButtonSubmitClass('uk-button uk-button-success');
                $commands['commands'][] = [
                    'command' => 'removeClass',
                    'options' => [
                        'target' => '#forms-field-menu',
                        'data'   => 'uk-open'
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'eval',
                    'options' => [
                        'data' => 'UIkit.dropdown("#form-fields-menu").hide();'
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'UK_modal',
                    'options' => [
                        'content'     => $_form->_render(),
                        'classDialog' => 'uk-width-1-2@l'
                    ]
                ];
                break;
            case 'save':
                $_field = $request->get('field');
                $_locale = DEFAULT_LOCALE;
                $validator = Validator::make($request->all(), [
                    "field.title.{$_locale}" => 'required',
                ], [], [
                    "field.title.{$_locale}" => 'Заголовок',
                ]);
                $commands['commands'][] = [
                    'command' => 'removeClass',
                    'options' => [
                        'target' => '#form-items-form *',
                        'data'   => 'form-field-error'
                    ]
                ];
                if ($validator->fails()) {
                    $_notification = '<ul class="uk-list uk-margin-remove">';
                    foreach ($validator->errors()->messages() as $_field => $message) {
                        $commands['commands'][] = [
                            'command' => 'addClass',
                            'options' => [
                                'target' => '#' . Fields::render_field_id($_field, 'form-items-form'),
                                'data'   => 'form-field-error'
                            ]
                        ];
                        $_notification .= "<li class='uk-margin-remove'>{$message[0]}</li>";
                    }
                    $_notification .= '</ul>';
                    $commands['commands'][] = [
                        'command' => 'UK_notification',
                        'options' => [
                            'status' => 'danger',
                            'text'   => $_notification
                        ]
                    ];
                } else {
                    $_item = FormFields::findOrNew($key);
                    $_item->fill($_field);
                    $_item->save();
                    $items = $entity->items;
                    $commands['commands'][] = [
                        'command' => 'html',
                        'options' => [
                            'target' => '#list-insert-items',
                            'data'   => view('backend.partials.insert_items_table', compact('items'))
                                ->render(function ($view, $content) {
                                    return clear_html($content);
                                })
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
                    $commands['commands'][] = [
                        'command' => 'removeClass',
                        'options' => [
                            'target' => '#uk-tab-body a.uk-button-save-sorting',
                            'data'   => 'uk-hidden'
                        ]
                    ];
                }
                break;
            case 'destroy':
                FormFields::find($key)
                    ->delete();
                $items = $entity->items;
                $commands['commands'][] = [
                    'command' => 'html',
                    'options' => [
                        'target' => '#list-insert-items',
                        'data'   => view('backend.partials.insert_items_table', compact('items'))
                            ->render(function ($view, $content) {
                                return clear_html($content);
                            })
                    ]
                ];
                $commands['commands'][] = [
                    'command' => 'UK_notification',
                    'options' => [
                        'text'   => 'Элемент удален',
                        'status' => 'success',
                    ]
                ];
                if ($items->isEmpty()) {
                    $commands['commands'][] = [
                        'command' => 'addClass',
                        'options' => [
                            'target' => '#uk-tab-body a.uk-button-save-sorting',
                            'data'   => 'uk-hidden'
                        ]
                    ];
                }
                break;
        }

        return response($commands, 200);
    }

    public function sort(Request $request, Forms $entity)
    {
        $_sorting = $request->all();
        foreach ($_sorting as $_id => $_sort) {
            if (is_numeric($_id)) {
                FormFields::where('id', $_id)
                    ->update([
                        'sort' => $_sort
                    ]);
            }
        }
        $items = $entity->items;
        $commands['commands'][] = [
            'command' => 'html',
            'options' => [
                'target' => '#list-insert-items',
                'data'   => view('backend.partials.insert_items_table', compact('items'))
                    ->render(function ($view, $content) {
                        return clear_html($content);
                    })
            ]
        ];
        $commands['commands'][] = [
            'command' => 'UK_notification',
            'options' => [
                'text'   => 'Сортировка сохранена',
                'status' => 'success',
            ]
        ];

        return response($commands, 200);
    }
}
