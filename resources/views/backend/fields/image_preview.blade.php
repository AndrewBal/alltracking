<div class="file-img{{ $options['view'] == 'gallery' ? ' sort-item' : NULL }}"
     data-id="{{ $file->id }}">
    @if($options['view'] == 'gallery')
        <div style="border: 1px solid #e5e5e5; padding: 5px; margin-bottom: 10px;"
             class="uk-position-relative uk-border-rounded">
            <span uk-icon="icon: open_with"
                  style="top: 10px; left: 10px;"
                  class="uk-position-absolute uk-position-z-index uk-display-block uk-sortable-handle">
            </span>
            <input type="hidden"
                   name="{{ $options['field'] }}[{{ $file->id }}][id]"
                   value="{{ $file->id }}">
            <div class="uk-image uk-border-rounded">
                <div class="uk-button-group uk-position-absolute uk-position-z-index"
                     style="top: 5px; right: 5px;">
                    <button type="button"
                            uk-icon="icon: info"
                            data-path="{{ _r('ajax.file.update', ['file' => $file->id]) }}"
                            class="uk-button uk-button-icon uk-button-primary uk-button-xsmall use-ajax">
                    </button>
                    <button type="button"
                            data-fid="{{ $file->id }}"
                            uk-icon="icon: delete_forever"
                            class="uk-button uk-button-icon uk-button-danger uk-file-remove-button uk-button-xsmall">
                    </button>
                </div>
                {!! render_image($file, 'thumb_small') !!}
            </div>
        </div>
    @elseif($options['view'] == 'background')
        <div>
            <div class="uk-position-relative uk-width-1-1 uk-height-small uk-image">
                <input type="hidden"
                       name="{{ $options['field'] }}[{{ $file->id }}][id]"
                       value="{{ $file->id }}">
                <button type="button"
                        data-fid="{{ $file->id }}"
                        uk-icon="icon: delete_forever"
                        class="uk-button uk-button-icon uk-button-danger uk-file-remove-button uk-position-absolute uk-position-z-index uk-button-xsmall"
                        style="top: 5px; right: 5px;">
                </button>
                {!! render_image($file, 'thumb_small') !!}
            </div>
        </div>
    @elseif($options['view'] == 'avatar')
        <div>
            <div class="uk-position-relative uk-width-1-1 uk-height-1-1 uk-image">
                <input type="hidden"
                       name="{{ $options['field'] }}[{{ $file->id }}][id]"
                       value="{{ $file->id }}">
                <button type="button"
                        data-fid="{{ $file->id }}"
                        uk-icon="icon: delete_forever"
                        class="uk-button uk-button-icon uk-button-danger uk-file-remove-button uk-position-absolute uk-position-z-index uk-button-xsmall"
                        style="top: 5px; right: 5px;">
                </button>
                {!! render_image($file, 'thumb_small') !!}
            </div>
        </div>
    @elseif($options['view'] == 'view')
        <div>
            <div uk-lightbox
                 class="uk-position-relative uk-width-1-1 uk-height-1-1 uk-image">
                <a href="{{ "/storage/{$file->file_name}" }}">
                    {!! render_image($file, 'thumb_small') !!}
                </a>
            </div>
        </div>
    @else
        <div>
            <div class="uk-position-relative uk-width-1-1 uk-height-1-1 uk-image">
                <input type="hidden"
                       name="{{ $options['field'] }}[{{ $file->id }}][id]"
                       value="{{ $file->id }}">
                <div class="uk-button-group uk-position-absolute uk-position-z-index"
                     style="top: 5px; right: 5px;">
                    <button type="button"
                            uk-icon="icon: info"
                            data-path="{{ _r('ajax.file.update', ['file' => $file->id]) }}"
                            class="uk-button uk-button-icon uk-button-primary uk-button-xsmall use-ajax">
                    </button>
                    <button type="button"
                            data-fid="{{ $file->id }}"
                            uk-icon="icon: delete_forever"
                            class="uk-button uk-button-icon uk-button-danger uk-file-remove-button uk-button-xsmall">
                    </button>
                </div>
                {!! render_image($file, 'thumb_small') !!}
            </div>
        </div>
    @endif
</div>
