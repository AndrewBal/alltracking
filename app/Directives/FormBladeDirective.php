<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class FormBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'form';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_form({$expression}) ?>";
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
