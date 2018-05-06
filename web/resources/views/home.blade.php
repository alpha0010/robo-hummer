@extends('layouts.app')

@section('scripts')
<script src="{{ asset('js/welcome.js') }}" defer></script>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">The song searcher</div>

                <div class="card-body">
                    <button id="record" type="button" class="btn btn-primary">Record</button>
                    <button id="save" type="button" class="btn btn-primary">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
