<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class ImageBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'image';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_image({$expression}) ?>";
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
