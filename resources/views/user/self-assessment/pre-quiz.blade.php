@extends('user.layouts.app')

@section('title', 'Pre-Assessment')
@section('meta_description', 'Kerjakan Pre-Assessment kesadaran keamanan siber C-SHIELD.')

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 3 dari 7
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Pre-Assessment
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Jawab seluruh pertanyaan berikut sesuai pemahaman Anda saat ini. Tidak perlu khawatir jika ada
                jawaban yang salah &mdash; hasil ini menjadi titik awal sebelum Anda mempelajari materi.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('self-assessment.pre.store') }}"
              onsubmit="return validateQuiz(this);">
            @csrf

            @include('user.self-assessment._questions')

            <button type="submit"
                    class="mt-8 w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                Kirim Jawaban
            </button>
        </form>
    </section>

@endsection
