@extends('admin.layouts.app')

@section('title', 'New Plan')
@section('page-title', 'New Plan')

@section('content')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.plans.store') }}">
            @csrf
            @include('admin.plans._form')
        </form>
    </div>
@endsection
