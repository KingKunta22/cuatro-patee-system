<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Laravel session messages
    @if(session('success'))
        Toast.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        Toast.error('{{ session('error') }}');
    @endif

    @if(session('warning'))
        Toast.warning('{{ session('warning') }}');
    @endif

    @if(session('info'))
        Toast.info('{{ session('info') }}');
    @endif

    // Handle validation errors
    @if($errors->any())
        @foreach($errors->all() as $error)
            Toast.error('{{ $error }}');
        @endforeach
    @endif
});
</script>