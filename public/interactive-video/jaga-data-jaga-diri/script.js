const scenes = [
    { id: 1, type: 'phishing', duration: 18, assessmentAt: 15, title: 'Email verifikasi akun', action: 'Ia mengeklik tautan verifikasi dalam email tanpa memeriksa pengirimnya.', safe: false, explanation: 'Tautan dari pengirim yang belum diverifikasi dapat mengarah ke situs pencuri akun. Periksa alamat pengirim dan konfirmasi melalui kanal resmi sebelum membuka tautan.' },
    { id: 2, type: 'password', duration: 12, assessmentAt: 9, title: 'Melindungi akun', action: 'Ia membuat kata sandi panjang dan unik menggunakan pengelola kata sandi.', safe: true, explanation: 'Kata sandi panjang dan unik membantu melindungi akun serta membatasi dampak jika kata sandi akun lain bocor.' },
    { id: 3, type: 'social', duration: 12, assessmentAt: 9, title: 'Permintaan kode verifikasi', action: 'Ia mengirim kode OTP kepada penelepon yang mengaku sebagai petugas IT.', safe: false, explanation: 'Kode OTP dapat digunakan untuk mengambil alih akun. Jangan membagikannya, termasuk kepada orang yang mengaku sebagai petugas IT.' },
    { id: 4, type: 'usb', duration: 12, assessmentAt: 9, title: 'USB yang ditemukan', action: 'Ia memasang USB yang ditemukan di meja umum ke laptop kerja.', safe: false, explanation: 'USB yang tidak diketahui asalnya dapat membawa malware. Serahkan perangkat kepada tim IT untuk diperiksa.' },
    { id: 5, type: 'wifi', duration: 12, assessmentAt: 9, title: 'Memilih koneksi', action: 'Ia memutus Wi-Fi publik dan beralih ke hotspot pribadi sebelum membuka sistem kantor.', safe: true, explanation: 'Hotspot pribadi mengurangi paparan terhadap jaringan publik yang tidak dikenal. Tetap gunakan HTTPS dan ikuti kebijakan koneksi kantor.' }
];
scenes.forEach(scene => {
    scene.question = 'Menurut kamu, tindakan ini aman atau berisiko?';
    scene.options = [{ text: 'Aman', correct: scene.safe }, { text: 'Berisiko', correct: !scene.safe }];
});
const sceneContent = document.querySelector('#sceneContent'), sceneCaption = document.querySelector('#sceneCaption'), sceneStage = document.querySelector('#sceneStage'), assessmentOverlay = document.querySelector('#assessmentOverlay'), questionCount = document.querySelector('#questionCount'), questionText = document.querySelector('#questionText'), answerList = document.querySelector('#answerList'), feedback = document.querySelector('#feedback'), feedbackIcon = document.querySelector('#feedbackIcon'), feedbackTitle = document.querySelector('#feedbackTitle'), feedbackText = document.querySelector('#feedbackText'), continueButton = document.querySelector('#continueButton'), playButton = document.querySelector('#playButton'), muteButton = document.querySelector('#muteButton'), volumeRange = document.querySelector('#volumeRange'), fullscreenButton = document.querySelector('#fullscreenButton'), progressRange = document.querySelector('#progressRange'), currentTimeLabel = document.querySelector('#currentTime'), durationLabel = document.querySelector('#duration'), resultsPanel = document.querySelector('#resultsPanel'), scoreValue = document.querySelector('#scoreValue'), percentageValue = document.querySelector('#percentageValue'), correctValue = document.querySelector('#correctValue'), incorrectValue = document.querySelector('#incorrectValue'), resultSummary = document.querySelector('#resultSummary'), restartButton = document.querySelector('#restartButton');
const totalDuration = scenes.reduce((total, scene) => total + scene.duration, 0); let sceneIndex = 0, sceneElapsed = 0, timelineTimer, lastTick, playing = false, assessmentOpen = false, selectedOption = null, answers = [];
function formatTime(seconds) { const safeSeconds = Math.max(0, Math.floor(seconds)); return `${String(Math.floor(safeSeconds / 60)).padStart(2, '0')}:${String(safeSeconds % 60).padStart(2, '0')}` }
function sceneMarkup(type) { if (type === 'phishing') return `<div class="laptop"><div class="laptop-screen"><div class="desktop-bar"><span>WORKSPACE</span><span class="desktop-dots"><i></i><i></i><i></i></span></div><div class="desktop-body"><div class="desktop-sidebar"></div><div class="desktop-card"></div><div class="email-notification"><span class="mail-icon">&#9993;</span><span><strong>New security message</strong><span>security-update@...</span></span></div><div class="email-window"><div class="window-bar"><span>New message</span><span>&#10005;</span></div><div class="email-body"><strong>Account verification required</strong><p>From: security-update@company-login.com</p><p>Your account requires verification.</p><span class="email-link">Verify Account</span></div><span class="fake-cursor">&#8598;</span></div></div></div><div class="laptop-base"></div></div>`; if (type === 'password') return `<div class="password-screen"><div class="login-card"><small>SECURE WORKSPACE</small><h3>Lindungi akun</h3><small>Pengelola kata sandi</small><div class="password-input"><span>&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;</span></div></div><div class="security-warning">&#9888; Kata sandi unik tersimpan</div></div>`; if (type === 'social') return `<div class="phone-scene"><div class="phone"><div class="phone-screen"><small>INCOMING MESSAGE</small><div class="phone-message"><strong>IT Support</strong>We need your verification code to secure your account.</div></div></div><div class="code-bubble">Kode OTP terkirim: 481 209</div></div>`; if (type === 'usb') return `<div class="usb-scene"><div class="usb-laptop"></div><div class="usb"></div><div class="usb-warning">&#9888; Unknown device detected</div></div>`; return `<div class="wifi-scene"><span class="wifi-label">HOTSPOT PRIBADI TERHUBUNG</span><span class="network-line"></span><div class="wifi-symbol"></div><div class="wifi-warning">&#9888; Koneksi diganti<br>Membuka sistem kantor</div></div>` }
function characterMarkup() {
    return `<div class="character-scene" role="img" aria-label="Animasi seorang pekerja duduk, membuka laptop, mengetik, dan menggunakan mouse di meja kerja">
    <svg viewBox="0 0 480 420" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <ellipse cx="236" cy="389" rx="204" ry="16" fill="#081313" opacity=".5"/>
        <rect x="30" y="35" width="136" height="137" rx="8" fill="#24413e"/>
        <path d="M98 39v129M34 101h128" stroke="#42615b" stroke-width="5"/>
        <circle cx="133" cy="69" r="18" fill="#e9cc89" opacity=".7"/>
        <path d="M40 160l40-38 35 20 42-24v44" fill="#385a50"/>
        <rect x="77" y="206" width="29" height="104" rx="14" fill="#334947"/>
        <rect x="83" y="296" width="129" height="19" rx="9" fill="#47615c"/>
        <path d="M139 313v62m-43 8 43-9 44 9" fill="none" stroke="#6b827a" stroke-width="9" stroke-linecap="round"/>
        <g class="actor-legs"><path d="M129 280l77 16-6 75m-45-86 72 9 12 75" fill="none" stroke="#283b50" stroke-width="27" stroke-linecap="round"/>
        <path d="M186 375h39m2 0h36" stroke="#b6c6bd" stroke-width="15" stroke-linecap="round"/></g>
        <g class="actor-body">
            <path d="M124 177q-28 15-19 61l9 50h65l-4-83q-4-32-28-31" fill="#65b9a3"/>
            <path d="M146 154v28" stroke="#d9a07c" stroke-width="22" stroke-linecap="round"/>
            <g class="actor-head">
                <path d="M118 100q-1-35 36-32 37 4 29 49l8 19-14 4q-5 31-34 24-31-10-25-64" fill="#e5b38c"/>
                <path d="M115 125q-20-50 15-61 32-13 53 11l-5 26-22-11-16 26-5 17" fill="#293230"/>
                <circle cx="135" cy="128" r="9" fill="#dba17e"/>
                <path class="actor-eye" d="M168 117h3" stroke="#283330" stroke-width="5" stroke-linecap="round"/>
                <path d="M167 146q8 4 13-1" fill="none" stroke="#9d614e" stroke-width="2.5" stroke-linecap="round"/>
            </g>
            <g class="actor-arm-back"><path d="M159 197l35 44 69-3" fill="none" stroke="#48917e" stroke-width="22" stroke-linecap="round"/><path d="M253 238l22 2" stroke="#dfaa83" stroke-width="14" stroke-linecap="round"/></g>
            <g class="actor-arm"><path d="M128 204l38 49 82-6" fill="none" stroke="#76cbb3" stroke-width="25" stroke-linecap="round"/><path d="M240 247l27 1" stroke="#edbd96" stroke-width="15" stroke-linecap="round"/></g>
        </g>
        <path d="M194 270v114m214-114v114" stroke="#7c6960" stroke-width="12"/>
        <rect x="175" y="258" width="270" height="15" rx="5" fill="#c49a77"/>
        <rect x="258" y="248" width="128" height="9" rx="4" fill="#a7bab4"/>
        <g class="actor-lid"><path d="M287 247l15-82h100l-18 82z" fill="#53736e" stroke="#a4bbb1" stroke-width="4"/><circle cx="349" cy="207" r="8" fill="#9dd7c5"/></g>
        <ellipse cx="253" cy="254" rx="13" ry="4" fill="#d0ded6"/>
        <path d="M414 219v33h19v-33z" fill="#e7c8a0"/><path d="M433 224q18 0 0 17" fill="none" stroke="#e7c8a0" stroke-width="5"/>
        <g class="actor-mail"><rect x="328" y="112" width="51" height="36" rx="7" fill="#72dec3"/><path d="m335 121 19 14 18-14" fill="none" stroke="#1e5146" stroke-width="3"/><circle cx="377" cy="113" r="7" fill="#f2bd67"/></g>
    </svg><span class="character-status" id="characterStatus"></span></div>`;
}
function renderScene() {
    const scene = scenes[sceneIndex];
    sceneContent.innerHTML = characterMarkup() + (sceneIndex === 0 ? '<div class="intro-login"><h3>Masuk ke akun kerja</h3><p>&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</p><strong>Berhasil masuk &#10003;</strong></div>' : '') + sceneMarkup(scene.type) + '<div class="action-caption">' + scene.action + '</div>';
    sceneCaption.textContent = `${String(scene.id).padStart(2, '0')} / ${scene.title}`;
    durationLabel.textContent = formatTime(totalDuration);
    sceneStage.dataset.scene = scene.type;
    syncAnimations();
}
function syncAnimations() {
    const status = document.querySelector('#characterStatus');
    if (status) status.textContent = sceneIndex === 0
        ? (sceneElapsed < 2 ? 'Memulai hari di meja kerja' : sceneElapsed < 4 ? 'Membuka laptop' : sceneElapsed < 8 ? 'Mengetik dan login' : sceneElapsed < 12 ? 'Membaca email masuk' : 'Mengeklik tautan dalam email')
        : scenes[sceneIndex].title;
 sceneContent.getAnimations({ subtree: true }).forEach(animation => { animation.pause(); animation.currentTime = sceneElapsed * 1000 }); }
