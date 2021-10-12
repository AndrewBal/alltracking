@if($_form->prefix)
    {!! $_form->prefix !!}
@endif
<form method="{{ $_form->method }}"
      enctype="multipart/form-data"
      id="{{ $_form->id }}"
      action="{{ $_form->action }}"
      class="{{ $_form->class }}{{ $_form->ajax ? ' use-ajax' : NULL }}">
    {!! csrf_field() !!}
    {!! method_field($_form->method) !!}
    <input type="hidden"
           name="form"
           value="{{ $_form->id }}">
    @if($_form->title)
        <h2 class="form-title">
            {!! $_form->title !!}
        </h2>
    @endif
    @foreach($_form->fields as $_field)
        {!! $_field !!}
    @endforeach
    @if($_form->body)
        <div class="form-description">
            {!! $_form->body !!}
        </div>
    @endif
    <div class="form-action">
        @if($_form->buttons)
            @foreach($_form->buttons as $_button)
                {!! $_button !!}
            @endforeach
        @else
            <button type="submit"
                    class="{{ $_form->button_submit_class ?? NULL }}"
                    value="1"
                    name="submit_form">
                {!! $_form->button_submit_text ?: trans('forms.buttons.submit') !!}
            </button>
        @endif
    </div>
</form>
@if($_form->suffix)
    {!! $_form->suffix !!}
@endif
