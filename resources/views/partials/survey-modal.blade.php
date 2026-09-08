@php
    $scaleOptions = [
        1 => 'Sangat tidak setuju',
        2 => 'Tidak Setuju',
        3 => 'Setuju',
        4 => 'Sangat setuju',
    ];
@endphp

<div id="survey-modal" class="fixed inset-0 z-50 items-center justify-center bg-black/50 px-4 py-8" style="display:none;">
    <div class="flex max-h-full w-full max-w-lg flex-col rounded-xl bg-white shadow-lg">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h2 class="font-heading text-lg font-bold text-navy-900">Survei Kepuasan</h2>
                <p class="mt-0.5 text-sm text-slate-500">Bantu kami meningkatkan layanan C-SHIELD.</p>
            </div>
            <button type="button" onclick="document.getElementById('survey-modal').style.display='none'" aria-label="Tutup" class="text-slate-400 hover:text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('survey.store') }}" class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
            @csrf

            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-navy-900">Identitas Pengisi</h3>

                <div>
                    <label class="text-sm font-medium">Nama <span class="text-slate-400">(opsional)</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-md border-slate-300 text-sm">
                </div>

                <div>
                    <label class="text-sm font-medium">Tanggal Menerima Layanan <span class="text-red-500">*</span></label>
                    <input type="date" name="service_date" value="{{ old('service_date', now()->toDateString()) }}" required class="mt-1 w-full rounded-md border-slate-300 text-sm">
                    @error('service_date')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Pendidikan <span class="text-red-500">*</span></label>
                    <select name="education" required class="mt-1 w-full rounded-md border-slate-300 text-sm">
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
                    <label class="text-sm font-medium">Usia <span class="text-red-500">*</span></label>
                    <input type="number" name="age" value="{{ old('age') }}" min="5" max="120" required class="mt-1 w-full rounded-md border-slate-300 text-sm">
                    @error('age')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-medium">Pekerjaan <span class="text-red-500">*</span></label>
                    <select name="occupation" required class="mt-1 w-full rounded-md border-slate-300 text-sm">
                        <option value="" disabled {{ old('occupation') ? '' : 'selected' }}>Pilih pekerjaan</option>
                        @foreach (['Pelajar/Mahasiswa', 'ASN/Pegawai Pemerintah', 'Pegawai Swasta', 'Wiraswasta', 'Tidak Bekerja', 'Lainnya'] as $option)
                            <option value="{{ $option }}" {{ old('occupation') === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error('occupation')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <span class="block text-sm font-medium text-navy-900">Apakah Anda merupakan penyandang disabilitas/pendamping penyandang disabilitas?</span>

                    <label class="mt-2 flex w-max cursor-pointer select-none items-center gap-3 text-sm">
                        <span class="text-navy-900">Tidak</span>
                        <input type="checkbox" name="is_disability" value="1" {{ old('is_disability') ? 'checked' : '' }}
                               onchange="document.getElementById('disability-types').classList.toggle('hidden', !this.checked)"
                               class="peer sr-only">
                        <span class="relative h-6 w-11 shrink-0 rounded-full bg-slate-300 transition peer-checked:bg-blue-600 after:absolute after:left-0.5 after:top-0.5 after:h-5 after:w-5 after:rounded-full after:bg-white after:shadow after:transition-all peer-checked:after:translate-x-5"></span>
                        <span class="text-blue-600">Ya</span>
                    </label>

                    <div id="disability-types" class="mt-3 space-y-2 {{ old('is_disability') ? '' : 'hidden' }}">
                        <p class="text-xs text-slate-400">Jika ya, jenis disabilitas apa yang Anda miliki/dampingi? (Jika tidak, lewati)</p>
                        @foreach (['Fisik', 'Intelektual', 'Mental', 'Sensorik'] as $type)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="disability_types[]" value="{{ $type }}" {{ in_array($type, old('disability_types', [])) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                Disabilitas {{ $type }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-5 space-y-5 border-t border-slate-100 pt-5">
                @forelse ($surveyQuestions as $question)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <p class="text-sm font-medium text-navy-900">
                            {{ $question->question }} <span class="text-red-500">*</span>
                        </p>
                        <div class="mt-3 space-y-2">
                            @foreach ($scaleOptions as $value => $label)
                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $value }}" required class="border-slate-300 text-teal-600 focus:ring-teal-500">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                        @error('answers.'.$question->id)
                            <p class="mt-2 text-xs text-red-600">Mohon pilih salah satu jawaban.</p>
                        @enderror
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Survei belum tersedia.</p>
                @endforelse
            </div>

            <div class="mt-5 space-y-3 border-t border-slate-100 pt-5">
                <div>
                    <label class="text-sm font-medium">Saran / komentar <span class="text-slate-400">(opsional)</span></label>
                    <textarea name="message" rows="3" class="mt-1 w-full rounded-md border-slate-300 text-sm">{{ old('message') }}</textarea>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('survey-modal').style.display='none'" class="px-4 py-2 text-sm text-slate-600">Batal</button>
                <button type="submit" class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950">Kirim Survei</button>
            </div>
        </form>
    </div>
</div>

@if ($errors->has('answers') || collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'answers.')))
    <script>document.addEventListener('DOMContentLoaded', () => document.getElementById('survey-modal').style.display = 'flex');</script>
@endif
