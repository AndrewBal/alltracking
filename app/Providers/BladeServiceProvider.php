<?php

namespace App\Providers;

use App\Directives\AdvantageBladeDirective;
use App\Directives\BannerBladeDirective;
use App\Directives\BlockBladeDirective;
use App\Directives\DesktopBladeDirective;
use App\Directives\FaqBladeDirective;
use App\Directives\FieldBladeDirective;
use App\Directives\FormBladeDirective;
use App\Directives\GalleryBladeDirective;
use App\Directives\ImageBladeDirective;
use App\Directives\LinkBladeDirective;
use App\Directives\MenuBladeDirective;
use App\Directives\MobileBladeDirective;
use App\Directives\SliderBladeDirective;
use App\Directives\TabletBladeDirective;
use App\Directives\VariableBladeDirective;
use App\Directives\WrapBladeDirective;
use App\Libraries\BladeDirectiveInterface;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Blade;

class BladeServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->registerDirective(new WrapBladeDirective());
        $this->registerDirective(new LinkBladeDirective());
        $this->registerDirective(new DesktopBladeDirective());
        $this->registerDirective(new TabletBladeDirective());
        $this->registerDirective(new MobileBladeDirective());
        $this->registerDirective(new FieldBladeDirective());
        $this->registerDirective(new VariableBladeDirective());
        $this->registerDirective(new BlockBladeDirective());
        $this->registerDirective(new BannerBladeDirective());
        $this->registerDirective(new AdvantageBladeDirective());
        $this->registerDirective(new ImageBladeDirective());
        $this->registerDirective(new SliderBladeDirective());
        $this->registerDirective(new GalleryBladeDirective());
        $this->registerDirective(new MenuBladeDirective());
        $this->registerDirective(new FaqBladeDirective());
        $this->registerDirective(new FormBladeDirective());
    }

    private function registerDirective(BladeDirectiveInterface $directive)
    {
        $_opening_tag = $directive->openingTag();
        $_closing_tag = $directive->closingTag();
        $_alternating_tag = $directive->alternatingTag();
        if ($_opening_tag) {
            Blade::directive($_opening_tag, [
                $directive,
                'openingHandler'
            ]);
        }
        if ($_closing_tag) {
            Blade::directive($_closing_tag, [
                $directive,
                'closingHandler'
            ]);
        }
        if ($_alternating_tag) {
            Blade::directive($_alternating_tag, [
                $directive,
                'alternatingHandler'
            ]);
        }
    }
}
