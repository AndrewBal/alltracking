<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class LinkBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'l';
    }

    public function openingHandler($expression)
    {
        return "<?php echo _l({$expression}) ?>";
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
