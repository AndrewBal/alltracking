<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class TabletBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'tablet';
    }

    public function openingHandler($expression)
    {
        return "<?php if (app('device')->isTablet()) : ?>";
    }

    public function closingTag()
    {
        return 'endtablet';
    }

    public function closingHandler($expression)
    {
        return "<?php endif; ?>";
    }

    public function alternatingTag()
    {
        return 'elsetablet';
    }

    public function alternatingHandler($expression)
    {
        return "<?php else: ?>";
    }
}
