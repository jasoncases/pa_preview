<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="apple-mobile-web-app-title" content="Proaction" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{$page_title ?? "Proaction"}}</title>
    <link rel="icon" type="image/png" href="/favicon_md.png" />

    <x-style_header :rand="$rand"></x-style_header>

    <script rel="preload" type="module" src="/js/globalComponents.js?{rand}"></script>
    <script type="text/javascript" src="/js/polyfill.js?{rand}?{rand}"></script>
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js?{rand}"></script>

</head>
