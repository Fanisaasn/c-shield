@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-8">
        <h2 class="font-heading text-xl font-bold text-navy-900">
            Selamat datang, {{ auth('admin')->user()->name }}
        </h2>
        <p class="mt-2 max-w-xl text-sm text-slate-500">
            Ringkasan konten dan hasil self-assessment C-SHIELD saat ini.
        </p>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                <p class="mt-2 font-heading text-3xl font-bold text-navy-900">{{ $stat['total'] }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $stat['published'] }} dipublikasikan</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Peserta Self Assessment</p>
            <p class="mt-2 font-heading text-3xl font-bold text-navy-900">{{ $assessmentStats['participants'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Pengerjaan Assessment</p>
            <p class="mt-2 font-heading text-3xl font-bold text-navy-900">{{ $assessmentStats['attempts'] }}</p>
        </div>
    </div>
@endsection
