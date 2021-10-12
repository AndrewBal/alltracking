<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class MobileBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'mobile';
    }

    public function openingHandler($expression)
    {
        return "<?php if (app('device')->isMobile() && !app('device')->isTablet()) : ?>";
    }

    public function closingTag()
    {
        return 'endmobile';
    }

    public function closingHandler($expression)
    {
        return "<?php endif; ?>";
    }

    public function alternatingTag()
    {
        return 'elsemobile';
    }

    public function alternatingHandler($expression)
    {
        return "<?php else: ?>";
    }
}
