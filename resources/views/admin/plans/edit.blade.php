@extends('admin.layouts.app')

@section('title', 'Edit Plan')
@section('page-title', 'Edit Plan: ' . $plan->label)

@section('content')
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-3xl">
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
            @csrf
            @method('PUT')
            @include('admin.plans._form', ['plan' => $plan])
        </form>
    </div>
@endsection
