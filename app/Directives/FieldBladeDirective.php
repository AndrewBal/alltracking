<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class FieldBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'field';
    }

    public function openingHandler($expression)
    {
        return "<?php echo field_render({$expression}) ?>";
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
