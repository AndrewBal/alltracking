<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Libraries\BaseController;
use App\Libraries\Dashboard;
use App\Models\Structure\Node;
use App\Models\Structure\Page;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->titles['index'] = 'Панель управления';
        $this->middleware([
            'permission:access_dashboard'
        ]);
    }

    public function index()
    {
        $_page = new Page();
        $_page->fill([
            'title'        => $this->titles['index'],
            'language'     => $_page->locale,
            'generate_url' => NULL
        ]);
        $_others = NULL;
        $_wrap = $this->render([
            'page.title'  => $this->titles['index'],
            'breadcrumbs' => render_breadcrumb([
                'entity' => $_page,
            ]),
        ]);

        return view('backend.dashboard', compact('_others', '_wrap'));
    }

    public function polygon()
    {

    }

    public function artisan($command, $target)
    {
        try {
            Artisan::call("{$target}:{$command}");
            $_notice = 'Команда выполнена.';
        } catch (\Throwable $exception) {
            report($exception);
            $_notice = 'Возникла ошибка выполнения скрипта.';
        }

        return redirect()
            ->back()
            ->with('notices', [
                [
                    'message' => $_notice,
                    'status'  => 'success'
                ]
            ]);
    }

    public function remove_permission()
    {
        $this->permissions = collect([
            'pharm_orders_import',
        ]);
        $this->permissions->each(function ($_permission) {
            Permission::findByName($_permission)
                ->delete();
        });
    }

    public function add_permission()
    {
        $this->permissions = collect([
            [
                'name'         => 'pharm_my_orders_read',
                'display_name' => 'Просмотр списка своих заказов',
                'guard_name'   => 'web',
            ]
        ]);
        $this->permissions->each(function ($_permission) {
            Permission::create($_permission);
        });
        $_role = Role::findByName('super_admin');
        $this->permissions->each(function ($_permission) use ($_role) {
            $_role->givePermissionTo($_permission['name']);
        });
    }
}
