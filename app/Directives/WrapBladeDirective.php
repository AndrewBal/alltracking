<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;
use Illuminate\Support\Arr;

class WrapBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'wrap';
    }

    public function openingHandler($expression)
    {
        global $wrap;
        $_key = str_replace([
            '"',
            '\''
        ], '', $expression);
        if ($_key && is_array($wrap) && $wrap) {
            return Arr::get($wrap, $_key);
        }

        return NULL;
    }

    public function closingTag()
    {
    }

    public function closingHandler($expression)
    {
    }

    public function alternatingTag()
    {
    }

    public function alternatingHandler($expression)
    {
    }
}
