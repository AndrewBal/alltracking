<div class="file uk-position-relative">
    @if($options['view'] == 'view')
        @l(Str::limit($file->file_name, 80), $file->base_url, ['attributes' => ['target' => '_blank', 'title' => $file->file_name]])
        ({{ file_size_number_format_short($file->file_size) }})
    @else
        <div uk-grid
             class="uk-grid-collapse uk-flex uk-flex-middle">
            <input type="hidden"
                   name="{{ $options['field'] }}[{{ $file->id }}][id]"
                   value="{{ $file->id }}">
            <div class="uk-width-expand">
                @l(Str::limit($file->file_name, 80), $file->base_url, ['attributes' => ['target' => '_blank', 'title' => $file->file_name]])
                ({{ file_size_number_format_short($file->file_size) }})
            </div>
            <div class="uk-width-auto">
                <div class="uk-button-group">
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
            </div>
        </div>
    @endif
</div>
