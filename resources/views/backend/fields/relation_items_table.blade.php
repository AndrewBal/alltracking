@if($items->isNotEmpty())
    <table
        class="uk-table uk-table-bordered uk-table-xsmall uk-table-hover uk-table-middle">
        <thead>
            <tr>
                <th class="uk-width-xsmall uk-text-center">
                    ID
                </th>
                <th>
                    Заголовок материала
                </th>
                <th class="uk-text-center"
                    style="width: 34px;">
                    <span uk-icon="icon: format_list_bulleted"></span>
                </th>
                <th class="uk-text-center"
                    style="width: 34px;">
                    <span uk-icon="icon: visibility"></span>
                </th>
                <th class="uk-text-center"
                    style="width: 34px;">
                    <span uk-icon="icon: delete"></span>
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $_item)
                <tr>
                    <td class="uk-text-center"
                        style="width: 40px;">
                        {!! $_item[0] !!}
                    </td>
                    <td>
                        {!! $_item[1] !!}
                    </td>
                    <td class="uk-text-center">
                        {!! $_item[2] !!}
                    </td>
                    <td class="uk-text-center">
                        {!! $_item[3] !!}
                    </td>
                    <td class="uk-text-center">
                        {!! $_item[4] !!}
                    </td>
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

