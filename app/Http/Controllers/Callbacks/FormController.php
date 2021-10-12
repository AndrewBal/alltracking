<?php

namespace App\Http\Controllers\Callbacks;

use App\Libraries\BaseController;
use App\Libraries\Fields;
use App\Models\Form\Forms;
use App\Models\Form\FormsData;
use App\Models\Structure\Page;
use App\Notifications\FormNotification;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class FormController extends BaseController
{
    use Authorizable;
    use Notifiable;

    public function __construct()
    {
        parent::__construct();
    }

    public function submit_form(Request $request, Forms $form)
    {
        //        try {
        global $wrap;
        $_form_index = $request->get('form_index', NULL);
        $_form_data = $form->formatted_data($_form_index);
        $_form_validate_rules = NULL;
        $_valid = FALSE;
        $_save_data = NULL;
        $_form_validate_rules = $_form_data->validation;
        if (isset($_form_validate_rules) && count($_form_validate_rules)) {
            $_validate_rules = [
                'captcha' => 'reCaptcha'
            ];
            $_validate_field_title = [
                'captcha' => trans('forms.fields.reCaptcha')
            ];
            $_validate_field_id = [];
            $_validate_field_multiple = [];
            $_validate_message = '';
            foreach ($_form_validate_rules as $_field) {
                $_validate_rules[$_field['name']] = $_field['rule'];
                $_validate_field_title[$_field['name']] = $_field['title'];
                $_validate_field_id[$_field['name']] = $_field['id'];
                if ($_field['multiple']) $_validate_field_multiple[$_field['name']] = $_field['id'];
            }
            $_validator = Validator::make($request->all(), $_validate_rules, [], $_validate_field_title);
            $commands['commands'][] = [
                'command' => 'removeClass',
                'options' => [
                    'target' => "#{$_form_data->form_id} *",
                    'data'   => 'form-field-error'
                ]
            ];
            $commands['rules'] = $_validate_rules;
            if ($_validator->fails()) {
                foreach ($_validator->errors()->messages() as $_field => $_message) {
                    $_validate_message .= "<div>{$_message[0]}</div>";
                    if (isset($_validate_field_id[$_field])) {
                        $commands['commands'][] = [
                            'command' => 'addClass',
                            'options' => [
                                'target' => '#' . Fields::render_field_id($_field, $_form_data->form_id),
                                'data'   => 'form-field-error'
                            ]
                        ];
                    } elseif (count($_validate_field_multiple)) {
                        foreach ($_validate_field_multiple as $_field_name => $_field_id) {
                            if (Str::is($_field_name, $_field)) {
                                $commands['commands'][] = [
                                    'command' => 'addClass',
                                    'options' => [
                                        'target' => '#' . Fields::render_field_id($_field, $_form_data->form_id),
                                        'data'   => 'form-field-error'
                                    ]
                                ];
                            }
                        }
                    }
                }
                $commands['commands'][] = [
                    'command' => 'UK_notification',
                    'options' => [
                        'text'   => $_validate_message,
                        'status' => 'danger'
                    ]
                ];
            } else {
                $_valid = TRUE;
            }
        } else {
            $_valid = TRUE;
        }
        if ($_valid) {
            $_request_field = $request->only('fields');
            $_save_data = $_form_data->options_fields
                ->filter(function ($field) {
                    return $field['options']['type'] != 'markup';
                })
                ->map(function ($field, $_id) use ($_request_field, $request) {
                    $_request_data = $_request_field['fields'][$field['options']['field_name']] ?? NULL;
                    if ($_request_data) {
                        switch ($field['options']['type']) {
                            case 'checkbox':
                            case 'radio':
                            case 'select':
                                $_tmp_data = [];
                                if (is_array($_request_data)) {
                                    foreach ($_request_data as $_option_value) {
                                        $_val = isset($_option_value) && is_array($_option_value) ? array_shift($_option_value) : ($_option_value ?? NULL);
                                        if (isset($field['options']['values'][$_val]) && $_val) $_tmp_data[] = $field['options']['values'][$_val];
                                    }

                                    $_request_data = implode(', ', $_tmp_data);
                                } else {
                                    if (isset($field['options']['values'][$_request_data])) $_request_data = $field['options']['values'][$_request_data];
                                }
                                break;
                            case 'file':
                                $_tmp_data = NULL;
                                try {
                                    $_file = $request->file("fields.{$field['options']['field_name']}");
                                    $_base_url = config('app.url');
                                    if (is_array($_file)) {
                                        foreach ($_file as $_attach_file) {
                                            $_file_extension = $_attach_file->getClientOriginalExtension();
                                            $_attach_file_name = Str::slug(basename($_attach_file->getClientOriginalName(), ".{$_file_extension}")) . '-' . uniqid() . ".{$_file_extension}";
                                            Storage::disk('base')
                                                ->put("form_attach/{$_attach_file_name}", file_get_contents($_attach_file->getRealPath()));
                                            $_tmp_data[] = "<a href=\"{$_base_url}/form_attach/{$_attach_file_name}\" target=\"_blank\">{$_attach_file_name}</a>";
                                        }
                                    } else {
                                        $_file_extension = $_file->getClientOriginalExtension();
                                        $_attach_file_name = Str::slug(basename($_file->getClientOriginalName(), ".{$_file_extension}")) . '-' . uniqid() . ".{$_file_extension}";
                                        Storage::disk('base')
                                            ->put("form_attach/{$_attach_file_name}", file_get_contents($_file->getRealPath()));
                                        $_tmp_data[] = "<a href=\"{$_base_url}/form_attach/{$_attach_file_name}\" target=\"_blank\">{$_attach_file_name}</a>";
                                    }
                                    if (count($_tmp_data)) $_request_data = implode(', ', $_tmp_data);
                                } catch (\Throwable $exception) {
                                    report($exception);
                                }
                                break;
                        }
                    }

                    return [
                        'label' => $field['options']['field_label'],
                        'data'  => $_request_data
                    ];
                });
        }
        if ($_save_data) {
            $_save = [
                'user_id'      => Auth::check() ? Auth::user()->id : NULL,
                'form_id'      => $form->id,
                'data'         => $_save_data,
                'referer_path' => $request->headers->get('referer')
            ];
            $_item = FormsData::create($_save);
            try {
                if ($_emails = $_item->_form->email_to_receive) {
                    $_emails = explode(',', $_emails);
                    foreach ($_emails as &$_email) $_email = trim($_email);
                    //                    Notification::route('mail', $_emails)
                    //                        ->notify(new FormNotification($_item));
                    //                    $_item->update([
                    //                        'notified' => 1
                    //                    ]);
                }
            } catch (\Throwable $exception) {
                report($exception);
            }
            if ($form->completion_type == 1 && $form->completion_page_id) {
                $_thanks_page = Page::find($form->completion_page_id);
                $commands['commands'][] = [
                    'command' => 'redirect',
                    'options' => [
                        'url' => _u($_thanks_page->generate_url)
                    ]
                ];
            } elseif ($form->completion_type == 2 && $form->completion_modal_text) {
                $commands['commands'][] = [
                    'command' => 'UK_modal',
                    'options' => [
                        'content'     => "<div class=\"uk-modal-body\"><button class=\"uk-modal-close-outside\" type=\"button\" uk-close></button><div>{$form->completion_modal_text}</div></div>",
                        'id'          => 'message-ajax-modal',
                        'classDialog' => 'uk-margin-auto-vertical',
                        'classModal'  => 'uk-flex-top'
                    ]
                ];
            } else {
                $commands['commands'][] = [
                    'command' => 'UK_notification',
                    'options' => [
                        'text'   => 'Form data submitted.',
                        'status' => 'success'
                    ]
                ];
            }
            $commands['commands'][] = [
                'command' => 'clearForm',
                'options' => [
                    'target' => "#{$_form_data->form_id}",
                ]
            ];
        }
        //        } catch (\Throwable $exception) {
        //            report($exception);
        //            $commands['commands'][] = [
        //                'command' => 'UK_notification',
        //                'options' => [
        //                    'text'   => trans('notifications.an_error_has_occurred'),
        //                    'status' => 'danger',
        //                ]
        //            ];
        //        }

        return response($commands, 200);
    }

    public function open_form(Request $request, Forms $form)
    {
        try {
            $_options = $request->only([
                'index',
                'view'
            ]);
            $_form_render = $form->_render($_options);
            $commands['commands'][] = [
                'command' => 'UK_modal',
                'options' => [
                    'content' => "<div class=\"uk-modal-body\"><button class=\"uk-modal-close-outside\" type=\"button\" uk-close></button><div>{$_form_render}</div></div>",
                    'id'      => 'constructor-form-ajax-modal',
                ]
            ];
        } catch (\Throwable $exception) {
            report($exception);
            $commands['commands'][] = [
                'command' => 'UK_notification',
                'options' => [
                    'text'   => trans('forms.messages.constructor_form.error_open'),
                    'status' => 'danger',
                ]
            ];
        }

        return response($commands, 200);
    }
}
