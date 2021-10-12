<?php

namespace App\Libraries;

use App\Http\Controllers\Controller;
use App\Models\Structure\Page;
use Barryvdh\Reflection\DocBlock\Tag\ExampleTag;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

abstract class BaseController extends Controller
{
    use Authorizable;
    use Dashboard;
    use Notifiable;

    public $tags;
    public $nodeTags;
    public $defaultLocale = 'en';

    public function __construct()
    {
        parent::__construct();
        $this->defaultLocale = DEFAULT_LOCALE;
        App::setLocale($this->defaultLocale);
    }

    public function __call($name, $arguments)
    {
        $_dashboard = new Page();
        $_dashboard->fill([
            'title'        => 'Панель управления',
            'generate_url' => _r('oleus')
        ]);
        switch ($name) {
            case 'index':
                if ($this->__can_permission() == FALSE) abort(403);
                $_page = new Page();
                $_page->fill([
                    'title' => $this->titles['index'],
                ]);
                $_wrap = $this->render([
                    'page.title'          => $_dashboard->title,
                    'page.callback_route' => $_dashboard->generate_url,
                    'seo.title'           => $_page->title,
                    'breadcrumbs'         => render_breadcrumb([
                        'parent' => [
                            $_dashboard
                        ],
                        'entity' => $_page,
                    ]),
                ]);

                return $this->_items($_wrap);
                break;
            case 'show':
                if ($this->__can_permission('view') == FALSE) abort(403);
                $_page = new Page();
                $_page->fill([
                    'title' => $this->titles['view'],
                    'generate_url' => NULL
                ]);
                $_parent = new Page();
                $_parent->fill([
                    'title'        => $this->titles['index'],
                    'generate_url' => _r("oleus.{$this->baseRoute}")
                ]);
                $_item = array_shift($arguments);
                $_view = $this->_view($_item);
                if (!$_view) {
                    return redirect()
                        ->route("oleus.{$this->baseRoute}")
                        ->with('notices', [
                            [
                                'message' => 'Не добавлено представление для просмотра',
                                'status'  => 'warning'
                            ]
                        ]);
                }
                $_wrap = $this->render([
                    'page.title'          => $this->titles['index'],
                    'page.callback_route' => $_parent->generate_url,
                    'seo.title'           => "{$_parent->title}. {$this->titles['view']}",
                    'breadcrumbs'         => render_breadcrumb([
                        'parent' => [
                            $_dashboard,
                            $_parent
                        ],
                        'entity' => $_page,
                    ]),
                ]);

                return view($_view->theme, compact('_view', '_item', '_wrap'));
                break;
            case 'create':
                if ($this->__can_permission('create') == FALSE) abort(403);
                $_page = new Page();
                $_page->fill([
                    'title' => $this->titles['create'],
                ]);
                $_parent = new Page();
                $_parent->fill([
                    'title'        => $this->titles['index'],
                    'generate_url' => _r("oleus.{$this->baseRoute}")
                ]);
                $_item = $this->entity;
                $_form = $this->_form($_item);
                $_wrap = $this->render([
                    'page.title'          => $this->titles['index'],
                    'page.callback_route' => $_parent->generate_url,
                    'seo.title'           => "{$_parent->title}. {$this->titles['create']}",
                    'breadcrumbs'         => render_breadcrumb([
                        'parent' => [
                            $_dashboard,
                            $_parent
                        ],
                        'entity' => $_page,
                    ]),
                ]);

                return view($_form->theme, compact('_form', '_item', '_wrap'));
                break;
            case 'edit':
//                try {
                    $_item = array_shift($arguments);
                    if ($this->__can_permission('edit') == FALSE) abort(403);
                    $_page = new Page();
                    $_page->fill([
                        'title' => $this->titles['edit']
                    ]);
                    $_parent = new Page();
                    $_parent->fill([
                        'title'        => $this->titles['index'],
                        'generate_url' => _r("oleus.{$this->baseRoute}")
                    ]);
                    $_form = $this->_form($_item);
                    $_wrap = $this->render([
                        'page.title'          => $this->titles['index'],
                        'page.callback_route' => $_parent->generate_url,
                        'seo.title'           => "{$_parent->title}. {$this->titles['edit']}",
                        'breadcrumbs'         => render_breadcrumb([
                            'parent' => [
                                $_dashboard,
                                $_parent
                            ],
                            'entity' => $_page,
                        ]),
                    ]);

                    return view($_form->theme, compact('_form', '_item', '_wrap'));
//                } catch (\Exception $e) {
//                    report($e);
//                }
                break;
            case 'translate':
//                try {
                    if ($this->__can_permission('edit') == FALSE) abort(403);
                    $_item = array_shift($arguments);
                    $_locale = array_shift($arguments);
                    $_item->frontLocale = $_locale;
                    $_locale = config("laravellocalization.supportedLocales.{$_locale}");
                    $_page = new Page();
                    $_page->fill([
                        'title'        => 'Оригинал',
                        'generate_url' => _r("oleus.{$this->baseRoute}.edit", [$_item])
                    ]);
                    $_translate = new Page();
                    $_translate->fill([
                        'title' => str_replace(':locale', '"' . mb_strtolower($_locale['native']) . '" язык', $this->titles['translate'])
                    ]);
                    $_parent = new Page();
                    $_parent->fill([
                        'title'        => $this->titles['index'],
                        'generate_url' => _r("oleus.{$this->baseRoute}")
                    ]);
                    if (is_null($_locale)) abort(404);
                    $_form = $this->_form_translate($_item);
                    $_wrap = $this->render([
                        'page.title'          => $this->titles['index'],
                        'page.callback_route' => $_parent->generate_url,
                        'seo.title'           => str_replace(':locale', '"' . mb_strtolower($_locale['native']) . '" язык', "{$_parent->title}. {$this->titles['translate']}"),
                        'breadcrumbs'         => render_breadcrumb([
                            'parent' => [
                                $_dashboard,
                                $_parent,
                                $_page
                            ],
                            'entity' => $_translate,
                        ]),
                    ]);

                    return view($_form->theme, compact('_form', '_item', '_wrap'));
//                } catch (\Exception $e) {
//                    report($e);
//                }
                break;
            case 'destroy':
                try {
                    $_item = array_shift($arguments);
                    if ($this->__can_permission('delete') == FALSE) abort(403);
                    $_item->delete();

                    return $this->__response_after_destroy(request(), $_item);
                } catch (\Exception $e) {
                    report($e);
                }
                break;
        }
    }

    protected function _items($wrap)
    {
    }

    protected function _view($entity)
    {
    }

    protected function _form($entity)
    {
    }

    protected function _form_translate($entity)
    {
    }
}
