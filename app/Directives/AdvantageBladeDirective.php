<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class AdvantageBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'advantage';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_advantage({$expression}) ?>";
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
