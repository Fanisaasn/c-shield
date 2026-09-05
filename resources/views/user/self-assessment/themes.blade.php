@extends('user.layouts.app')

@section('title', 'Self Assessment')
@section('meta_description', 'Pilih tema self-assessment kesadaran keamanan siber C-SHIELD sesuai minat Anda.')

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Pilih Tema Self Assessment
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Pilih tema materi yang ingin Anda ukur tingkat pemahamannya. Anda akan mengisi data diri singkat,
                lalu mengerjakan Pre-Assessment sesuai tema yang dipilih.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if ($categories->isEmpty())
            <p class="text-center text-sm text-slate-500">Belum ada tema assessment yang tersedia.</p>
        @else
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($categories as $category)
                    <a href="{{ route('self-assessment.create', $category) }}"
                       class="group rounded-xl border border-slate-200 bg-white p-6 transition hover:-translate-y-0.5 hover:border-blue-600/30 hover:shadow-md">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600/10 text-blue-600 transition group-hover:bg-teal-500/10 group-hover:text-teal-500">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M12 2 4 5v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V5l-8-3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <h2 class="mt-4 font-heading text-base font-bold text-navy-900 group-hover:text-blue-600">
                            {{ $category->name }}
                        </h2>
                        @if ($category->description)
                            <p class="mt-1.5 text-sm text-slate-500">{{ $category->description }}</p>
                        @endif
                        <p class="mt-3 text-xs font-medium uppercase tracking-wide text-slate-400">
                            {{ $category->questions_count }} pertanyaan
                        </p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

@endsection
