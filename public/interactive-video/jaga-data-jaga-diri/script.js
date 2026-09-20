/* ============================================
   SECURITY AWARENESS TRAINING — SCRIPT
   Interactive Animated Training Experience
   ============================================ */

// ============================================
// SCENARIO DATA — separated from rendering logic
// ============================================
const SCENARIOS = [
  {
    id: 'phishing',
    title: 'Suspicious Email',
    question: 'APA YANG HARUS RAKA LAKUKAN?',
    setup: 'phishing',
    options: [
      {
        letter: 'A',
        text: 'Klik link dan login secepatnya.',
        type: 'risky',
        score: 0,
        statusLabel: 'BERISIKO TINGGI',
        explanation: 'Phishing sering menggunakan rasa panik dan urgensi agar korban bertindak tanpa memeriksa sumber pesan.',
        consequence: 'Raka mengklik link tersebut dan memasukkan kredensialnya. Data berhasil dicuri oleh penyerang.',
        points: [
          'Jangan langsung klik link.',
          'Periksa domain pengirim.',
          'Jangan memasukkan password pada halaman yang mencurigakan.'
        ],
        recommended: 'Selalu verifikasi keaslian email sebelum bertindak.'
      },
      {
        letter: 'B',
        text: 'Periksa alamat pengirim dan arahkan cursor ke link untuk melihat tujuan URL.',
        type: 'safe',
        score: 25,
        statusLabel: 'PILIHAN AMAN',
        explanation: 'Memeriksa alamat pengirim dan tujuan link membantu mendeteksi phishing sebelum kredensial diberikan kepada penyerang.',
        consequence: 'Raka menemukan bahwa pengirim adalah security@company-support.xyz — bukan domain resmi perusahaan.',
        points: [
          'Selalu periksa alamat pengirim.',
          'Hover link sebelum klik untuk melihat URL asli.',
          'Laporkan email mencurigakan ke tim IT/Security.'
        ],
        recommended: 'Laporkan email tersebut ke tim keamanan IT perusahaan.'
      },
      {
        letter: 'C',
        text: 'Forward email tersebut ke teman untuk bertanya apakah emailnya asli.',
        type: 'partial',
        score: 10,
        statusLabel: 'LEBIH BAIK, TAPI BELUM IDEAL',
        explanation: 'Meskipun bertanya adalah langkah lebih baik daripada langsung klik, forward email mencurigakan justru dapat menyebarkan link berbahaya ke lebih banyak orang.',
        consequence: 'Teman Raka juga menerima link berbahaya dan mungkin mengkliknya.',
        points: [
          'Jangan forward email mencurigakan.',
          'Gunakan channel resmi untuk melaporkan.',
          'Hubungi tim IT/Security langsung.'
        ],
        recommended: 'Laporkan melalui channel IT/Security resmi, bukan ke rekan kerja.'
      }
    ]
  },
  {
    id: 'usb',
    title: 'Unknown USB',
    question: 'Kamu menemukan USB yang tidak dikenal. Apa yang harus dilakukan?',
    setup: 'usb',
    options: [
      {
        letter: 'A',
        text: 'Colokkan ke komputer untuk melihat isinya.',
        type: 'risky',
        score: 0,
        statusLabel: 'BERISIKO TINGGI',
        explanation: 'USB yang tidak dikenal dapat mengandung malware yang otomatis berjalan ketika perangkat terhubung.',
        consequence: 'Malware dari USB menyebar ke workstation Raka dan menginfeksi jaringan perusahaan.',
        points: [
          'Jangan pernah colokkan USB yang tidak dikenal.',
          'Malware USB bisa berjalan otomatis (autorun).',
          'Serangan "USB drop" adalah teknik social engineering umum.'
        ],
        recommended: 'Serahkan perangkat mencurigakan ke tim IT/Security.'
      },
      {
        letter: 'B',
        text: 'Serahkan ke tim IT/Security.',
        type: 'safe',
        score: 25,
        statusLabel: 'PILIHAN AMAN',
        explanation: 'Tim IT/Security memiliki prosedur dan alat untuk memeriksa perangkat yang tidak dikenal secara aman tanpa membahayakan jaringan.',
        consequence: 'Tim keamanan memeriksa USB di lingkungan terisolasi dan menemukan malware di dalamnya.',
        points: [
          'Selalu ikuti prosedur perusahaan.',
          'Tim IT memiliki sandbox untuk inspeksi aman.',
          'Melaporkan temuan adalah tanggung jawab semua karyawan.'
        ],
        recommended: 'Ikuti prosedur perusahaan untuk penanganan perangkat asing.'
      },
      {
        letter: 'C',
        text: 'Bawa pulang karena mungkin masih bisa digunakan.',
        type: 'partial',
        score: 0,
        statusLabel: 'TIDAK DISARANKAN',
        explanation: 'Perangkat penyimpanan yang tidak dikenal tidak boleh digunakan di perangkat pribadi maupun perusahaan karena risiko malware.',
        consequence: 'USB tersebut ternyata mengandung malware yang bisa menginfeksi perangkat pribadi Raka.',
        points: [
          'Jangan gunakan perangkat penyimpanan asing.',
          'Risiko tidak hanya untuk perusahaan tapi juga perangkat pribadi.',
          'Serahkan ke pihak yang berwenang.'
        ],
        recommended: 'Jangan gunakan perangkat asing — serahkan ke IT.'
      }
    ]
  },
  {
    id: 'tailgating',
    title: 'Tailgating',
    question: 'APA YANG HARUS RAKA LAKUKAN?',
    setup: 'tailgating',
    options: [
      {
        letter: 'A',
        text: 'Biarkan masuk karena terlihat seperti pegawai.',
        type: 'risky',
        score: 0,
        statusLabel: 'BERISIKO TINGGI',
        explanation: 'Tailgating adalah teknik akses fisik tanpa otorisasi di mana seseorang mengikuti karyawan yang sah melewati pintu yang aman.',
        consequence: 'Orang tersebut ternyata bukan karyawan dan berhasil mengakses area sensitif perusahaan.',
        points: [
          'Jangan biarkan orang tanpa akses masuk.',
          'Penampilan bukan bukti identitas.',
          'Tailgating adalah ancaman keamanan fisik nyata.'
        ],
        recommended: 'Selalu minta verifikasi identitas resmi.'
      },
      {
        letter: 'B',
        text: 'Minta orang tersebut menggunakan prosedur akses resmi atau menghubungi security.',
        type: 'safe',
        score: 25,
        statusLabel: 'PILIHAN AMAN',
        explanation: 'Identitas seseorang tidak boleh diasumsikan hanya berdasarkan pakaian, wajah, atau klaim bahwa mereka bekerja di perusahaan.',
        consequence: 'Raka mengarahkan orang tersebut ke resepsionis untuk verifikasi identitas.',
        points: [
          'Verifikasi identitas adalah prosedur standar.',
          'Bersikap sopan tapi tetap mengikuti aturan.',
          'Keamanan fisik sama pentingnya dengan keamanan digital.'
        ],
        recommended: 'Arahkan ke prosedur akses resmi atau hubungi security.'
      },
      {
        letter: 'C',
        text: 'Tanyakan nama saja lalu biarkan masuk.',
        type: 'partial',
        score: 10,
        statusLabel: 'BELUM CUKUP',
        explanation: 'Mengetahui nama seseorang bukan merupakan autentikasi yang memadai. Siapapun bisa mengklaim identitas palsu.',
        consequence: 'Orang tersebut memberikan nama palsu dan tetap berhasil masuk.',
        points: [
          'Nama bukan bukti identitas.',
          'Gunakan kartu akses atau verifikasi resmi.',
          'Social engineering sering menggunakan kepercayaan.'
        ],
        recommended: 'Gunakan metode verifikasi resmi, bukan pertanyaan informal.'
      }
    ]
  },
  {
    id: 'password',
    title: 'Password Security',
    question: 'Password mana yang lebih aman?',
    setup: 'password',
    options: [
      {
        letter: 'A',
        text: 'raka123',
        type: 'risky',
        score: 0,
        statusLabel: 'SANGAT LEMAH',
        explanation: 'Password ini sangat mudah ditebak karena menggunakan nama pengguna dan angka berurutan yang umum.',
        consequence: 'Password ini bisa dicrack dalam hitungan detik oleh brute-force attack.',
        points: [
          'Jangan gunakan nama pribadi dalam password.',
          'Angka berurutan (123) sangat umum dan mudah ditebak.',
          'Password pendek sangat rentan terhadap serangan.'
        ],
        recommended: 'Gunakan password yang panjang, unik, dan tidak mengandung informasi pribadi.'
      },
      {
        letter: 'B',
        text: 'perusahaan2026',
        type: 'partial',
        score: 0,
        statusLabel: 'MASIH LEMAH',
        explanation: 'Meskipun lebih panjang, password ini menggunakan kata yang mudah ditebak dan tahun yang bisa diprediksi.',
        consequence: 'Password ini rentan terhadap serangan dictionary attack.',
        points: [
          'Jangan gunakan kata-kata umum atau nama perusahaan.',
          'Tahun bukan pengganti karakter khusus.',
          'Dictionary attack dapat menebak kombinasi seperti ini.'
        ],
        recommended: 'Hindari kata-kata yang berhubungan langsung dengan konteks kerja.'
      },
      {
        letter: 'C',
        text: 'Kopi!Langit#Kereta92',
        type: 'partial',
        score: 10,
        statusLabel: 'CUKUP KUAT',
        explanation: 'Password ini menggunakan kombinasi kata acak, simbol, dan angka yang membuatnya lebih sulit ditebak.',
        consequence: 'Password ini jauh lebih aman, tetapi mengingat banyak password unik tanpa alat bantu tetap sulit.',
        points: [
          'Kombinasi kata acak lebih kuat dari kata tunggal.',
          'Simbol dan angka meningkatkan kompleksitas.',
          'Passphrase lebih mudah diingat daripada string acak.'
        ],
        recommended: 'Ini bagus, tapi gunakan password manager untuk mengelola banyak password.'
      },
      {
        letter: 'D',
        text: 'Menggunakan password manager untuk membuat password unik dan panjang.',
        type: 'safe',
        score: 25,
        statusLabel: 'PILIHAN TERBAIK',
        explanation: 'Password manager menghasilkan dan menyimpan password yang sangat kuat dan unik untuk setiap akun, menghilangkan kebutuhan untuk mengingat banyak password.',
        consequence: 'Setiap akun Raka terlindungi dengan password unik dan sangat kuat.',
        points: [
          'Password manager menghasilkan password yang benar-benar acak.',
          'Setiap akun mendapat password unik — tidak ada reuse.',
          'Aktifkan MFA (Multi-Factor Authentication) sebagai lapisan tambahan.',
          'Password manager juga melindungi dari phishing karena auto-fill hanya bekerja di domain yang benar.'
        ],
        recommended: 'Gunakan password manager dan aktifkan MFA di semua akun penting.'
      }
    ]
  }
];

