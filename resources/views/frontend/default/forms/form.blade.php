@if($_item->prefix)
    {!! $_item->prefix !!}
@endif
<form method="post"
      enctype="multipart/form-data"
      action="{{ _r('ajax.submit_form', [$_item]) }}"
    {!! render_attributes($_form_data->attributes) !!}>
    {!! csrf_field() !!}
    {!! method_field('POST') !!}
    <input type="hidden"
           name="form-index"
           value="{{ $_item->renderIndex }}">
    <input type="hidden"
           name="form"
           value="{{ $_form_data->form_id }}">
    @if(isset($_accessEdit['form']) && $_accessEdit['form'])
        <div class="edit-box uk-position-top-right uk-position-z-index uk-margin-small-top uk-margin-small-right uk-text-small">
            @if($_locale == DEFAULT_LOCALE)
                @l('Редактировать', 'oleus.forms.edit', ['p' => [$_item], 'attributes' => ['target' => '_blank']])
            @else
                @l('Редактировать', 'oleus.forms.translate', ['p' => [$_item, $_locale], 'attributes' => ['target' => '_blank']])
            @endif
        </div>
    @endif
    @if(!$_item->hidden_title)
        <h2 class="form-title uk-text-uppercase uk-heading-bullet uk-margin-remove-top">
            {!! $_item->title !!}
        </h2>
        @if($_item->sub_title)
            <div class="form-sub-title uk-text-muted">
                {!! $_item->sub_title !!}
            </div>
        @endif
    @endif
    <div class="form-field-items">
        @foreach($_form_data->render_fields as $_field)
            {!! $_field !!}
        @endforeach
    </div>
    <div class="form-actions">
        <button type="submit"
                class="{{ $_item->settings->button_send->class ?? NULL }}"
                value="1"
                name="send_form">
            {{ $_item->button_send ?: trans('forms.constructor_form.buttons.submit') }}
        </button>
    </div>
    @if($_item->body)
        <div class="form-content">
            {!! $_item->body !!}
        </div>
    @endif
</form>
@if($_item->suffix)
    {!! $_item->suffix !!}
@endif
