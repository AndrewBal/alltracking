@if (session('notices'))
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            @foreach(session('notices') as $_notice)
            cmd_UK_notification({
                text: '{!! $_notice['message'] !!}',
                status: '{{ $_notice['status'] ?? 'primary' }}',
                pos: '{{ $_notice['pos'] ?? 'top-center' }}',
                timeout: {{ $_notice['timeout'] ?? 5000 }}
            })
            @endforeach
        });
    </script>
@endif
@if (session('modal'))
    @php
        $_modal = session('modal');
        $_modal_status = isset($_modal['status']) && $_modal['status'] ? " uk-modal-{$_modal['status']}" : NULL;
    @endphp
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            cmd_UK_modal({
                content: '<button class="uk-modal-close-outside" type="button" uk-close></button><div class="uk-modal-body{{ $_modal_status }}">{!! $_modal['content'] !!}</div>',
                bgClose: {{ $_modal['bgClose'] ?? TRUE }},
                clsPage: '{{ $_modal['clsPage'] ?? 'uk-modal-page view-ajax-modal' }}',
                id: '{{ $_modal['id'] ?? 'ajax-modal' }}',
                classDialog: '{{ $_modal['classDialog'] ?? 'uk-margin-auto-vertical' }}',
                classModal: '{{ $_modal['classModal'] ?? NULL }}'
            })
        });
    </script>
@endif
@if (session('commands'))
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(function () {
                var c = <?= session('commands') ?>;
                for (var i = 0; i < c.length; ++i) {
                    if (window['cmd_' + c[i].command] != undefined) window['cmd_' + c[i].command](c[i].options)
                }
            }, 500);
        });
    </script>
@endif