// Scene timeline definition
const SCENE_TIMELINE = [
  { id: 'intro',      startTime: 0,   duration: 15, interactive: false },
  { id: 'phishing',   startTime: 20,  duration: 30, interactive: true },
  { id: 'usb',        startTime: 50,  duration: 30, interactive: true },
  { id: 'tailgating', startTime: 80,  duration: 30, interactive: true },
  { id: 'password',   startTime: 110, duration: 30, interactive: true },
  { id: 'ending',     startTime: 140, duration: 0,  interactive: false }
];

const TOTAL_DURATION = 140; // seconds

// ============================================
// VOICE CONTROLLER — Web Speech API
// ============================================
class VoiceController {
  constructor() {
    this.synth = window.speechSynthesis;
    this.voice = null;
    this.enabled = true;
    this._loadVoice();
  }

  _loadVoice() {
    const setVoice = () => {
      const voices = this.synth.getVoices();
      // Try Indonesian male voice first (for Raka)
      this.voice = voices.find(v => v.lang.startsWith('id') && v.name.toLowerCase().includes('male')) ||
                   voices.find(v => v.lang.startsWith('id')) ||
                   voices.find(v => v.lang.startsWith('ms')) ||
                   voices.find(v => v.lang === 'en-US') ||
                   voices[0];
    };
    setVoice();
    if (this.synth.onvoiceschanged !== undefined) {
      this.synth.onvoiceschanged = setVoice;
    }
  }

