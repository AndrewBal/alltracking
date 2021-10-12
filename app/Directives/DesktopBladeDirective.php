<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class DesktopBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'desktop';
    }

    public function openingHandler($expression)
    {
        return "<?php if (!app('device')->isMobile()) : ?>";
    }

    public function closingTag()
    {
        return 'enddesktop';
    }

    public function closingHandler($expression)
    {
        return "<?php endif; ?>";
    }

    public function alternatingTag()
    {
        return 'elsedesktop';
    }

    public function alternatingHandler($expression)
    {
        return "<?php else: ?>";
    }
}
