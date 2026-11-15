<section id="workflow" class="security-workflow mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-labelledby="workflow-title">
    <div class="workflow-intro" data-reveal>
        <p class="eyebrow">02 / INSIDE THE SECURITY LAB</p>
        <h2 id="workflow-title">Dari koneksi.<br><span>Menjadi proteksi.</span></h2>
        <p>Telusuri cara pertahanan siber bekerja. Lima tahap untuk memahami apa yang terjadi di balik jaringan yang aman.</p>
        <div class="workflow-monitor" aria-hidden="true"><span class="status-dot"></span> <span data-workflow-label>NETWORK</span><div class="workflow-meter"><i></i></div><span>SCROLL TO EXPLORE ↓</span></div>
    </div>
    <ol class="workflow-stages">
        @foreach ([
            ['NETWORK', 'Setiap koneksi punya cerita.', 'Perangkat, pengguna, dan layanan saling terhubung. Mengenali jaringan adalah langkah pertama memahami permukaan serangan.', '01 / CONNECT'],
            ['PACKET', 'Baca jejak pertukaran data.', 'Informasi bergerak dalam paket. Alamat sumber, tujuan, dan protokol membantu menjelaskan aktivitas di dalam jaringan.', '02 / INSPECT'],
            ['ANALYSIS', 'Temukan pola di balik aktivitas.', 'Bandingkan aktivitas dengan perilaku normal. Anomali menjadi petunjuk untuk pemeriksaan lebih lanjut.', '03 / UNDERSTAND'],
            ['THREAT', 'Kenali risiko sebelum meluas.', 'Identifikasi indikasi phishing, akses mencurigakan, atau malware. Validasi konteks sebelum menentukan respons.', '04 / IDENTIFY'],
            ['DEFENSE', 'Bangun pertahanan berlapis.', 'Gunakan autentikasi berlapis, pembaruan sistem, dan kebiasaan digital yang aman. Pertahanan dimulai dari kesadaran.', '05 / PROTECT'],
        ] as [$stage, $title, $description, $code])
            <li class="workflow-stage" data-stage="{{ $stage }}">
                <span class="stage-node">{{ $loop->iteration }}</span>
                <p class="eyebrow">{{ $code }}</p><h3>{{ $stage }}</h3>
                <h4>{{ $title }}</h4><p>{{ $description }}</p>
            </li>
        @endforeach
    </ol>
</section>