  speak(text, { rate = 1, pitch = 1, volume = 0.9 } = {}) {
    return new Promise(resolve => {
      if (!this.enabled || !this.synth) { resolve(); return; }
      this.synth.cancel();
      const utt = new SpeechSynthesisUtterance(text);
      if (this.voice) utt.voice = this.voice;
      utt.lang = 'id-ID';
      utt.rate = rate;
      utt.pitch = pitch;
      utt.volume = volume;
      utt.onend = () => resolve();
      utt.onerror = () => resolve();
      this.synth.speak(utt);
      const maxDuration = Math.max(text.length * 100, 5000);
      setTimeout(() => { this.synth.cancel(); resolve(); }, maxDuration);
    });
  }

  stop() {
    if (this.synth) this.synth.cancel();
  }

  toggle() {
    this.enabled = !this.enabled;
    if (!this.enabled) this.stop();
    return this.enabled;
  }
}

// ============================================
// SCORE MANAGER
// ============================================
class ScoreManager {
  constructor() {
    this.score = 0;
    this.el = document.getElementById('score-value');
    this.container = document.getElementById('score-display');
  }

  add(points) {
    this.score += points;
    this.el.textContent = this.score;
    // Float popup
    this._showPopup(points);
  }

  _showPopup(points) {
    const popup = document.createElement('span');
    popup.className = `score-popup ${points > 10 ? 'positive' : 'neutral'}`;
    popup.textContent = points > 0 ? `+${points}` : `${points}`;
    this.container.appendChild(popup);
    setTimeout(() => popup.remove(), 1500);
  }

