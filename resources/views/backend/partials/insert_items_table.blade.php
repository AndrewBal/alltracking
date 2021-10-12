@if($items->isNotEmpty())
    <table
        class="uk-table uk-table-bordered uk-table-xsmall uk-table-hover uk-table-middle">
        <thead>
            <tr>
                @foreach($items['headers'] as $_th)
                    <th class="{{ $_th['class'] ?? NULL }}"
                        style="{{ $_th['style'] ?? NULL }}">
                        {!! $_th['data'] !!}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($items['items'] as $_item)
                <tr>
                    @foreach($_item as $_i => $_data)
                        <td class="{{ $items['headers'][$_i]['class'] ?? NULL }}"
                            style="{{ $items['headers'][$_i]['style'] ?? NULL }}">
                            {!! $_data !!}
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <div class="uk-alert uk-alert-warning uk-border-rounded"
         uk-alert>
        Список элементов пуст
    </div>
@endif

