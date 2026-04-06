<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('{{ asset('service-worker.js') }}').catch(function (error) {
            console.error('PWA registration failed:', error);
        });
    });
}
</script>