  getScore() { return this.score; }

  reset() {
    this.score = 0;
    this.el.textContent = '0';
  }
}

// ============================================
// TIMELINE CONTROLLER
// ============================================
class TimelineController {
  constructor(totalDuration) {
    this.totalDuration = totalDuration;
    this.currentTime = 0;
    this.progressEl = document.querySelector('.timeline-progress');
    this.currentTimeEl = document.getElementById('timeline-current');
    this.markers = {};
    this._interval = null;
    this._paused = false;
  }

  init(scenes) {
    const track = document.querySelector('.timeline-track');
    scenes.forEach(scene => {
      if (!scene.interactive) return;
      const pct = (scene.startTime / this.totalDuration) * 100;
      const marker = document.createElement('div');
      marker.className = 'timeline-marker';
      marker.style.left = `${pct}%`;
      marker.dataset.sceneId = scene.id;

      const label = document.createElement('span');
      label.className = 'timeline-marker-label';
      // Capitalize first letter
      const scenario = SCENARIOS.find(s => s.id === scene.id);
      label.textContent = scenario ? scenario.title : scene.id;
      marker.appendChild(label);

      track.appendChild(marker);
      this.markers[scene.id] = marker;
    });
  }

  start() {
    this._paused = false;
    this._interval = setInterval(() => {
      if (this._paused) return;
      this.currentTime += 0.1;
      this._update();
    }, 100);
  }

  pause() {
    this._paused = true;
  }

  resume() {
    this._paused = false;
  }

  jumpTo(time) {
    this.currentTime = time;
    this._update();
  }

  setMarkerActive(sceneId) {
    Object.values(this.markers).forEach(m => m.classList.remove('active'));
    if (this.markers[sceneId]) this.markers[sceneId].classList.add('active');
  }

  setMarkerCompleted(sceneId) {
    if (this.markers[sceneId]) {
      this.markers[sceneId].classList.remove('active');
      this.markers[sceneId].classList.add('completed');
    }
  }

  _update() {
    const pct = Math.min((this.currentTime / this.totalDuration) * 100, 100);
    this.progressEl.style.width = `${pct}%`;
    this.currentTimeEl.textContent = this._formatTime(this.currentTime);
  }

  _formatTime(secs) {
    const m = Math.floor(secs / 60);
    const s = Math.floor(secs % 60);
    return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;
  }

  stop() {
    clearInterval(this._interval);
  }

  reset() {
    this.stop();
    this.currentTime = 0;
    this._update();
    Object.values(this.markers).forEach(m => {
      m.classList.remove('active', 'completed');
    });
  }
}

// ============================================
// CHARACTER CONTROLLER
// ============================================
class CharacterController {
  constructor(elementId) {
    this.el = document.getElementById(elementId);
    this.currentState = 'idle';
    this.speechBubble = document.getElementById('raka-speech');
  }

  setState(state) {
    // Remove all state classes
    const states = ['idle', 'walking', 'typing', 'thinking', 'alert', 'warning', 'success', 'panic'];
    states.forEach(s => this.el.classList.remove(`state-${s}`));
    this.el.classList.add(`state-${state}`);
    this.currentState = state;

    // Update SVG face expression
    this._updateExpression(state);
  }

  moveTo(leftPercent, bottomPercent) {
    return new Promise(resolve => {
      this.el.style.left = `${leftPercent}%`;
      if (bottomPercent !== undefined) this.el.style.bottom = `${bottomPercent}%`;
      setTimeout(resolve, 800);
    });
  }

