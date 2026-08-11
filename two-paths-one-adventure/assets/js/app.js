(() => {
    'use strict';

    const state = {
        questions: [],
        currentStep: 0,
        selections: {},
        adventureCode: localStorage.getItem('tpoa_adventure_code') || '',
        guestName: localStorage.getItem('tpoa_guest_name') || '',
        finalSummary: '',
        soundEnabled: false,
        audioContext: null,
        soundTimer: null
    };

    const screens = {
        welcome: document.getElementById('welcomeScreen'),
        game: document.getElementById('gameScreen'),
        summary: document.getElementById('summaryScreen'),
        accepted: document.getElementById('acceptedScreen'),
        error: document.getElementById('errorScreen')
    };

    const elements = {
        welcomeForm: document.getElementById('welcomeForm'),
        guestName: document.getElementById('guestName'),
        nameError: document.getElementById('nameError'),
        questionEyebrow: document.getElementById('questionEyebrow'),
        questionTitle: document.getElementById('questionTitle'),
        questionDescription: document.getElementById('questionDescription'),
        optionsGrid: document.getElementById('optionsGrid'),
        stepLabel: document.getElementById('stepLabel'),
        progressPercent: document.getElementById('progressPercent'),
        progressBar: document.getElementById('progressBar'),
        backButton: document.getElementById('backButton'),
        summaryText: document.getElementById('summaryText'),
        summaryChips: document.getElementById('summaryChips'),
        ticketGuest: document.getElementById('ticketGuest'),
        ticketCode: document.getElementById('ticketCode'),
        acceptButton: document.getElementById('acceptButton'),
        restartButton: document.getElementById('restartButton'),
        newAdventureButton: document.getElementById('newAdventureButton'),
        shareButton: document.getElementById('shareButton'),
        retryButton: document.getElementById('retryButton'),
        errorMessage: document.getElementById('errorMessage'),
        brandButton: document.getElementById('brandButton'),
        themeToggle: document.getElementById('themeToggle'),
        soundToggle: document.getElementById('soundToggle'),
        toast: document.getElementById('toast'),
        floatingBits: document.getElementById('floatingBits')
    };

    const iconMap = {
        calm: '<svg viewBox="0 0 24 24"><path d="M4 14c4-6 12-6 16 0M6 18c3-4 9-4 12 0M12 3v5"/></svg>',
        spark: '<svg viewBox="0 0 24 24"><path d="m12 2 1.7 5.3L19 9l-5.3 1.7L12 16l-1.7-5.3L5 9l5.3-1.7L12 2Zm7 13 .9 2.6L22 19l-2.1 1.4L19 23l-.9-2.6L16 19l2.1-1.4L19 15Z"/></svg>',
        coffee: '<svg viewBox="0 0 24 24"><path d="M5 8h12v5a6 6 0 0 1-6 6H5V8Zm12 2h2a2 2 0 0 1 0 4h-2M8 3c-1 1-.8 2 .2 3M12 3c-1 1-.8 2 .2 3"/></svg>',
        scoop: '<svg viewBox="0 0 24 24"><path d="M8 10a4 4 0 1 1 8 0M7 10h10l-3 11h-4L7 10Z"/></svg>',
        explore: '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m15 9-2 5-5 2 2-5 5-2Z"/></svg>',
        play: '<svg viewBox="0 0 24 24"><path d="M8 8h8a5 5 0 0 1 4.8 6.4l-1 3.2a2.2 2.2 0 0 1-3.8.8L14.5 16h-5L8 18.4a2.2 2.2 0 0 1-3.8-.8l-1-3.2A5 5 0 0 1 8 8Z"/><path d="M8 11v4M6 13h4M16 12h.01M18 14h.01"/></svg>',
        street: '<svg viewBox="0 0 24 24"><path d="M4 10h16M6 10l1 10h10l1-10M7 10l1-5h8l1 5M9 14h6"/></svg>',
        dine: '<svg viewBox="0 0 24 24"><path d="M7 3v8M4 3v5a3 3 0 0 0 6 0V3M7 11v10M16 3v18M16 3c3 2 4 5 4 8h-4"/></svg>',
        sunset: '<svg viewBox="0 0 24 24"><path d="M3 18h18M5 14a7 7 0 0 1 14 0M12 2v3M4.2 6.2l2.1 2.1M19.8 6.2l-2.1 2.1"/></svg>',
        movie: '<svg viewBox="0 0 24 24"><path d="M4 7h16v13H4V7Zm0 4h16M7 4l2 3M12 4l2 3M17 4l2 3"/></svg>',
        check: '<svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/></svg>'
    };

    function setScreen(name) {
        Object.entries(screens).forEach(([key, node]) => {
            node.classList.toggle('active-screen', key === name);
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    async function api(path, options = {}) {
        const response = await fetch(`api/${path}`, {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                ...(options.headers || {})
            },
            ...options
        });

        let payload;
        try {
            payload = await response.json();
        } catch {
            throw new Error('The server returned an unreadable response.');
        }

        if (!response.ok || payload.success === false) {
            throw new Error(payload.message || 'Something went wrong.');
        }

        return payload;
    }

    function showError(message) {
        elements.errorMessage.textContent = message;
        setScreen('error');
    }

    function toast(message) {
        elements.toast.textContent = message;
        elements.toast.classList.add('show');
        window.clearTimeout(toast.timer);
        toast.timer = window.setTimeout(() => {
            elements.toast.classList.remove('show');
        }, 2600);
    }

    async function loadQuestions() {
        const payload = await api('get_questions.php', { method: 'GET' });
        state.questions = payload.questions || [];
        if (!state.questions.length) {
            throw new Error('No adventure choices are available yet.');
        }
    }

    async function startAdventure(name) {
        const payload = await api('start_adventure.php', {
            method: 'POST',
            body: JSON.stringify({
                guest_name: name,
                theme: document.documentElement.dataset.bsTheme || 'light'
            })
        });

        state.adventureCode = payload.adventure_code;
        state.guestName = payload.guest_name;
        state.currentStep = 0;
        state.selections = {};
        localStorage.setItem('tpoa_adventure_code', state.adventureCode);
        localStorage.setItem('tpoa_guest_name', state.guestName);
        renderQuestion();
        setScreen('game');
    }

    function renderQuestion() {
        const question = state.questions[state.currentStep];
        if (!question) return;

        const progress = Math.round(((state.currentStep + 1) / state.questions.length) * 100);
        elements.stepLabel.textContent = `Choice ${state.currentStep + 1} of ${state.questions.length}`;
        elements.progressPercent.textContent = `${progress}%`;
        elements.progressBar.style.width = `${progress}%`;
        elements.progressBar.parentElement.setAttribute('aria-valuenow', String(progress));

        elements.questionEyebrow.textContent = question.eyebrow;
        elements.questionTitle.textContent = question.title;
        elements.questionDescription.textContent = question.description;
        elements.backButton.style.visibility = state.currentStep === 0 ? 'hidden' : 'visible';

        const selectedOption = state.selections[question.id]?.option_id;
        elements.optionsGrid.innerHTML = question.options.map(option => {
            const selected = Number(selectedOption) === Number(option.id);
            return `
                <button
                    type="button"
                    class="choice-card ${selected ? 'selected' : ''}"
                    data-question-id="${question.id}"
                    data-option-id="${option.id}"
                    aria-pressed="${selected ? 'true' : 'false'}"
                >
                    <span class="choice-check">${iconMap.check}</span>
                    <span class="choice-icon">${iconMap[option.icon_name] || iconMap.spark}</span>
                    <h3>${escapeHtml(option.title)}</h3>
                    <p>${escapeHtml(option.description)}</p>
                </button>
            `;
        }).join('');

        elements.optionsGrid.querySelectorAll('.choice-card').forEach(card => {
            card.addEventListener('click', () => chooseOption(card, question));
        });
    }

    async function chooseOption(card, question) {
        if (card.classList.contains('loading')) return;

        const optionId = Number(card.dataset.optionId);
        const option = question.options.find(item => Number(item.id) === optionId);
        if (!option) return;

        elements.optionsGrid.querySelectorAll('.choice-card').forEach(item => {
            item.classList.toggle('selected', item === card);
            item.setAttribute('aria-pressed', item === card ? 'true' : 'false');
        });
        card.classList.add('loading');

        try {
            await api('save_choice.php', {
                method: 'POST',
                body: JSON.stringify({
                    adventure_code: state.adventureCode,
                    question_id: question.id,
                    option_id: option.id
                })
            });

            state.selections[question.id] = {
                question_id: question.id,
                option_id: option.id,
                title: option.title,
                icon_name: option.icon_name
            };

            playSelectionTone();

            window.setTimeout(async () => {
                if (state.currentStep < state.questions.length - 1) {
                    state.currentStep += 1;
                    renderQuestion();
                } else {
                    await completeAdventure();
                }
            }, 320);
        } catch (error) {
            card.classList.remove('loading');
            toast(error.message);
        }
    }

    async function completeAdventure() {
        try {
            const payload = await api('complete_adventure.php', {
                method: 'POST',
                body: JSON.stringify({
                    adventure_code: state.adventureCode
                })
            });

            state.finalSummary = payload.summary;
            elements.summaryText.textContent = payload.summary;
            elements.ticketGuest.textContent = state.guestName;
            elements.ticketCode.textContent = `PATH-${state.adventureCode.slice(0, 4).toUpperCase()}`;

            elements.summaryChips.innerHTML = payload.choices.map(choice => `
                <span class="summary-chip">
                    ${iconMap[choice.icon_name] || iconMap.check}
                    ${escapeHtml(choice.title)}
                </span>
            `).join('');

            setScreen('summary');
        } catch (error) {
            showError(error.message);
        }
    }

    async function respond(responseType) {
        await api('respond_invitation.php', {
            method: 'POST',
            body: JSON.stringify({
                adventure_code: state.adventureCode,
                response: responseType
            })
        });
    }

    async function acceptAdventure() {
        elements.acceptButton.disabled = true;
        try {
            await respond('accepted');
            burstConfetti();
            setScreen('accepted');
            playCelebration();
        } catch (error) {
            toast(error.message);
        } finally {
            elements.acceptButton.disabled = false;
        }
    }

    async function restartAdventure(recordResponse = true) {
        try {
            if (recordResponse && state.adventureCode) {
                await respond('restart');
            }
        } catch {
            // Restart remains available even if the response could not be stored.
        }

        state.currentStep = 0;
        state.selections = {};
        state.adventureCode = '';
        state.finalSummary = '';
        localStorage.removeItem('tpoa_adventure_code');
        elements.guestName.value = state.guestName;
        setScreen('welcome');
        window.setTimeout(() => elements.guestName.focus(), 280);
    }

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    }

    function applyTheme(theme) {
        const finalTheme = theme === 'dark' ? 'dark' : 'light';
        document.documentElement.dataset.bsTheme = finalTheme;
        localStorage.setItem('tpoa_theme', finalTheme);
        document.querySelector('meta[name="theme-color"]')
            .setAttribute('content', finalTheme === 'dark' ? '#16131b' : '#5d3b8c');
    }

    function toggleTheme() {
        const current = document.documentElement.dataset.bsTheme || 'light';
        applyTheme(current === 'light' ? 'dark' : 'light');
    }

    function createAudioContext() {
        if (!state.audioContext) {
            state.audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }
        return state.audioContext;
    }

    function playNote(frequency, duration = .8, volume = .025, delay = 0) {
        if (!state.soundEnabled) return;
        const context = createAudioContext();
        const start = context.currentTime + delay;
        const oscillator = context.createOscillator();
        const gain = context.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(frequency, start);
        gain.gain.setValueAtTime(0.0001, start);
        gain.gain.exponentialRampToValueAtTime(volume, start + .08);
        gain.gain.exponentialRampToValueAtTime(0.0001, start + duration);

        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.start(start);
        oscillator.stop(start + duration + .05);
    }

    function playSelectionTone() {
        playNote(523.25, .35, .02);
        playNote(659.25, .42, .015, .08);
    }

    function playCelebration() {
        [523.25, 659.25, 783.99, 1046.5].forEach((note, index) => {
            playNote(note, .8, .025, index * .12);
        });
    }

    function startAmbientSound() {
        state.soundEnabled = true;
        elements.soundToggle.classList.add('is-on');
        elements.soundToggle.setAttribute('aria-label', 'Turn off ambient sound');
        elements.soundToggle.innerHTML = '<svg viewBox="0 0 24 24"><path d="M4 9v6h4l5 4V5L8 9H4Zm11.5-.5a5 5 0 0 1 0 7M18 6a8.5 8.5 0 0 1 0 12"/></svg>';

        const chime = () => {
            const notes = [261.63, 329.63, 392, 493.88];
            const note = notes[Math.floor(Math.random() * notes.length)];
            playNote(note, 2.4, .012);
            playNote(note * 1.5, 1.8, .006, .22);
        };

        chime();
        state.soundTimer = window.setInterval(chime, 5200);
    }

    function stopAmbientSound() {
        state.soundEnabled = false;
        window.clearInterval(state.soundTimer);
        elements.soundToggle.classList.remove('is-on');
        elements.soundToggle.setAttribute('aria-label', 'Turn on ambient sound');
        elements.soundToggle.innerHTML = '<svg viewBox="0 0 24 24"><path d="M4 9v6h4l5 4V5L8 9H4Zm13.5 3a4.5 4.5 0 0 0-2.2-3.9v7.8a4.5 4.5 0 0 0 2.2-3.9Z"/></svg>';
    }

    function toggleSound() {
        if (state.soundEnabled) {
            stopAmbientSound();
        } else {
            startAmbientSound();
        }
    }

    function burstConfetti() {
        const colors = ['var(--accent)', 'var(--coral)', 'var(--gold)', '#8ab7a0', '#f1c6d5'];
        elements.floatingBits.innerHTML = '';

        for (let index = 0; index < 72; index += 1) {
            const bit = document.createElement('span');
            bit.className = 'confetti-bit';
            bit.style.left = `${Math.random() * 100}%`;
            bit.style.background = colors[index % colors.length];
            bit.style.setProperty('--duration', `${2 + Math.random() * 1.7}s`);
            bit.style.setProperty('--drift', `${-90 + Math.random() * 180}px`);
            bit.style.setProperty('--spin', `${300 + Math.random() * 740}deg`);
            bit.style.animationDelay = `${Math.random() * .45}s`;
            elements.floatingBits.appendChild(bit);
        }

        window.setTimeout(() => {
            elements.floatingBits.innerHTML = '';
        }, 4300);
    }

    async function shareAdventure() {
        const shareText = state.finalSummary
            ? `${state.finalSummary} — Two Paths, One Adventure`
            : 'Our adventure is ready.';

        try {
            if (navigator.share) {
                await navigator.share({
                    title: 'Two Paths, One Adventure',
                    text: shareText
                });
            } else {
                await navigator.clipboard.writeText(shareText);
                toast('Adventure copied to your clipboard.');
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                toast('Sharing is not available on this device.');
            }
        }
    }

    function bindEvents() {
        elements.welcomeForm.addEventListener('submit', async event => {
            event.preventDefault();
            const name = elements.guestName.value.trim();
            elements.nameError.textContent = '';

            if (name.length < 2) {
                elements.nameError.textContent = 'Please enter at least two characters.';
                elements.guestName.focus();
                return;
            }

            const submitButton = elements.welcomeForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;

            try {
                if (!state.questions.length) {
                    await loadQuestions();
                }
                await startAdventure(name);
            } catch (error) {
                showError(error.message);
            } finally {
                submitButton.disabled = false;
            }
        });

        elements.backButton.addEventListener('click', () => {
            if (state.currentStep > 0) {
                state.currentStep -= 1;
                renderQuestion();
            }
        });

        elements.acceptButton.addEventListener('click', acceptAdventure);
        elements.restartButton.addEventListener('click', () => restartAdventure(true));
        elements.newAdventureButton.addEventListener('click', () => restartAdventure(false));
        elements.shareButton.addEventListener('click', shareAdventure);
        elements.retryButton.addEventListener('click', () => window.location.reload());
        elements.brandButton.addEventListener('click', () => restartAdventure(false));
        elements.themeToggle.addEventListener('click', toggleTheme);
        elements.soundToggle.addEventListener('click', toggleSound);
    }

    async function init() {
        const storedTheme = localStorage.getItem('tpoa_theme');
        const systemDark = window.matchMedia?.('(prefers-color-scheme: dark)').matches;
        applyTheme(storedTheme || (systemDark ? 'dark' : 'light'));
        elements.guestName.value = state.guestName;
        bindEvents();

        try {
            await loadQuestions();
        } catch (error) {
            showError(error.message);
        }
    }

    init();
})();
