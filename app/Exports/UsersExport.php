<?php

namespace App\Exports;

use App\Models\User\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $group = FALSE;

    public function __construct($group = FALSE)
    {
        $this->group = TRUE;
    }

    public function collection()
    {
        if ($this->group) {
            return DB::table('users as u')
                ->leftJoin('user_groups as g', 'g.id', '=', 'u.group_id')
                ->select([
                    'u.id',
                    'u.name',
                    'u.surname',
                    'u.patronymic',
                    'u.email',
                    'u.phone',
                    'g.name as group_name',
                ])
                ->get();
        } else {
            return DB::table('users as u')
                ->leftJoin('model_has_roles as ru', 'ru.model_id', '=', 'u.id')
                ->leftJoin('roles as r', 'r.id', '=', 'ru.role_id')
                ->leftJoin('user_groups as g', 'g.id', '=', 'u.group_id')
                ->where('ru.model_type', User::class)
                ->select([
                    'u.id',
                    'u.name',
                    'u.surname',
                    'u.patronymic',
                    'u.email',
                    'u.phone',
                    DB::raw("(CASE `u`.`sex` WHEN 1 THEN 'Male' WHEN 2 THEN 'Female' WHEN 0 THEN '' END) as sex"),
                    'u.birthday',
                    'r.display_name as role_name',
                    'g.name as group_name',
                ])
                ->get();
        }
    }

    public function headings(): array
    {
        if ($this->group) {
            return [
                '#',
                'Name',
                'Surname',
                'Patronymic',
                'Email',
                'Phone',
                'Group',
            ];
        } else {
            return [
                '#',
                'Name',
                'Surname',
                'Patronymic',
                'Email',
                'Phone',
                'Sex',
                'Birthday',
                'Role',
                'Group',
            ];
        }
    }
}