  speak(text, duration = 3000) {
    return new Promise(resolve => {
      const nameEl = this.speechBubble.querySelector('.speaker-name');
      const textEl = this.speechBubble.querySelector('.bubble-text');
      if (nameEl) nameEl.textContent = 'Raka';
      if (textEl) textEl.textContent = text;

      // Position bubble above character
      this.speechBubble.style.left = this.el.style.left || '48%';
      this.speechBubble.style.bottom = '75%';
      this.speechBubble.classList.add('visible');

      setTimeout(() => {
        this.speechBubble.classList.remove('visible');
        setTimeout(resolve, 400);
      }, duration);
    });
  }

  hideSpeech() {
    this.speechBubble.classList.remove('visible');
  }

  _updateExpression(state) {
    const mouth = this.el.querySelector('.raka-mouth');
    const brows = this.el.querySelectorAll('.raka-brow');
    if (!mouth) return;

    switch(state) {
      case 'idle':
      case 'success':
        mouth.setAttribute('d', 'M39,42 Q45,48 51,42'); // smile
        brows.forEach(b => b.setAttribute('transform', ''));
        break;
      case 'thinking':
        mouth.setAttribute('d', 'M40,44 Q45,44 50,44'); // neutral line
        break;
      case 'alert':
      case 'warning':
        mouth.setAttribute('d', 'M39,46 Q45,42 51,46'); // slight frown
        break;
      case 'panic':
        mouth.setAttribute('d', 'M39,42 Q45,50 51,42'); // open mouth (O shape)
        break;
      default:
        mouth.setAttribute('d', 'M39,42 Q45,48 51,42');
    }
  }

  reset() {
    this.setState('idle');
    this.el.style.left = '48%';
    this.el.style.bottom = '22%';
    this.hideSpeech();
  }
}

// ============================================
// DECISION MANAGER
// ============================================
class DecisionManager {
  constructor() {
    this.overlay = document.getElementById('decision-overlay');
    this.questionEl = this.overlay.querySelector('.decision-question');
    this.optionsContainer = this.overlay.querySelector('.decision-options');
    this.explanationPanel = document.getElementById('explanation-panel');
    this.selectedOption = null;
  }

  show(scenario) {
    return new Promise(resolve => {
      this._resolve = resolve;
      this.selectedOption = null;
      this.questionEl.textContent = scenario.question;
      this.optionsContainer.innerHTML = '';

      scenario.options.forEach((opt, idx) => {
        const btn = document.createElement('button');
        btn.className = 'decision-btn';
        btn.setAttribute('role', 'button');
        btn.setAttribute('tabindex', '0');
        btn.setAttribute('aria-label', `Opsi ${opt.letter}: ${opt.text}`);
        btn.id = `option-${scenario.id}-${opt.letter}`;
        btn.innerHTML = `
          <span class="option-letter">${opt.letter}</span>
          <span class="option-text">${opt.text}</span>
        `;
        btn.addEventListener('click', () => this._selectOption(scenario, opt, btn));
        // Keyboard support
        btn.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this._selectOption(scenario, opt, btn);
          }
        });
        this.optionsContainer.appendChild(btn);
      });

      this.overlay.classList.add('visible');
      // Focus first option
      setTimeout(() => {
        const firstBtn = this.optionsContainer.querySelector('.decision-btn');
        if (firstBtn) firstBtn.focus();
      }, 300);
    });
  }

  _selectOption(scenario, option, btnEl) {
    // Mark all buttons with their type class
    const allBtns = this.optionsContainer.querySelectorAll('.decision-btn');
    allBtns.forEach((b, i) => {
      const opt = scenario.options[i];
      b.classList.add(opt.type === 'safe' ? 'correct' : opt.type === 'risky' ? 'risky' : 'partial');
    });
    btnEl.classList.add('selected');
    this.selectedOption = option;

    // Show explanation after short delay
    setTimeout(() => {
      this._showExplanation(option);
    }, 600);
  }

  _showExplanation(option) {
    const card = this.explanationPanel.querySelector('.explanation-card');
    card.innerHTML = `
      <span class="explanation-status ${option.type}">${option.statusLabel}</span>
      <h3 class="explanation-title">Kenapa?</h3>
      <p class="explanation-text">${option.explanation}</p>
      <p class="explanation-text" style="font-style:italic; color: var(--text-muted);">
        <strong>Konsekuensi:</strong> ${option.consequence}
      </p>
      <ul class="explanation-points">
        ${option.points.map(p => `<li>${p}</li>`).join('')}
      </ul>
      <p class="explanation-text"><strong>Rekomendasi:</strong> ${option.recommended}</p>
      <div class="explanation-actions">
        <button class="btn btn-primary" id="btn-continue-scenario">Lanjutkan Skenario</button>
      </div>
    `;

    this.explanationPanel.classList.add('visible');

    document.getElementById('btn-continue-scenario').addEventListener('click', () => {
      this.explanationPanel.classList.remove('visible');
      this.overlay.classList.remove('visible');
      if (this._resolve) {
        this._resolve(this.selectedOption);
        this._resolve = null;
      }
    });

    // Focus continue button
    setTimeout(() => {
      const contBtn = document.getElementById('btn-continue-scenario');
      if (contBtn) contBtn.focus();
    }, 300);
  }

  hide() {
    this.overlay.classList.remove('visible');
    this.explanationPanel.classList.remove('visible');
  }
}

