<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class SliderBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'slider';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_slider({$expression}) ?>";
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
