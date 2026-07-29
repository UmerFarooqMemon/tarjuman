@if (isset($errors) && count($errors) > 0)
    @foreach ($errors->all() as $error)
        <script>
            toastr.error(@json($error), @json(__('general.error')));
        </script>
    @endforeach
@endif

@if (Session::get('error'))
<script>
    toastr.error(@json(Session::get('error')), @json(__('general.error')));
</script>
@endif

@if (Session::get('success'))
<script>
    toastr.success(@json(Session::get('success')), @json(__('general.success')));
</script>
@endif

@if (session('status'))
<script>
    toastr.success(@json(session('status')), @json(__('general.success')));
</script>
@endif