// ============================================
// SCENE MANAGER — orchestrates everything
// ============================================
class SceneManager {
  constructor() {
    this.character = new CharacterController('character-raka');
    this.decisions = new DecisionManager();
    this.score = new ScoreManager();
    this.timeline = new TimelineController(TOTAL_DURATION);
    this.voice = new VoiceController();
    this.currentSceneIndex = -1;
    this.isRunning = false;

    // DOM refs
    this.stage = document.getElementById('scene-stage');
    this.titleOverlay = document.getElementById('title-overlay');
    this.endingOverlay = document.getElementById('ending-overlay');
    this.startScreen = document.getElementById('start-screen');
    this.progressEl = document.getElementById('progress-text');
    this.sceneTransition = document.getElementById('scene-transition');
    this.consequenceOverlay = document.getElementById('consequence-overlay');

    // Environment element references
    this.emailPopup = document.getElementById('email-popup');
    this.usbCloseup = document.getElementById('usb-closeup');
    this.strangerEl = document.getElementById('character-stranger');
    this.strangerBubble = document.getElementById('stranger-bubble');
    this.passwordScreen = document.getElementById('password-screen');
    this.notificationBell = document.getElementById('notification-bell');

    this.timeline.init(SCENE_TIMELINE);

    // Voice toggle button
    const voiceBtn = document.getElementById('btn-voice-toggle');
    if (voiceBtn) {
      voiceBtn.addEventListener('click', () => {
        const on = this.voice.toggle();
        voiceBtn.textContent = on ? '🔊' : '🔇';
      });
    }
  }

  async start() {
    this.isRunning = true;
    this.startScreen.classList.add('hidden');
    this.timeline.start();
    await this._wait(500);
    await this.playScene(0); // intro
  }

  async playScene(index) {
    if (index >= SCENE_TIMELINE.length) return;
    this.currentSceneIndex = index;
    const scene = SCENE_TIMELINE[index];

    this.timeline.jumpTo(scene.startTime);
    this._updateProgress(index);

    if (scene.id === 'intro') {
      await this._playIntro();
    } else if (scene.id === 'ending') {
      await this._playEnding();
    } else {
      const scenario = SCENARIOS.find(s => s.id === scene.id);
      if (scenario) {
        await this._playInteractiveScene(scenario, scene);
      }
    }
  }

  async nextScene() {
    await this._transitionScene();
    await this.playScene(this.currentSceneIndex + 1);
  }

  async _playIntro() {
    // Show title
    this.titleOverlay.classList.add('visible');
    this.character.setState('walking');
    await this.character.moveTo(10);
    await this._wait(800);
    await this.character.moveTo(30);
    await this._wait(500);
    this.character.setState('idle');
    await this._wait(500);

    // Hide title, character speaks
    this.titleOverlay.classList.remove('visible');
    await this._wait(300);

    const introText = 'Hari ini tugas kita sederhana: menjaga data dan sistem perusahaan tetap aman.';
    this.character.speak(introText, 5000);
    await this.voice.speak(introText, { rate: 1.05, pitch: 0.9 });

    await this._wait(500);
    await this.character.moveTo(48);
    this.character.setState('idle');
    await this._wait(500);

    // Move to first scenario
    await this.nextScene();
  }

