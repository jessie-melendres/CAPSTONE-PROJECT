<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'NCBII Academic Information System' }}</title>
    <link rel="stylesheet" href="{{ route('legacy.style') }}">
    <script src="{{ asset('js/portal.js') }}" defer></script>
</head>
<body class="{{ $bodyClass ?? '' }}">
    @yield('content')
</body>
</html>
