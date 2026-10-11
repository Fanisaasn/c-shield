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
                materi Social Engineering sebelum mengerjakan Post-Assessment.
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
            <select name="mode" id="mode" required onchange="updateModeState(this)"
                    class="mt-1.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <option value="pre" class="text-navy-900">Pre-Assessment</option>
                @if ($postUnlocked)
                    <option value="post" class="text-navy-900">Post-Assessment</option>
                @else
                    <option value="post" class="text-red-600">Post-Assessment</option>
                @endif
            </select>

            @unless ($postUnlocked)
                <p id="post-hint" class="mt-2 flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                    Post-Assessment akan aktif setelah Anda menyelesaikan Pre-Assessment.
                </p>

                <div id="post-locked-alert" role="alert"
                     class="mt-3 hidden items-start gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                    </svg>
                    <div>
                        <p class="font-semibold">Post-Assessment belum dapat digunakan</p>
                        <p class="mt-0.5 text-red-600">
                            Anda belum menyelesaikan Pre-Assessment. Silakan kerjakan Pre-Assessment terlebih dahulu.
                        </p>
                    </div>
                </div>
            @endunless

            <button type="submit" id="start-button"
                    class="mt-6 w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                Mulai
            </button>
        </form>
    </section>

@endsection

@push('scripts')
    <script>
        const postUnlocked = @json($postUnlocked);

        function updateModeState(select) {
            const locked = select.value === 'post' && !postUnlocked;
            const alertBox = document.getElementById('post-locked-alert');
            const hint = document.getElementById('post-hint');

            select.classList.toggle('border-red-500', locked);
            select.classList.toggle('text-red-600', locked);
            select.classList.toggle('ring-1', locked);
            select.classList.toggle('ring-red-500', locked);
            select.classList.toggle('border-slate-300', !locked);

            if (alertBox) {
                alertBox.classList.toggle('hidden', !locked);
                alertBox.classList.toggle('flex', locked);
            }
            if (hint) {
                hint.classList.toggle('hidden', locked);
            }

            document.getElementById('start-button').disabled = locked;
        }

        function checkMode(form) {
            const select = form.querySelector('#mode');

            if (select.value === 'post' && !postUnlocked) {
                updateModeState(select);
                return false;
            }

            return true;
        }
    </script>
@endpush
