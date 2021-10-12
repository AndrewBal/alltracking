<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class VariableBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'variable';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_variable({$expression}) ?>";
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
