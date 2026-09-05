@extends('user.layouts.app')

@section('title', 'Flyer')
@section('meta_description', 'Kumpulan flyer sosialisasi keamanan siber dari C-SHIELD Diskominfo Kota Cimahi.')

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-white">Flyer</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Materi sosialisasi visual seputar keamanan siber yang ringkas dan mudah dibagikan.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($flyers->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                Belum ada flyer yang dipublikasikan.
            </p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($flyers as $flyer)
                    <a href="{{ route('flyers.show', $flyer) }}" class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex aspect-[3/4] items-center justify-center bg-slate-100">
                            @if ($flyer->image)
                                <img src="{{ asset('storage/' . $flyer->image) }}" alt="{{ $flyer->title }}" class="h-full w-full object-cover">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-slate-300">
                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="m3 16 5-5 4 4 5-6 4 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif
                        </div>
                        <div class="p-4">
                            <h2 class="font-heading text-sm font-bold text-navy-900 group-hover:text-blue-600">
                                {{ $flyer->title }}
                            </h2>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $flyers->links() }}
            </div>
        @endif
    </section>
@endsection
