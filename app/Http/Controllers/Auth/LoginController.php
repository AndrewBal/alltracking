<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Libraries\Fields;
use App\Libraries\Form;
use App\Models\Structure\Page;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        $_page = new Page();
        $_page->fill([
            'title'        => trans('forms.titles.auth.login'),
            'language'     => $_page->locale,
            'generate_url' => _r('login')
        ]);
        $_page->setWrap([
            'seo.title'            => $_page->title,
            'seo.robots'           => 'noindex, nofollow',
            'page.title'           => $_page->title,
            'page.translate_links' => collect([]),
            'breadcrumbs'          => render_breadcrumb([
                'entity' => $_page
            ]),
        ]);
        $_body = NULL;
        $_form = new Form([
            'id'     => 'user-login-form',
            'class'  => 'uk-form',
            'title'  => trans('forms.titles.auth.login'),
            'action' => _r('login'),
            'body'   => $_body
        ]);
        $_form->setAjax();
        $_form->setFields([
            'login.email'    => [
                'required'   => TRUE,
                'attributes' => [
                    'placeholder'  => trans('forms.fields.auth.login.email'),
                    'autocomplete' => TRUE,
                ],
                'uikit'      => TRUE
            ],
            'login.password' => [
                'type'       => 'password',
                'attributes' => [
                    'autocomplete' => 'new-password',
                    'placeholder'  => trans('forms.fields.auth.login.password'),
                ],
                'required'   => TRUE,
                'uikit'      => TRUE
            ],
            'login.remember' => [
                'type'   => 'checkbox',
                'values' => [
                    1 => trans('forms.fields.auth.login.remember')
                ],
                'uikit'  => TRUE
            ]
        ]);
        $_form->setButtonSubmitText(trans('forms.buttons.auth.login'));
        $_form->setButtonSubmitClass('uk-button uk-button-success');

        return View::first([
            "frontend.{$_page->device_template}.auth.login",
            'frontend.default.auth.login'
        ], [
            'wrap'    => app('wrap')->render(),
            'content' => $_form->_render()
        ]);
    }

    public function login(Request $request)
    {
        $_wrap = app('wrap');
        $_locale = $_wrap->getLocale();
        if ($request->ajax()) {
            $_response = [
                'commands' => NULL,
            ];
            $_form = $request->get('form');
            $_response['commands'][] = [
                'command' => 'removeClass',
                'options' => [
                    'target' => "#{$_form} *",
                    'data'   => 'uk-form-danger'
                ]
            ];
            $_response['commands'][] = [
                'command' => 'val',
                'options' => [
                    'target' => "#{$_form} input[type='password']",
                    'data'   => ''
                ]
            ];
            $_validator = $this->validator($request->all());
            if ($_validator->fails()) {
                $_messages = NULL;
                foreach ($_validator->errors()->messages() as $_field => $_message) {
                    $_messages .= "<div>{$_message[0]}</div>";
                    $_response['commands'][] = [
                        'command' => 'addClass',
                        'options' => [
                            'target' => '#' . Fields::render_field_id($_field, $_form),
                            'data'   => 'uk-form-danger'
                        ]
                    ];
                }
                $_response['commands'][] = [
                    'command' => 'UK_notification',
                    'options' => [
                        'text'   => $_messages,
                        'status' => 'danger'
                    ]
                ];
            } else {
                if ($this->attemptLogin($request)) {
                    $_user = $request->user();
                    if ($_user->blocked) {
                        Auth::logout();
                        $_response['commands'][] = [
                            'command' => 'UK_notification',
                            'options' => [
                                'text'   => '<div>' . trans('forms.messages.auth.login.account_locked') . '</div>',
                                'status' => 'danger'
                            ]
                        ];
                    } else {
                        $_rollback_url = $this->redirectTo;
                        if ($_locale != DEFAULT_LOCALE) $_rollback_url = "/{$_locale}/{$_rollback_url}";
                        if ($_user->can('access_dashboard')) $_rollback_url = 'oleus';
                        $_response['commands'][] = [
                            'command' => 'clearForm',
                            'options' => [
                                'target' => "#{$_form}"
                            ]
                        ];
                        $_response['commands'][] = [
                            'command' => 'redirect',
                            'options' => [
                                'url' => $_rollback_url
                            ]
                        ];
                    }
                } else {
                    $_response['commands'][] = [
                        'command' => 'UK_notification',
                        'options' => [
                            'text'   => '<div>' . trans('forms.messages.auth.login.failed') . '</div>',
                            'status' => 'danger'
                        ]
                    ];
                }
            }

            return response($_response, 200);
        } else {
            $this->validator($request->all())
                ->validate();
            if (method_exists($this, 'hasTooManyLoginAttempts') &&
                $this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);

                return $this->sendLockoutResponse($request);
            }
            if ($this->attemptLogin($request)) {
                return $this->sendLoginResponse($request);
            }
            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse($request);
        }
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'login.email'    => 'required|string|email',
            'login.password' => 'required|string'
        ], [], [
            'login.email'    => trans('forms.fields.auth.login.email'),
            'login.password' => trans('forms.fields.auth.login.password'),
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt([
            'email'    => $request->input('login.email'),
            'password' => $request->input('login.password'),
        ], $request->filled('login.remember'));
    }
}
