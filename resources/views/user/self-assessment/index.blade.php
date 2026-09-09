@extends('user.layouts.app')

@section('title', 'Self Assessment')
@section('meta_description', 'Ukur tingkat kesadaran keamanan siber Anda melalui Pre-Assessment dan Post-Assessment C-SHIELD.')

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Ukur Tingkat Kesadaran Keamanan Siber Anda
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Kerjakan Pre-Assessment terlebih dahulu untuk mengetahui pemahaman awal Anda. Setelah itu, pelajari
                materi Video, Artikel, dan Flyer yang tersedia sebelum mengerjakan Post-Assessment.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-lg px-4 py-10 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('self-assessment.start') }}" class="rounded-xl border border-slate-200 bg-white p-6 sm:p-8"
              onsubmit="return checkMode(this);">
            @csrf

            <label for="mode" class="block text-sm font-medium text-navy-900">Pilih Assessment</label>
            <select name="mode" id="mode" required
                    class="mt-1.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="pre">Pre-Assessment</option>
                <option value="post" {{ $preCompleted ? '' : 'disabled' }}>Post-Assessment</option>
            </select>

            @unless ($preCompleted)
                <p class="mt-2 text-xs text-slate-500">
                    Post-Assessment akan aktif setelah Anda menyelesaikan Pre-Assessment.
                </p>
            @endunless

            <button type="submit"
                    class="mt-6 w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                Mulai
            </button>
        </form>
    </section>

@endsection

@push('scripts')
    <script>
        function checkMode(form) {
            const mode = form.querySelector('#mode').value;
            const preCompleted = @json($preCompleted);

            if (mode === 'post' && !preCompleted) {
                alert('Kerjakan Pre-Assessment terlebih dahulu.');
                return false;
            }

            return true;
        }
    </script>
@endpush
