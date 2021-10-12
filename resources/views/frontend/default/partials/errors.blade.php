@if ($errors->any())
    <div class="uk-alert uk-alert-danger uk-margin-small-bottom">
        <ul class="uk-list">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
