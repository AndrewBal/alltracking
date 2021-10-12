<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class BannerBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'banner';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_banner({$expression}) ?>";
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
