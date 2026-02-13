<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/images/LOGO_LIGHT.png" type="image/png" media="(prefers-color-scheme: light)">
<link rel="icon" href="/images/LOGO_DARK.webp" type="image/webp" media="(prefers-color-scheme: dark)">
<link rel="apple-touch-icon" href="/images/LOGO_LIGHT.png">

@stack('styles')
@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