function updateTimeline() { const completedDuration = scenes.slice(0, sceneIndex).reduce((total, scene) => total + scene.duration, 0), currentTime = completedDuration + sceneElapsed; currentTimeLabel.textContent = formatTime(currentTime); progressRange.value = (currentTime / totalDuration) * 100 }
function tick(timestamp) { if (!playing) return; if (!lastTick) lastTick = timestamp; const delta = Math.min((timestamp - lastTick) / 1000, .1); lastTick = timestamp; sceneElapsed += delta; narrateScene(); syncAnimations(); updateTimeline(); const scene = scenes[sceneIndex]; if (sceneElapsed >= scene.assessmentAt && !answers.some(answer => answer.sceneId === scene.id)) { sceneElapsed = scene.assessmentAt; pause(); syncAnimations(); updateTimeline(); openAssessment(scene); return } if (sceneElapsed >= scene.duration) advanceScene() }
function narrateScene() {
    const scene = scenes[sceneIndex];
    const cues = sceneIndex === 0
        ? [[0, 'Seorang karyawan duduk di meja.'], [3, 'Ia membuka laptop.'], [6, 'Ia masuk ke akun kerja.'], [9, 'Sebuah email baru masuk.'], [12.5, 'Ia mengeklik tautan mencurigakan.']]
        : [[0, scene.title + '.'], [3, scene.action]];
    const cue = cues.filter(([at]) => sceneElapsed >= at).pop();
    if (cue) narration.cue(`${scene.id}:${cue[0]}`, cue[1]);
}
function play() { if (assessmentOpen || !resultsPanel.hidden) return; playing = true; narration.resume(); narrateScene(); lastTick = null; playButton.innerHTML = '&#10074;&#10074;'; playButton.setAttribute('aria-label', 'Jeda animasi'); clearInterval(timelineTimer); timelineTimer = setInterval(() => tick(performance.now()), 50) }
function pause() { narration.pause(); playing = false; lastTick = null; clearInterval(timelineTimer); playButton.innerHTML = '&#9654;'; playButton.setAttribute('aria-label', 'Putar animasi') }
function advanceScene() { if (sceneIndex >= scenes.length - 1) { finishAssessment(); return } narration.stop(); sceneIndex += 1; sceneElapsed = 0; lastTick = null; renderScene(); narrateScene() }
function openAssessment(scene) { assessmentOpen = true; selectedOption = null; questionCount.textContent = `Pertanyaan ${scene.id} dari ${scenes.length}`; questionText.textContent = scene.question; document.querySelector('#questionContext').textContent = scene.action; answerList.innerHTML = ''; feedback.hidden = true; feedback.classList.remove('is-wrong'); continueButton.disabled = true; scene.options.forEach((option, index) => { const button = document.createElement('button'); button.type = 'button'; button.className = 'answer-option'; button.setAttribute('aria-pressed', 'false'); button.innerHTML = `<span class="answer-index">${String.fromCharCode(65 + index)}</span><span>${option.text}</span>`; button.addEventListener('click', () => selectAnswer(option, button)); answerList.appendChild(button) }); assessmentOverlay.hidden = false; sceneStage.classList.add('is-assessment'); playButton.disabled = true; progressRange.disabled = true; answerList.querySelector('button').focus(); narration.say(scene.question) }
function selectAnswer(option, button) { if (selectedOption) return; selectedOption = option; button.classList.add('selected'); button.setAttribute('aria-pressed', 'true'); answerList.querySelectorAll('.answer-option').forEach(item => { item.disabled = true }); feedback.hidden = false; feedback.classList.toggle('is-wrong', !option.correct); feedbackIcon.innerHTML = option.correct ? '&#10003;' : '&#33;'; feedbackTitle.textContent = option.correct ? 'Benar.' : 'Kurang tepat.'; feedbackText.textContent = (scenes[sceneIndex].safe ? 'Tindakan tersebut aman. ' : 'Tindakan tersebut berisiko. ') + scenes[sceneIndex].explanation; continueButton.disabled = false; continueButton.focus(); narration.say(feedbackTitle.textContent + " " + feedbackText.textContent) }
function continueFromAssessment() {
    if (!assessmentOpen || !selectedOption) return;
    answers.push({ sceneId: scenes[sceneIndex].id, correct: selectedOption.correct });
    assessmentOpen = false; selectedOption = null; assessmentOverlay.hidden = true;
    sceneStage.classList.remove('is-assessment'); playButton.disabled = false;
    narration.stop(); playButton.focus(); play();
}
function finishAssessment() { pause(); const correct = answers.filter(answer => answer.correct).length, percentage = Math.round(correct / scenes.length * 100); scoreValue.textContent = `${correct} / ${scenes.length}`; percentageValue.textContent = `${percentage}%`; correctValue.textContent = correct; incorrectValue.textContent = answers.length - correct; resultSummary.textContent = percentage >= 80 ? 'Pemahamanmu sudah baik. Tetap periksa permintaan sebelum bertindak dan laporkan hal yang mencurigakan.' : 'Terus berlatih mengenali risiko. Periksa pengirim, jaga kerahasiaan akun, dan gunakan kanal resmi untuk konfirmasi.'; resultsPanel.hidden = false; playButton.disabled = true; updateTimeline(); restartButton.focus(); narration.say(`Latihan selesai. Kamu menjawab ${correct} dari ${scenes.length} pertanyaan dengan benar. ` + resultSummary.textContent) }
function restart() { pause(); narration.reset(); sceneIndex = 0; sceneElapsed = 0; answers = []; assessmentOpen = false; selectedOption = null; assessmentOverlay.hidden = true; resultsPanel.hidden = true; sceneStage.classList.remove('is-assessment'); playButton.disabled = false; progressRange.disabled = true; renderScene(); updateTimeline(); play() }
playButton.addEventListener('click', () => playing ? pause() : play()); continueButton.addEventListener('click', continueFromAssessment); restartButton.addEventListener('click', restart); fullscreenButton.addEventListener('click', () => document.fullscreenElement ? document.exitFullscreen() : document.querySelector('#videoPlayer').requestFullscreen?.()); renderScene(); updateTimeline();