  async _playInteractiveScene(scenario, scene) {
    this.timeline.setMarkerActive(scenario.id);

    // Setup scene visuals
    await this._setupSceneVisuals(scenario);

    // Pause timeline
    this.timeline.pause();

    // Show decision
    const chosenOption = await this.decisions.show(scenario);

    // Voice reads explanation
    this.voice.speak(chosenOption.explanation, { rate: 1 });

    // Add score
    this.score.add(chosenOption.score);

    // Play consequence animation
    await this._playConsequence(scenario, chosenOption);

    // Mark completed
    this.timeline.setMarkerCompleted(scenario.id);
    this.timeline.resume();

    // Clean up scene visuals
    await this._cleanupSceneVisuals(scenario);

    await this._wait(500);

    // Move to next scene
    await this.nextScene();
  }

  async _setupSceneVisuals(scenario) {
    switch (scenario.id) {
      case 'phishing':
        this.character.setState('typing');
        await this.character.moveTo(30);
        await this._wait(600);
        // Show notification bell
        this.notificationBell.classList.add('visible');
        await this._wait(800);
        this.notificationBell.classList.remove('visible');
        // Show email popup
        this.emailPopup.classList.add('visible');
        await this._wait(500);
        this.character.setState('thinking');
        await this._wait(800);
        break;

      case 'usb':
        this.character.setState('walking');
        await this.character.moveTo(55);
        await this._wait(600);
        this.character.setState('idle');
        await this._wait(300);
        // Show USB close-up
        this.usbCloseup.classList.add('visible');
        await this._wait(500);
        this.character.setState('thinking');
        await this._wait(500);
        break;

      case 'tailgating':
        this.character.setState('walking');
        await this.character.moveTo(62);
        await this._wait(600);
        this.character.setState('idle');
        await this._wait(300);
        // Show stranger approaching
        this.strangerEl.classList.add('visible');
        await this._wait(800);
        this.strangerBubble.classList.add('visible');
        await this._wait(500);
        this.character.setState('thinking');
        break;

      case 'password':
        this.character.setState('walking');
        await this.character.moveTo(30);
        await this._wait(600);
        this.character.setState('typing');
        await this._wait(400);
        // Show password screen
        this.passwordScreen.classList.add('visible');
        await this._wait(500);
        this.character.setState('thinking');
        break;
    }
  }

  async _playConsequence(scenario, option) {
    switch (option.type) {
      case 'risky':
        this.character.setState('panic');
        this._flashEffect('red');
        if (scenario.id === 'phishing') {
          await this._showAttackerAnimation();
        } else if (scenario.id === 'usb') {
          await this._showMalwareAnimation();
        } else {
          await this._wait(1200);
        }
        break;

      case 'safe':
        this.character.setState('success');
        this._flashEffect('green');
        await this._showShieldAnimation();
        break;

      case 'partial':
        this.character.setState('warning');
        await this._wait(1200);
        break;
    }

    await this._wait(400);
    this.character.setState('idle');
  }

  async _cleanupSceneVisuals(scenario) {
    switch (scenario.id) {
      case 'phishing':
        this.emailPopup.classList.remove('visible');
        break;
      case 'usb':
        this.usbCloseup.classList.remove('visible');
        break;
      case 'tailgating':
        this.strangerEl.classList.remove('visible');
        this.strangerBubble.classList.remove('visible');
        break;
      case 'password':
        this.passwordScreen.classList.remove('visible');
        break;
    }
    // Clean consequence overlay
    this.consequenceOverlay.innerHTML = '';
  }

  async _playEnding() {
    this.timeline.pause();
    this.timeline.jumpTo(TOTAL_DURATION);

    const finalScore = this.score.getScore();
    let rank, rankClass, rankLabel;

    if (finalScore >= 80) {
      rank = 'Security Guardian';
      rankClass = 'rank-guardian';
      rankLabel = '🛡️ Security Guardian';
    } else if (finalScore >= 50) {
      rank = 'Security Learner';
      rankClass = 'rank-learner';
      rankLabel = '📚 Security Learner';
    } else {
      rank = 'Needs Improvement';
      rankClass = 'rank-needs-improvement';
      rankLabel = '⚠️ Security Awareness Needs Improvement';
    }

    // Populate ending overlay
    const scoreEl = this.endingOverlay.querySelector('.ending-score');
    const rankEl = this.endingOverlay.querySelector('.ending-rank');
    scoreEl.textContent = finalScore;
    scoreEl.style.color = finalScore >= 80 ? 'var(--accent-green)' : finalScore >= 50 ? 'var(--accent-yellow)' : 'var(--accent-red)';
    rankEl.textContent = rankLabel;
    rankEl.className = `ending-rank ${rankClass}`;

    // Character final pose
    this.character.setState('success');
    await this.character.moveTo(48);
    const endText = 'Keamanan bukan hanya tugas tim IT. Setiap keputusan kita adalah bagian dari pertahanan.';
    this.character.speak(endText, 5000);
    await this.voice.speak(endText, { rate: 1, pitch: 0.9 });

    await this._wait(500);

    // Show ending overlay
    this.endingOverlay.classList.add('visible');

    // Wire up ending buttons
    document.getElementById('btn-restart').onclick = () => this.restart();
    document.getElementById('btn-summary').onclick = () => this._showSummary();
  }

