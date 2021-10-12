<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class MenuBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'menu';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_menu({$expression}) ?>";
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
