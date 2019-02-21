@extends('errors::illustrated-layout')

@section('code', '404')
@section('title', __('Page Not Found'))

@section('image')
<div style="background-image: url({{ asset('/svg/404.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
@if (isset(json_decode($exception->getMessage())[1]))
<pre style="background-color: rgba(255, 255, 255, .5); color: black; white-space: pre-wrap; margin: 0;">
{{json_decode($exception->getMessage())[1]}}
</pre>
@endif
</div>
@endsection

@section('message', json_decode($exception->getMessage())[0] ?? __('Sorry, the page you are looking for could not be found.'))