  _showSummary() {
    // Scroll ending overlay to show all content
    const endingContent = this.endingOverlay.querySelector('.ending-content');
    if (endingContent) endingContent.scrollTop = endingContent.scrollHeight;
  }

  async restart() {
    this.voice.stop();
    this.endingOverlay.classList.remove('visible');
    this.titleOverlay.classList.remove('visible');
    this.decisions.hide();
    this.character.reset();
    this.score.reset();
    this.timeline.reset();
    this._cleanupAllVisuals();
    this.currentSceneIndex = -1;

    await this._wait(300);
    this.timeline.start();
    await this.playScene(0);
  }

  _cleanupAllVisuals() {
    this.emailPopup.classList.remove('visible');
    this.usbCloseup.classList.remove('visible');
    this.strangerEl.classList.remove('visible');
    this.strangerBubble.classList.remove('visible');
    this.passwordScreen.classList.remove('visible');
    this.notificationBell.classList.remove('visible');
    this.consequenceOverlay.innerHTML = '';
  }

  // --- Visual Effects ---

  _flashEffect(color) {
    this.stage.classList.add(color === 'red' ? 'flash-red' : 'flash-green');
    setTimeout(() => {
      this.stage.classList.remove('flash-red', 'flash-green');
    }, 800);
  }

  async _showShieldAnimation() {
    const shield = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    shield.setAttribute('viewBox', '0 0 64 80');
    shield.classList.add('shield-anim');
    shield.innerHTML = `
      <path d="M32 4 L56 18 L56 44 C56 60 32 76 32 76 C32 76 8 60 8 44 L8 18 Z"
            fill="rgba(34,197,94,0.3)" stroke="#22c55e" stroke-width="3"/>
      <path d="M22 40 L30 48 L44 30" fill="none" stroke="#22c55e" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
    `;
    this.consequenceOverlay.appendChild(shield);
    await this._wait(1500);
  }

  async _showAttackerAnimation() {
    const attacker = document.createElement('div');
    attacker.className = 'attacker-icon';
    attacker.style.top = '30%';
    attacker.style.right = '15%';
    attacker.textContent = '🕵️';
    this.consequenceOverlay.appendChild(attacker);

    // Red warning on server
    const warning = document.createElement('div');
    warning.style.cssText = `
      position: absolute; top: 10%; right: 8%;
      font-size: 2rem; animation: floatUp 1s ease-in-out infinite;
    `;
    warning.textContent = '⚠️';
    this.consequenceOverlay.appendChild(warning);

    await this._wait(1800);
  }

  async _showMalwareAnimation() {
    // Create malware particles spreading from center
    for (let i = 0; i < 8; i++) {
      const p = document.createElement('div');
      p.className = 'malware-particle';
      const angle = (i / 8) * Math.PI * 2;
      p.style.left = '55%';
      p.style.top = '60%';
      p.style.setProperty('--mx', `${Math.cos(angle) * 80}px`);
      p.style.setProperty('--my', `${Math.sin(angle) * 80}px`);
      p.style.animationDelay = `${i * 0.1}s`;
      this.consequenceOverlay.appendChild(p);
    }
    await this._wait(1800);
  }

  async _transitionScene() {
    this.sceneTransition.classList.add('active');
    await this._wait(500);
    this.sceneTransition.classList.remove('active');
    await this._wait(300);
  }

  _updateProgress(index) {
    const total = SCENE_TIMELINE.length;
    this.progressEl.textContent = `${index + 1} / ${total}`;
  }

  // Utility
  _wait(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', () => {
  const sceneManager = new SceneManager();

  // Start button
  document.getElementById('btn-start').addEventListener('click', () => {
    sceneManager.start();
  });

  // Keyboard shortcut: Enter to start
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !sceneManager.isRunning) {
      sceneManager.start();
    }
  });
});
