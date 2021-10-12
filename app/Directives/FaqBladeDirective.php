<?php

namespace App\Directives;

use App\Libraries\BladeDirectiveInterface;

class FaqBladeDirective implements BladeDirectiveInterface
{
    public function openingTag()
    {
        return 'faq';
    }

    public function openingHandler($expression)
    {
        return "<?php echo render_faq({$expression}) ?>";
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
