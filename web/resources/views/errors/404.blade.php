@extends('errors.illustrated-layout')

@section('code', '404')
@section('title', __('Page Not Found'))

@section('image')
<div style="background-image: url({{ asset('/svg/404.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center">
@if (isset($file))
<pre style="background-color: rgba(255, 255, 255, .5); color: black; white-space: pre-wrap; margin: 0;">
{{$file}}
</pre>
@endif
</div>
@endsection

@section('message', $message ?? __('Sorry, the page you are looking for could not be found.'))
