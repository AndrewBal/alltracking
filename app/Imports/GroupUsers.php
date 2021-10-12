<?php

namespace App\Imports;

use App\Models\User\Group;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class GroupUsers implements ToCollection, WithCalculatedFormulas
{
    protected $group;

    public function __construct(Group $group)
    {
        $this->group = $group;
    }

    public function collection(Collection $rows)
    {
        $_headings = $rows->shift();
        $_group = $this->group;
        $rows->map(function ($row) use ($_group, $_headings) {
            $_item = $_headings->combine($row);
            if ($_item->get('Group') == $_group->name) {
                DB::table('users')
                    ->where('id', $_item->get('#'))
                    ->update([
                        'group_id' => $_group->id
                    ]);
            }
        });
    }

}
