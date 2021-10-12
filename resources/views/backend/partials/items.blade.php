@extends('backend.index')

@section('content')
    <article class="uk-article">
        @if($_items->before)
            <div class="uk-card uk-card-default uk-card-small uk-border-rounded uk-margin-medium-bottom">
                @if($_items->before['header'])
                    <div class="uk-card-header">
                        <h2 class="uk-text-uppercase">
                            {!! $_items->before['header'] !!}
                        </h2>
                    </div>
                @endif
                <div class="uk-card-body">
                    {!! $_items->before['body'] !!}
                </div>
                @if($_items->before['footer'])
                    <div class="uk-card-footer uk-text-right">
                        {!! $_items->before['footer'] !!}
                    </div>
                @endif
            </div>
        @endif
        <div class="uk-card uk-card-default uk-card-small uk-border-rounded uk-margin-medium-bottom">
            @if($_items->buttons || $_items->filters)
                <div class="uk-card-header">
                    <div class="uk-grid uk-grid-small">
                        <div class="uk-width-expand">
                            @if($_items->filters && ($_items->items->isNotEmpty() || ($_items->use_filters && $_items->items->isEmpty())))
                                <button uk-toggle="target: #items-filter"
                                        class="uk-button uk-button-primary uk-button-small uk-float-left uk-margin-xsmall-right"
                                        type="button">
                                    Фильтровать
                                </button>
                            @endif
                            @if($_items->actions && $_items->items->isNotEmpty())
                                <form action="">
                                    <div class="uk-grid uk-grid-xsmall">
                                        <div style="width: 150px;">
                                            <select name="action"
                                                    id="items-actions"
                                                    class="uk-select uk-form-small">
                                                <option value="no">Выбрать действие</option>
                                                @foreach($_items->actions as $_key => $_label)
                                                    <option value="{{ $_key }}">{{ $_label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <button type="submit"
                                                    class="uk-button uk-button-success uk-button-icon uk-button-xsmall"
                                                    name="save_action"
                                                    uk-icon="icon: check;">
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                        <div>
                            @foreach($_items->buttons as $_button)
                                {!! $_button !!}
                            @endforeach
                        </div>
                    </div>
                    @if($_items->filters)
                        <div id="items-filter"
                             class="uk-margin-small-top uk-border-rounded uk-box-shadow-small-inset uk-background-default" {{ $_items->use_filters ?: 'hidden' }}>
                            <form action=""
                                  method="get"
                                  class="uk-padding-small-left uk-padding-small-right">
                                <div class="uk-grid uk-grid-small uk-flex uk-flex-top">
                                    <div class="uk-width-expand uk-padding-small-bottom"
                                         style="border-right: 1px #e4e9f0 solid;">
                                        <div class="uk-grid uk-grid-small">
                                            @foreach($_items->filters as $_field)
                                                {!! $_field['data'] !!}
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="uk-width-auto">
                                        <div class="uk-button-group uk-padding-small-top">
                                            <button type="submit"
                                                    name="filter"
                                                    value="1"
                                                    class="uk-button uk-button-success uk-button-icon uk-button-small"
                                                    uk-icon="search"></button>
                                            <button type="submit"
                                                    name="clear"
                                                    value="1"
                                                    class="uk-button uk-button-danger uk-button-icon uk-button-small"
                                                    uk-icon="close"></button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
            <div class="uk-card-body uk-padding-remove-top">
                @if($_items->items->isNotEmpty())
                    <table
                        class="uk-table uk-table-bordered uk-table-xsmall uk-table-hover uk-table-middle uk-margin-remove">
                        <thead>
                            <tr>
                                @foreach($_items->headers as $_td)
                                    <th class="{{ $_td['class'] ?? NULL }}"
                                        style="{{ $_td['style'] ?? NULL }}">
                                        {!! $_td['data'] ?? NULL !!}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($_items->items as $_item)
                                <tr class="{{ $_item['class'] ?? NULL }}"
                                    {{ $_item['attributes'] ?? NULL }}
                                    id="{{ $_item['id'] ?? NULL }}">
                                    @foreach(($_item['data'] ?? $_item) as $_key => $_td)
                                        @if(is_string($_td) || is_null($_td))
                                            <td class="{{ $_items->headers[$_key]['class'] ?? NULL }}"
                                                style="{{ $_items->headers[$_key]['style'] ?? NULL }}">
                                                {!! $_td !!}
                                            </td>
                                        @else
                                            <td class="{{ $_items->headers[$_key]['class'] ?? NULL }} {{ $_td['class'] ?? NULL }}"
                                                id="{{ $_td['id'] ?? NULL }}"
                                                {{ $_td['attributes'] ?? NULL }}
                                                style="{{ $_items->headers[$_key]['style'] ?? NULL }} {{ $_td['style'] ?? NULL }}">
                                                {!! $_td['data'] !!}
                                            </td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="uk-clearfix">
                        {{ $_items->items->links('backend.partials.pagination') }}
                    </div>
                @else
                    <div class="uk-alert uk-alert-warning uk-border-rounded uk-box-shadow-small-inset">
                        Список пуст
                    </div>
                @endif
            </div>
        </div>
        @if($_items->after)
            <div class="uk-card uk-card-default uk-card-small uk-border-rounded uk-margin-medium-bottom">
                @if($_items->after['header'])
                    <div class="uk-card-header">
                        <h2 class="uk-text-uppercase">
                            {!! $_items->after['header'] !!}
                        </h2>
                    </div>
                @endif
                <div class="uk-card-body">
                    {!! $_items->after['body'] !!}
                </div>
                @if($_items->after['footer'])
                    <div class="uk-card-footer uk-text-right">
                        {!! $_items->after['footer'] !!}
                    </div>
                @endif
            </div>
        @endif
    </article>
@endsection
