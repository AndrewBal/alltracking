<div class="uk-modal-body">
    <button class="uk-modal-close-outside"
            type="button"
            uk-close></button>
    <div class="message-body">
        {!! $message !!}
    </div>
    <div class="message-link-to-home">
        <a href="{{ _u(LaravelLocalization::getLocalizedURL($_locale, '/')) }}">
            @lang('frontend.links.to_home')
        </a>
    </div>
</div>




