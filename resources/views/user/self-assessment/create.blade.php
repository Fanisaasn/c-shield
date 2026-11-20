@extends('user.layouts.app')

@section('title', 'Self Assessment')
@section('meta_description', 'Mulai self-assessment kesadaran keamanan siber C-SHIELD dengan mengisi data diri singkat.')

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 2 dari 7
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Data Diri Responden
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Data ini digunakan untuk mengelompokkan hasil self-assessment secara umum. Isian Anda tidak
                dipublikasikan dan tidak memerlukan pembuatan akun.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form id="respondent-form" method="POST" action="{{ route('self-assessment.store') }}" novalidate class="rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
            @csrf

            <div id="form-alert" role="alert" class="{{ $errors->any() ? 'flex' : 'hidden' }} mb-6 items-start gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                <span>Data diri belum lengkap. Lengkapi kolom yang ditandai merah untuk melanjutkan.</span>
            </div>

            <div class="space-y-5">
                <div>
                    <label for="name" class="block text-sm font-medium text-navy-900">Nama / Inisial</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone_last_digits" class="block text-sm font-medium text-navy-900">4 Digit Terakhir Nomor HP</label>
                    <input type="text" name="phone_last_digits" id="phone_last_digits" value="{{ old('phone_last_digits') }}"
                           inputmode="numeric" maxlength="4" required
                           class="mt-1.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    @error('phone_last_digits')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="block text-sm font-medium text-navy-900">Jenis Kelamin</span>
                    <div class="mt-1.5 flex gap-4">
                        @foreach (['Laki-laki', 'Perempuan'] as $option)
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="radio" name="gender" value="{{ $option }}" {{ old('gender') === $option ? 'checked' : '' }} required
                                       class="border-slate-300 text-blue-600 focus:ring-blue-600">
                                {{ $option }}
                            </label>
                        @endforeach
                    </div>
                    @error('gender')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="age" class="block text-sm font-medium text-navy-900">Usia</label>
                    <input type="number" name="age" id="age" value="{{ old('age') }}" min="5" max="120" required
                           class="mt-1.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    @error('age')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="education" class="block text-sm font-medium text-navy-900">Pendidikan Terakhir</label>
                    <select name="education" id="education" required
                            class="mt-1.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <option value="" disabled {{ old('education') ? '' : 'selected' }}>Pilih pendidikan terakhir</option>
                        @foreach (['SD/Sederajat', 'SMP/Sederajat', 'SMA/SMK/Sederajat', 'Diploma (D1-D4)', 'S1', 'S2', 'S3'] as $option)
                            <option value="{{ $option }}" {{ old('education') === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('education')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="domicile" class="block text-sm font-medium text-navy-900">Domisili (Kecamatan)</label>
                    <select name="domicile" id="domicile" required
                            class="mt-1.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <option value="" disabled {{ old('domicile') ? '' : 'selected' }}>Pilih kecamatan</option>
                        @foreach (\App\Models\AssessmentUser::DOMICILES as $region => $districts)
                            <optgroup label="{{ $region }}">
                                @foreach ($districts as $district)
                                    <option value="{{ $district }}" {{ old('domicile') === $district ? 'selected' : '' }}>{{ $district }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>                    @error('domicile')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="occupation_status" class="block text-sm font-medium text-navy-900">Status / Pekerjaan</label>
                    <select name="occupation_status" id="occupation_status" required
                            class="mt-1.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <option value="" disabled {{ old('occupation_status') ? '' : 'selected' }}>Pilih status/pekerjaan</option>
                        @foreach (['Pelajar', 'Mahasiswa', 'ASN/Pegawai Pemerintah', 'Karyawan Swasta', 'Wiraswasta', 'Tidak Bekerja', 'Lainnya'] as $option)
                            <option value="{{ $option }}" {{ old('occupation_status') === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('occupation_status')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <button type="submit"
                    class="mt-8 w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                Lanjut ke Pre-Assessment
            </button>
        </form>
    </section>

    <script>
        (() => {
            const form = document.getElementById('respondent-form');
            const alertBox = document.getElementById('form-alert');
            const messages = {
                name: 'Nama / inisial wajib diisi.',
                phone_last_digits: '4 digit terakhir nomor HP wajib diisi.',
                gender: 'Pilih jenis kelamin.',
                age: 'Usia wajib diisi.',
                education: 'Pilih pendidikan terakhir.',
                domicile: 'Pilih domisili (kecamatan).',
                occupation_status: 'Pilih status / pekerjaan.',
            };

            const fieldBox = (name) => form.querySelector(`[name="${name}"]`).closest('.space-y-5 > div');

            const errorFor = (name) => {
                const value = name === 'gender'
                    ? form.querySelector('[name="gender"]:checked')?.value
                    : form.elements[name].value.trim();
                if (!value) return messages[name];
                if (name === 'phone_last_digits' && !/^\d{4}$/.test(value)) return 'Isi tepat 4 digit angka terakhir nomor HP.';
                if (name === 'age' && (!/^\d+$/.test(value) || value < 5 || value > 120)) return 'Isi usia dengan angka antara 5 dan 120.';
                return null;
            };

            const showError = (name, message) => {
                const box = fieldBox(name);
                // The server-rendered message (after a failed submit) is replaced by the live one.
                box.querySelectorAll('p.text-red-600').forEach((p) => p.remove());
                box.querySelectorAll('input:not([type=radio]), select').forEach((el) => {
                    el.classList.toggle('border-red-500', Boolean(message));
                    el.classList.toggle('border-slate-300', !message);
                    el.setAttribute('aria-invalid', message ? 'true' : 'false');
                });
                if (message) {
                    const p = document.createElement('p');
                    p.className = 'mt-1 text-xs text-red-600';
                    p.textContent = message;
                    box.appendChild(p);
                }
            };

            form.addEventListener('submit', (event) => {
                const invalid = Object.keys(messages).filter((name) => {
                    const message = errorFor(name);
                    showError(name, message);
                    return message;
                });

                if (invalid.length) {
                    event.preventDefault();
                    alertBox.classList.replace('hidden', 'flex');
                    const first = form.querySelector(`[name="${invalid[0]}"]`);
                    fieldBox(invalid[0]).scrollIntoView({ behavior: 'smooth', block: 'center' });
                    first.focus({ preventScroll: true });
                    return;
                }

                const button = form.querySelector('button[type=submit]');
                button.disabled = true;
                button.textContent = 'Memproses...';
            });

            // Clear a field's warning as soon as it is filled in.
            Object.keys(messages).forEach((name) => {
                form.querySelectorAll(`[name="${name}"]`).forEach((el) => {
                    el.addEventListener(el.tagName === 'SELECT' || el.type === 'radio' ? 'change' : 'input', () => {
                        if (fieldBox(name).querySelector('p.text-red-600') && !errorFor(name)) showError(name, null);
                        if (!form.querySelector('p.text-red-600')) alertBox.classList.replace('flex', 'hidden');
                    });
                });
            });

            // Server-side errors: mark the fields too.
            form.querySelectorAll('p.text-red-600').forEach((p) => {
                p.closest('.space-y-5 > div')?.querySelectorAll('input:not([type=radio]), select').forEach((el) => {
                    el.classList.replace('border-slate-300', 'border-red-500');
                });
            });
        })();
    </script>

@endsection
