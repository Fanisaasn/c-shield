@extends('user.layouts.app')

@section('title', 'Pre-Assessment')
@section('meta_description', 'Kerjakan Pre-Assessment kesadaran keamanan siber C-SHIELD.')

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 3 dari 4
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Pre-Assessment &mdash; {{ $category->name }}
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Jawab seluruh pertanyaan berikut sesuai pemahaman Anda saat ini. Tidak ada jawaban benar/salah
                yang perlu dikhawatirkan &mdash; hasil ini menjadi titik awal sebelum Anda mempelajari materi.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('self-assessment.pre.store') }}">
            @csrf

            <div class="space-y-4">
                @foreach ($questions as $question)
                    <fieldset class="rounded-xl border border-slate-200 bg-white p-5">
                        <legend class="px-1 text-sm font-medium text-navy-900">
                            {{ $loop->iteration }}. {{ $question->question }}
                        </legend>
                        <div class="mt-3 space-y-2">
                            @foreach ($question->options as $option)
                                <label class="flex items-start gap-2.5 rounded-md border border-slate-200 px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}"
                                           {{ old('answers.' . $question->id) == $option->id ? 'checked' : '' }} required
                                           class="mt-0.5 border-slate-300 text-blue-600 focus:ring-blue-600">
                                    <span>{{ $option->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach
            </div>

            <button type="submit"
                    class="mt-8 w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                Kirim Jawaban
            </button>
        </form>
    </section>

@endsection
