<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class GalleryBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'gallery';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_gallery({$expression}) ?>";
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
