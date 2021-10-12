@extends('backend.index')

@section('content')
    <article class="uk-article">
        <form class="uk-form uk-width-1-1{{ $_form->class ? " {$_form->class}" : NULL }}"
              method="POST"
              enctype="multipart/form-data"
              action="{{ $_item->exists ? _r("oleus.{$_form->route_tag}.update", [$_item]) : _r("oleus.{$_form->route_tag}.store") }}">
            {{ csrf_field() }}
            {{ $_item->exists ? method_field('PUT') : method_field('POST') }}
            <div class="uk-card uk-card-default uk-card-small uk-border-rounded">
                <div class="uk-card-header uk-text-right">
                    @if($_item->exists)
                        @can($_form->permission['update'])
                            <button type="submit"
                                    name="save"
                                    value="1"
                                    class="uk-button uk-button-success uk-button-small uk-margin-xsmall-right uk-text-uppercase">
                                Сохранить
                            </button>
                        @endcan
                    @else
                        @can($_form->permission['create'])
                            <button type="submit"
                                    name="save"
                                    value="1"
                                    class="uk-button uk-button-success uk-button-small uk-margin-xsmall-right uk-text-uppercase">
                                Добавить
                            </button>
                            <button type="submit"
                                    name="save_and_create"
                                    value="1"
                                    class="uk-button uk-button-primary uk-button-small uk-margin-xsmall-right uk-text-uppercase">
                                Добавить и создать новый
                            </button>
                        @endcan
                    @endif
                    @if($_item->exists)
                        @can($_form->permission['update'])
                            <button type="submit"
                                    name="save_close"
                                    value="1"
                                    class="uk-button uk-button-primary uk-button-small uk-margin-xsmall-right uk-text-uppercase">
                                Сохранить и закрыть
                            </button>
                        @endcan
                        @can($_form->permission['delete'])
                            <button type="button"
                                    name="delete"
                                    href="#toggle-animation"
                                    value="1"
                                    uk-icon="icon: delete_forever"
                                    uk-toggle="target: #form-delete-{{ $_item->id }}-box; animation: uk-animation-fade"
                                    class="uk-button uk-button-danger uk-button-icon uk-button-small uk-margin-xsmall-right uk-text-uppercase">
                            </button>
                        @endcan
                    @endif
                    @if($_form->buttons)
                        @foreach($_form->buttons as $_button)
                            {!! $_button !!}
                        @endforeach
                    @endif
                    @can($_form->permission['translate'])
                        @l('', "oleus.{$_form->route_tag}.edit", ['p' => [$_item], 'attributes' => ['class' => 'uk-button uk-button-default uk-button-small uk-button-icon', 'uk-icon' => 'icon: reply']])
                    @else
                        @l('', "oleus.{$_form->route_tag}", ['attributes' => ['class' => 'uk-button uk-button-default uk-button-icon uk-button-small', 'uk-icon' => 'icon: reply']])
                    @endcan
                </div>
                <div class="uk-card-body">
                    @if($_item->exists)
                        @can($_form->permission['delete'])
                            <div id="form-delete-{{ $_item->id }}-box"
                                 hidden
                                 style="background: #f8fafc;"
                                 class="uk-margin-bottom uk-border-rounded uk-padding-small uk-text-center uk-text-danger uk-box-shadow-small-inset uk-border-danger">
                                Вы уверены, что хотите удалить элемент?
                                <a href="javascript:void(0);"
                                   data-item="{{ $_item->id }}"
                                   class="uk-button uk-button-danger uk-button-small uk-text-uppercase uk-margin-small-left uk-button-delete-entity">Да</a>
                                <a href="javascript:void(0);"
                                   data-item="{{ $_item->id }}"
                                   uk-toggle="target: #form-delete-{{ $_item->id }}-box; animation: uk-animation-fade"
                                   class="uk-button uk-button-primary uk-button-small uk-text-uppercase uk-margin-small-left uk-button-delete-action">Нет</a>
                            </div>
                        @endcan
                    @endif
                    @if($errors->any())
                        <div class="uk-alert uk-alert-danger">
                            <ul class="uk-list">
                                @foreach ($errors->all() as $_error)
                                    <li class="uk-margin-remove">{!! $_error !!}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if($_form->tabs)
                        <div class="uk-grid-match"
                             uk-grid>
                            <div class="uk-width-1-4">
                                <ul class="uk-tab uk-tab-left"
                                    uk-tab="connect: #uk-tab-body; animation: uk-animation-fade; swiping: false;">
                                    @foreach($_form->tabs as $tab)
                                        @if($tab)
                                            <li class="{{ $loop->index == 0 ? 'uk-active' : '' }}">
                                                <a href="#">{!! $tab['title'] !!}</a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            <div class="uk-width-3-4">
                                <ul id="uk-tab-body"
                                    class="uk-switcher uk-margin">
                                    @foreach($_form->tabs as $tab)
                                        @if($tab)
                                            <li class="{{ $loop->index == 0 ? 'uk-active' : '' }}">
                                                @foreach($tab['content'] as $content)
                                                    {!! $content !!}
                                                @endforeach
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @elseif($_form->contents)
                        @foreach($_form->contents as $content)
                            @if($content)
                                {!! $content !!}
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </form>
        @if($_item->exists && $_form->permission['delete'])
            @can($_form->permission['delete'])
                <form action="{{ _r("oleus.{$_form->route_tag}.destroy", [$_item]) }}"
                      id="form-delete-{{ $_item->id }}-object"
                      method="POST">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                </form>
            @endcan
        @endif
    </article>
@endsection
