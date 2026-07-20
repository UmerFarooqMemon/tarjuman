@if (isset($errors) && count($errors) > 0)
    @foreach ($errors->all() as $error)
        <script>
            toastr.error('{{ $error }}', 'Error!', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 2000 });
        </script>
    @endforeach
@endif

@if (Session::get('error'))
<script>
    toastr.error("{!! Session::get('error') !!}", 'Error!', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 2000 });
</script>
@endif

@if (Session::get('success'))
<script>
    toastr.success("{!! Session::get('success') !!}", 'Success', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 2000 });
</script>
@endif

@if (session('status'))
<script>
    toastr.success("{!! session('status') !!}", 'Success', { "showMethod": "slideDown", "hideMethod": "slideUp", timeOut: 2000 });
</script>
@endif