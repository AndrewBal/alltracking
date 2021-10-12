<ul {!! render_attributes($_item->styleAttributes) !!}>
    <a class="uk-navbar-toggle " uk-navbar-toggle-icon href="#"></a>
    <div  uk-dropdown="pos: top-right" class="uk-navbar-dropdown">
        <ul class="uk-nav uk-navbar-dropdown-nav">
            @foreach($_item->menu_items as $_menu_item)
                {!! render_menu_item($_menu_item) !!}
            @endforeach
        </ul>
    </div>
</ul>







