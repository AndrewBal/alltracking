<?php

namespace App\Http\Controllers\Callbacks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShortCutController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $_response = [];
        $_shortcut = config('shortcut');
        if (count($_shortcut)) {
            foreach ($_shortcut as $_entity => $_data) {
                $_items = ($_data['model'])::orderBy($_data['primary'])
                    ->get([
                        $_data['primary'],
                        $_data['field']
                    ]);
                $_response[$_entity]['entity'] = $_data['title'];
                $_response[$_entity]['multiple'] = $_data['multiple'];
                switch ($_entity) {
                    default:
                        if ($_items->isNotEmpty()) {
                            $_items->each(function ($_item) use (&$_response, $_data, $_entity) {
                                $_response[$_entity]['items'][$_item->{$_data['primary']}] = $_item->{$_data['field']};
                            });
                        }
                        break;
                }
            }
        }

        return response()
            ->json($_response, 200);
    }
}
