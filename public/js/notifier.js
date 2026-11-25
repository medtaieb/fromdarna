/**
 * FromDarna Real-Time Notifications Hub (Browser Notifications + Web Audio + Haptic Vibration)
 */
window.FromDarnaNotifier = (function () {
    let audioCtx = null;

    function getAudioContext() {
        if (!audioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                audioCtx = new AudioContext();
            }
        }
        if (audioCtx && audioCtx.state === 'suspended') {
            audioCtx.resume();
        }
        return audioCtx;
    }

    // Joue un son d'alerte bitonal agréable
    function playBeep(type = 'default') {
        try {
            const ctx = getAudioContext();
            if (!ctx) return;
            const now = ctx.currentTime;

            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            if (type === 'urgent') {
                // Alerte rapide répétée
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(650, now);
                osc.frequency.setValueAtTime(850, now + 0.15);
                gain.gain.setValueAtTime(0.3, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.4);
            } else if (type === 'success') {
                // Son harmonieux montant (Do-Mi-Sol)
                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, now);
                osc.frequency.setValueAtTime(659.25, now + 0.12);
                osc.frequency.setValueAtTime(783.99, now + 0.24);
                gain.gain.setValueAtTime(0.25, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.45);
            } else {
                // Carillon restaurant standard
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(880, now);
                osc.frequency.setValueAtTime(1174.66, now + 0.18);
                gain.gain.setValueAtTime(0.3, now);
                gain.gain.exponentialRampToValueAtTime(0.001, now + 0.55);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start(now);
                osc.stop(now + 0.55);
            }
        } catch (e) {
            console.warn("AudioContext notification non supporté ou bloqué", e);
        }
    }

    // Demander la permission à l'utilisateur
    function requestPermission(callback) {
        if (!("Notification" in window)) {
            console.log("Ce navigateur ne prend pas en charge les notifications de bureau");
            return;
        }

        if (Notification.permission === "default") {
            Notification.requestPermission().then(function (permission) {
                if (callback) callback(permission);
            });
        } else if (callback) {
            callback(Notification.permission);
        }
    }

    // Afficher une notification système (Desktop + Mobile)
    function notify(title, options = {}) {
        // 1. Sonnerie
        playBeep(options.soundType || 'default');

        // 2. Vibration mobile si disponible
        if (navigator.vibrate) {
            navigator.vibrate(options.vibrate || [200, 100, 200]);
        }

        // 3. Notification système native
        if ("Notification" in window && Notification.permission === "granted") {
            try {
                const notif = new Notification(title, {
                    body: options.body || '',
                    icon: options.icon || 'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="50" fill="%2398551C"/><text x="50" y="65" font-size="45" font-family="sans-serif" font-weight="bold" fill="%23FFF4D2" text-anchor="middle">FD</text></svg>',
                    badge: options.badge || '',
                    tag: options.tag || 'fromdarna-alert',
                    renotify: true,
                    ...options
                });

                if (options.url) {
                    notif.onclick = function () {
                        window.focus();
                        window.location.href = options.url;
                    };
                }
            } catch (err) {
                console.warn("Erreur déclenchement Notification native:", err);
            }
        }

        // 4. Toast visuel flottant in-page (toujours visible même si notifications système désactivées)
        showInPageToast(title, options.body || '', options.soundType || 'default');
    }

    function showInPageToast(title, body, type) {
        let container = document.getElementById('fromdarna-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'fromdarna-toast-container';
            container.style.cssText = "position:fixed;top:20px;right:20px;z-index:999999;display:flex;flex-direction:column;gap:10px;pointer-events:none;max-width:360px;";
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        const borderColor = type === 'urgent' ? '#C41317' : (type === 'success' ? '#2E7D32' : '#98551C');
        const bgColor = type === 'urgent' ? '#FFF5F5' : '#FFFDF9';

        toast.style.cssText = `background:${bgColor};border:2px solid ${borderColor};border-radius:12px;padding:12px 16px;box-shadow:0 8px 24px rgba(0,0,0,0.18);pointer-events:auto;animation:toast-pop 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);display:flex;flex-direction:column;gap:4px;font-family:system-ui,-apple-system,sans-serif;`;
        
        toast.innerHTML = `
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <b style="color:${borderColor};font-size:14.5px;">${title}</b>
                <span style="cursor:pointer;font-weight:bold;color:#888;font-size:16px;margin-left:8px;" onclick="this.parentElement.parentElement.remove()">×</span>
            </div>
            ${body ? `<div style="font-size:13px;color:#333;">${body}</div>` : ''}
            <div style="font-size:11px;color:#999;text-align:right;">À l'instant · FromDarna</div>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.transition = 'opacity 0.4s, transform 0.4s';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 400);
            }
        }, 5000);
    }

    return {
        requestPermission: requestPermission,
        playBeep: playBeep,
        send: notify
    };
})();
