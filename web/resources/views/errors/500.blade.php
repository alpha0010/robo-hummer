@extends('errors::layout')

@section('title', 'Error')

@section('message', $exception->getMessage() ?? 'Whoops, looks like something went wrong.' )
