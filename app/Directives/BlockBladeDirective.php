<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class BlockBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'block';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_block({$expression}) ?>";
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
