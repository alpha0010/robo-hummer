@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/welcome.js') }}" defer></script>
@endsection

@section('content')
<div class="container">
    {!! $content !!}
</div>
@endsection
