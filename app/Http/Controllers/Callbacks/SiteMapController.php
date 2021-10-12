<?php

namespace App\Http\Controllers\Callbacks;

use App\Http\Controllers\Controller;
use App\Models\Seo\SiteMap;
use Illuminate\Http\Request;

class SiteMapController extends Controller
{
    public function generate(Request $request, $index = NULL)
    {
        return SiteMap::_renderXML($index);
    }
}
