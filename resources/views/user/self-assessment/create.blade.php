@extends('user.layouts.app')

@section('title', 'Self Assessment')
@section('meta_description', 'Mulai self-assessment kesadaran keamanan siber C-SHIELD dengan mengisi data diri singkat.')

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 2 dari 4
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Data Diri Responden
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Tema yang dipilih: <span class="font-semibold text-teal-300">{{ $category->name }}</span>.
                Data ini digunakan untuk mengelompokkan hasil self-assessment secara umum. Isian Anda tidak
                dipublikasikan dan tidak memerlukan pembuatan akun.
            </p>
            <a href="{{ route('self-assessment.themes') }}" class="mt-3 inline-block text-xs font-medium text-white/60 hover:text-white">
                &larr; Ganti tema
            </a>
        </div>
    </section>

    <section class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('self-assessment.store', $category) }}" class="rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
            @csrf

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
                    <label for="domicile" class="block text-sm font-medium text-navy-900">Domisili (Kecamatan/Kelurahan)</label>
                    <input type="text" name="domicile" id="domicile" value="{{ old('domicile') }}" required
                           class="mt-1.5 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    @error('domicile')
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

@endsection
