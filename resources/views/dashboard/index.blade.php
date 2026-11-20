@extends('layouts.app')

@section('title', 'Universal Dashboard')

@section('content')
<main class="page-shell">
    <header class="page-heading">
        <div>
            <p class="eyebrow">JSON-driven workspace</p>
            <h1>Universal Dashboard</h1>
            <p class="subtitle">Configure widgets once and render them from one dashboard payload.</p>
        </div>
    </header>

    <x-universal-dashboard :dashboard="$dashboard" />
</main>
@endsection
