@push('head')
    <meta name="robots" content="noindex"/>
    <meta name="google" content="notranslate">
    <link
          href="{{ asset('images/favicon.png') }}"
          sizes="any"
          type="image/png"
          id="favicon"
          rel="icon"
    >

    <!-- For Safari on iOS -->
    <meta name="theme-color" content="#1F3A2E">
@endpush

{{--
    Only used on the login page now (the sidebar shows a profile card
    instead — see dashboard.blade.php). The login page's background is
    cream, and the logo's dark green reads fine directly against it, so
    no white backing card is needed here — that was only ever necessary
    against the sidebar's own dark green.
--}}
<img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="img-fluid" style="max-height: 5rem;">
