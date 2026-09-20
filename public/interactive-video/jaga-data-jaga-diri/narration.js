// Browser voices are supplied by the operating system; no audio is recorded.
const narration = (() => {
    const synth = window.speechSynthesis;
    const select = document.querySelector('#voiceSelect');
    const mute = document.querySelector('#muteButton');
    const status = document.querySelector('#voiceStatus');
    let voices = [], muted = false, lastCue = null, utterance = null;
    const supported = Boolean(synth && window.SpeechSynthesisUtterance);
    // The API has no gender field. Prefer known female voice names when available.
    const femaleName = /gadis|indah|female|wanita|damayanti/i;
    function loadVoices() {
        const previous = select.value;
        voices = synth.getVoices().filter(voice => /^id(?:-|_|$)/i.test(voice.lang));
        voices.sort((a, b) => Number(femaleName.test(b.name)) - Number(femaleName.test(a.name)));
        select.replaceChildren();
        voices.forEach(voice => {
            const option = document.createElement('option');
            option.value = voice.voiceURI;
            option.textContent = voice.name;
            select.appendChild(option);
        });
        if (voices.some(voice => voice.voiceURI === previous)) select.value = previous;
        select.disabled = !voices.length;
        if (!voices.length) {
            const option = document.createElement('option');
            option.textContent = 'Suara bawaan (id-ID)';
            select.appendChild(option);
        }
        updateStatus();
    }
    function updateStatus() {
        const voice = voices.find(item => item.voiceURI === select.value);
        status.textContent = muted ? 'Narasi mati' : voice && femaleName.test(voice.name)
            ? 'Suara wanita' : 'Suara mengikuti perangkat';
    }
    function stop() {
        if (supported) synth.cancel();
        utterance = null;
    }
    function say(text, { interrupt = true } = {}) {
        if (interrupt) stop();
        if (!supported || muted) return;
        const speech = new SpeechSynthesisUtterance(text);
        speech.lang = 'id-ID';
        speech.voice = voices.find(voice => voice.voiceURI === select.value) || null;
        speech.rate = .95;
        speech.pitch = .96;
        speech.volume = 1;
        utterance = speech;
        speech.onerror = event => {
            if (utterance === speech && !['interrupted', 'canceled'].includes(event.error)) {
                status.textContent = 'Narasi tidak tersedia. Coba suara lain.';
            }
        };
        synth.resume();
        synth.speak(speech);
    }
    if (supported) {
        loadVoices();
        synth.addEventListener('voiceschanged', loadVoices);
        mute.addEventListener('click', () => {
            muted = !muted;
            mute.setAttribute('aria-pressed', String(muted));
            mute.setAttribute('aria-label', muted ? 'Aktifkan narasi' : 'Matikan narasi');
            mute.title = muted ? 'Aktifkan narasi' : 'Matikan narasi';
            mute.innerHTML = muted ? '&#128263;' : '&#128266;';
            if (muted) stop();
            updateStatus();
        });
        select.addEventListener('change', () => {
            stop();
            updateStatus();
            // Audition the selected voice only on an explicit user choice.
            say('Halo, mari belajar mengenali risiko keamanan digital.');
        });
        window.addEventListener('pagehide', stop);
    } else {
        select.disabled = mute.disabled = true;
        select.options[0].textContent = 'Narasi tidak didukung';
        status.textContent = 'Gunakan browser dengan dukungan suara';
    }
    return {
        say, stop,
        cue(key, text) { if (key !== lastCue) { lastCue = key; say(text, { interrupt: false }); } },
        pause() { if (supported) synth.pause(); },
        resume() { if (supported && !muted) synth.resume(); },
        reset() { stop(); lastCue = null; }
    };
})();
