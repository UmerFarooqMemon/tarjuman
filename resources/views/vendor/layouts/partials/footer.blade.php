<footer class="content-footer footer bg-footer-theme">
    <div class="container-xxl">
        <div class="footer-container d-flex align-items-center justify-content-between py-2 flex-md-row flex-column">
            <div>
                ©
                <script>document.write(new Date().getFullYear());</script>
                <a href="javascript:;" class="fw-medium">{{ config('app.name', 'Tarjuman') }}</a>
                {!! $siteSettings->footer_sentence ?? '' !!}
            </div>
        </div>
    </div>
</footer>
