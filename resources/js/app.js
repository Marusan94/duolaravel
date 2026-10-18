// duoLaravel — micro-interacciones: sonidos (Web Audio, sin assets) y transición de tema
(() => {
    let ctx = null;

    const ensureCtx = () => {
        if (!ctx) {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return null;
            ctx = new AC();
        }
        if (ctx.state === 'suspended') ctx.resume().catch(() => {});
        return ctx;
    };

    const note = (freq, start, dur, opts = {}) => {
        const ac = ensureCtx();
        if (!ac) return;

        const { type = 'sine', gain = 0.12, slideTo = null } = opts;
        const t0 = ac.currentTime + start;
        const osc = ac.createOscillator();
        const g = ac.createGain();

        osc.type = type;
        osc.frequency.setValueAtTime(freq, t0);
        if (slideTo) osc.frequency.exponentialRampToValueAtTime(slideTo, t0 + dur);

        g.gain.setValueAtTime(0.0001, t0);
        g.gain.exponentialRampToValueAtTime(gain, t0 + 0.02);
        g.gain.exponentialRampToValueAtTime(0.0001, t0 + dur);

        osc.connect(g);
        g.connect(ac.destination);
        osc.start(t0);
        osc.stop(t0 + dur + 0.05);
    };

    const sounds = {
        // Respuesta correcta: dos notas ascendentes (E5 → B5)
        correct() {
            note(659.25, 0, 0.15, { gain: 0.11 });
            note(987.77, 0.09, 0.24, { gain: 0.11 });
        },
        // Respuesta incorrecta: zumbido grave y corto
        wrong() {
            note(196, 0, 0.18, { type: 'triangle', gain: 0.13, slideTo: 130 });
            note(185, 0.16, 0.22, { type: 'triangle', gain: 0.1, slideTo: 110 });
        },
        // Lección completada: arpegio C5-E5-G5-C6
        complete() {
            note(523.25, 0, 0.14, { gain: 0.1 });
            note(659.25, 0.1, 0.14, { gain: 0.1 });
            note(783.99, 0.2, 0.16, { gain: 0.1 });
            note(1046.5, 0.32, 0.34, { gain: 0.12 });
        },
    };

    const enabled = () => {
        try {
            return window.localStorage.getItem('duo-sound') !== '0';
        } catch (e) {
            return true;
        }
    };

    window.duoFx = {
        play(name) {
            if (!enabled() || !sounds[name]) return;
            try {
                sounds[name]();
            } catch (e) {
                // El audio jamás debe romper la interfaz
            }
        },
        setEnabled(state) {
            try {
                window.localStorage.setItem('duo-sound', state ? '1' : '0');
            } catch (e) {}
        },
        isEnabled: enabled,
    };

    // Los navegadores exigen un gesto del usuario para habilitar el audio
    const unlock = () => ensureCtx();
    window.addEventListener('pointerdown', unlock, { once: true });
    window.addEventListener('keydown', unlock, { once: true });

    // Puente con Livewire: el componente emite el evento `duo-sound`
    document.addEventListener('duo-sound', (event) => {
        const detail = event.detail;
        const name = detail && typeof detail === 'object' ? detail.name : detail;
        window.duoFx.play(name);
    });

    // Transición suave al cambiar de tema (evita el salto brusco de colores)
    window.duoThemeAnim = () => {
        const root = document.documentElement;
        root.classList.add('theme-anim');
        window.clearTimeout(window.__duoThemeAnimTimer);
        window.__duoThemeAnimTimer = window.setTimeout(
            () => root.classList.remove('theme-anim'),
            520,
        );
    };

    // ---------- Tema persistente (cookie → localStorage) ----------

    const readTheme = () => {
        try {
            const m = document.cookie.match(/(?:^|;\s*)duo_theme=([a-z]+)/);
            if (m && m[1]) return m[1];
        } catch (e) {}
        try {
            return window.localStorage.getItem('duo-theme');
        } catch (e) {
            return null;
        }
    };

    const applyStoredTheme = () => {
        const tema = readTheme();
        if (tema) document.documentElement.setAttribute('data-theme', tema);
    };

    window.duoSetThemeCookie = (id) => {
        try {
            document.cookie = 'duo_theme=' + id + ';path=/;max-age=31536000;samesite=lax';
        } catch (e) {}
    };

    // Livewire reemplaza el <html> al navegar (morph) y borra data-theme:
    // lo re-aplicamos al terminar cada navegación para que el tema no salte.
    document.addEventListener('livewire:navigated', applyStoredTheme);

    // ---------- Navegación fluida: barra superior + atenúado + enlace pulsado ----------

    const bar = document.createElement('div');
    bar.id = 'duo-nav-progress';
    document.body.appendChild(bar);

    const startBusy = () => {
        document.documentElement.classList.add('duo-nav-busy');
        bar.style.transition = 'none';
        bar.style.opacity = '1';
        bar.style.width = '0';
        window.requestAnimationFrame(() => {
            bar.style.transition = 'width .9s cubic-bezier(.1, .7, .3, 1)';
            bar.style.width = '72%';
        });
    };

    const endBusy = () => {
        document.documentElement.classList.remove('duo-nav-busy');
        document.querySelectorAll('.duo-pending').forEach((el) => el.classList.remove('duo-pending'));
        bar.style.transition = 'width .2s ease, opacity .3s ease .18s';
        bar.style.width = '100%';
        bar.style.opacity = '0';
        window.setTimeout(() => {
            bar.style.width = '0';
        }, 520);
    };

    document.addEventListener('livewire:navigating', startBusy);
    document.addEventListener('livewire:navigated', endBusy);

    // Highlight optimista: marca el enlace pulsado al instante, sin esperar al servidor
    document.addEventListener(
        'click',
        (event) => {
            const link = event.target && event.target.closest ? event.target.closest('a[href]') : null;
            if (!link) return;

            const isNavigate = Array.from(link.attributes).some(
                (attr) => attr.name.indexOf('wire:navigate') === 0,
            );
            if (!isNavigate) return;

            document.querySelectorAll('.duo-pending').forEach((el) => el.classList.remove('duo-pending'));
            link.classList.add('duo-pending');

            const nav = link.closest('nav');
            if (nav) {
                nav.querySelectorAll('a[href="' + link.getAttribute('href') + '"]').forEach((el) =>
                    el.classList.add('duo-pending'),
                );
            }
        },
        true,
    );
})();
