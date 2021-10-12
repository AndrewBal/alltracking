<?php

namespace App\Http\Controllers\Callbacks;

use App\Libraries\BaseController;
use App\Libraries\Form;
use App\Models\Components\File;
use App\Models\Components\FaqItems;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;

class FileController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $_file = $request->file('file');
            $_entity = [
                'file_base_name' => $_file->getClientOriginalName(),
                'file_name'      => NULL,
                'file_mime'      => NULL,
                'file_size'      => $_file->getSize(),
            ];
            $_item = File::where('file_base_name', $_entity['file_base_name'])
                ->where('file_size', $_entity['file_size'])
                ->first();
            if (is_null($_item)) {
                $_file_extension = $_file->getClientOriginalExtension();
                $_file_base_name = Str::lower(Str::slug(basename($_entity['file_base_name'], ".{$_file_extension}")));
                $_entity['file_name'] = render_unique_value([
                    $_file_base_name,
                    ".{$_file_extension}"
                ], 'files_managed', 'file_name');
                $_entity['file_mime'] = $_file->getClientMimeType();
                Storage::disk('public')
                    ->put($_entity['file_name'], file_get_contents($_file->getRealPath()));
                $_item = File::create($_entity);
            } else {
                $_item = $_item->replicate();
                $_item->save();
            }
            $_file_render = clear_html(render_preview_file($_item, $request->only([
                'field',
                'view'
            ])));

            return response($_file_render, 200);
        } else {
            return response(trans('notice.field_upload_not_upload'), 500);
        }
    }

    public function update(Request $request, File $file)
    {
        if ($request->has('save')) {
            $file->update($request->get('file'));
            $_commands['commands'][] = [
                'command' => 'UK_notification',
                'options' => [
                    'text'   => 'Элемент сохранен',
                    'status' => 'success',
                ]
            ];
            $_commands['commands'][] = [
                'command' => 'UK_modalClose',
                'options' => []
            ];
        } else {
            $_locale = DEFAULT_LOCALE;
            $_tabs = [
                [
                    'title'   => 'По умолчанию',
                    'content' => [
                        render_field('save', [
                            'type'  => 'hidden',
                            'value' => 1,
                        ]),
                        render_field("file.title.{$_locale}", [
                            'label'      => 'Атрибут TITLE',
                            'type'       => 'textarea',
                            'value'      => $file->getTranslation('title', $_locale),
                            'attributes' => [
                                'rows' => 3,
                            ],
                            'uikit'      => TRUE
                        ]),
                        render_field("file.alt.{$_locale}", [
                            'label'      => 'Атрибут ALT',
                            'type'       => 'textarea',
                            'value'      => $file->getTranslation('alt', $_locale),
                            'attributes' => [
                                'rows' => 3,
                            ],
                            'uikit'      => TRUE
                        ]),
                        render_field('file.sort', [
                            'type'       => 'number',
                            'label'      => 'Порядок сортировки',
                            'value'      => $file->sort ?? 0,
                            'uikit'      => TRUE,
                            'attributes' => [
                                'step' => 1
                            ]
                        ]),
                    ]
                ]
            ];
            if (USE_MULTI_LANGUAGE) {
                $_languages = config('laravellocalization.supportedLocales');
                foreach ($_languages as $_locale => $_data) {
                    if ($_locale != DEFAULT_LOCALE) {
                        $_tabs[] = [
                            'title'   => $_data['native'],
                            'content' => [
                                render_field("file.title.{$_locale}", [
                                    'label'      => 'Атрибут TITLE',
                                    'type'       => 'textarea',
                                    'value'      => $file->getTranslation('title', $_locale, FALSE),
                                    'attributes' => [
                                        'rows' => 3,
                                    ],
                                    'uikit'      => TRUE
                                ]),
                                render_field("file.alt.{$_locale}", [
                                    'label'      => 'Атрибут ALT',
                                    'type'       => 'textarea',
                                    'value'      => $file->getTranslation('alt', $_locale, FALSE),
                                    'attributes' => [
                                        'rows' => 3,
                                    ],
                                    'uikit'      => TRUE
                                ]),
                            ]
                        ];
                    } else {
                        $_tabs[0]['title'] = "По умолчанию ({$_data['native']})";
                    }
                }
            }
            $_form = new Form([
                'id'     => 'file-update-data-form',
                'class'  => 'uk-form',
                'title'  => 'Дополнительные параметры',
                'action' => _r('ajax.file.update', [$file]),
                'tabs'   => TRUE,
                'prefix' => '<div class="uk-modal-body uk-padding-small"><button class="uk-modal-close-outside" type="button" uk-close></button>',
                'suffix' => '</div>',
            ]);
            $_form->setAjax();
            $_form->setFields($_tabs);
            $_form->setButtonSubmitText('Сохранить');
            $_form->setButtonSubmitClass('uk-button uk-button-success');
            $_commands['commands'][] = [
                'command' => 'UK_modal',
                'options' => [
                    'content' => $_form->_render()
                ]
            ];
        }

        return response($_commands, 200);
    }

    public function sort(Request $request)
    {
        $_sorting = $request->all();
        foreach ($_sorting as $_id => $_sort) {
            if (is_numeric($_id)) {
                File::where('id', $_id)
                    ->update([
                        'sort' => $_sort
                    ]);
            }
        }
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
