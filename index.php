<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="calculatrice.png">
    <title>Calculateur Télécom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="noise"></div>
    <div class="grid-bg"></div>

    <div class="app-wrapper">
        <!-- Header -->
        <header class="app-header">
            <div class="header-left">
                <div class="logo-mark">
                    <span class="logo-inner"></span>
                </div>
                <div class="header-text">
                    <span class="app-title">TELECOM CALC</span>
                    <span class="app-sub">Calculateur de liaisons</span>
                </div>
            </div>
            <div class="header-right">
                <div class="status-dot"></div>
                <span class="status-label">ACTIF</span>
                <button id="theme-toggle" class="theme-btn" title="Basculer thème" aria-label="Changer le thème">
                    <span class="theme-icon">🌙</span>
                </button>
            </div>
        </header>

        <!-- Main layout -->
        <main class="app-main">
            <!-- Sidebar menu -->
            <aside class="sidebar">
                <p class="sidebar-label">COMMANDES</p>
                <nav class="cmd-list">
                    <button class="cmd-item" data-cmd="gain">
                        <span class="cmd-num">01</span>
                        <span class="cmd-name">Gain</span>
                        <span class="cmd-icon">→</span>
                    </button>
                    <button class="cmd-item" data-cmd="directivite">
                        <span class="cmd-num">02</span>
                        <span class="cmd-name">Directivité</span>
                        <span class="cmd-icon">→</span>
                    </button>
                    <button class="cmd-item" data-cmd="efficacite">
                        <span class="cmd-num">03</span>
                        <span class="cmd-name">Efficacité</span>
                        <span class="cmd-icon">→</span>
                    </button>
                    <button class="cmd-item" data-cmd="attenuation">
                        <span class="cmd-num">04</span>
                        <span class="cmd-name">Atténuation</span>
                        <span class="cmd-icon">→</span>
                    </button>
                    <button class="cmd-item" data-cmd="capacite">
                        <span class="cmd-num">05</span>
                        <span class="cmd-name">Capacité canal</span>
                        <span class="cmd-icon">→</span>
                    </button>
                    <button class="cmd-item" data-cmd="budget_optique">
                        <span class="cmd-num">06</span>
                        <span class="cmd-name">Budget optique</span>
                        <span class="cmd-icon">→</span>
                    </button>
                    <div class="sidebar-divider"></div>
                    <button class="cmd-item cmd-util" data-cmd="help">
                        <span class="cmd-num">?</span>
                        <span class="cmd-name">Aide</span>
                        <span class="cmd-icon">→</span>
                    </button>
                    <button class="cmd-item cmd-util" data-cmd="clear">
                        <span class="cmd-num">✕</span>
                        <span class="cmd-name">Effacer</span>
                        <span class="cmd-icon">→</span>
                    </button>
                </nav>
            </aside>

            <!-- Terminal panel -->
            <section class="terminal-panel">
                <!-- Terminal topbar -->
                <div class="terminal-bar">
                    <div class="traffic-lights">
                        <span class="tl tl-red"></span>
                        <span class="tl tl-yellow"></span>
                        <span class="tl tl-green"></span>
                    </div>
                    <span class="terminal-title">~ Calculateur@telecom ~</span>
                    <span class="terminal-tag">v2.0</span>
                </div>

                <!-- Output -->
                <div id="output" class="terminal-output"></div>

                <!-- Input row -->
                <div class="terminal-input-row">
                    <span class="prompt-symbol">❯</span>
                    <input
                        type="text"
                        id="terminal-input"
                        class="terminal-input"
                        placeholder="Tapez une commande ou un numéro…"
                        autofocus
                        autocomplete="off"
                        spellcheck="false"
                    >
                    <button id="send-btn" class="send-btn" aria-label="Envoyer">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M2 8h12M9 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </section>
        </main>
    </div>

<script>
const outputDiv = document.getElementById('output');
const input = document.getElementById('terminal-input');
const sendBtn = document.getElementById('send-btn');
let errorOccurred = false;
let currentStep = null;
let commandData = {};

const commandMap = {
    '1': 'gain', '2': 'directivite', '3': 'efficacite',
    '4': 'help', '5': 'clear', '6': 'attenuation',
    '7': 'capacite', '8': 'budget_optique'
};

function addLine(text, type = 'normal') {
    const line = document.createElement('div');
    line.className = 'output-line ' + type;
    if (type === 'result') {
        const label = document.createElement('span');
        label.className = 'result-label';
        label.textContent = 'RÉSULTAT';
        line.appendChild(label);
        const content = document.createElement('span');
        content.className = 'result-content';
        content.textContent = text;
        line.appendChild(content);
    } else {
        line.textContent = text;
    }
    outputDiv.appendChild(line);
    line.style.animationDelay = '0ms';
    outputDiv.scrollTop = outputDiv.scrollHeight;
}

function addUserLine(text) {
    const line = document.createElement('div');
    line.className = 'output-line user';
    const prompt = document.createElement('span');
    prompt.className = 'user-prompt';
    prompt.textContent = '❯ ';
    const content = document.createElement('span');
    content.textContent = text;
    line.appendChild(prompt);
    line.appendChild(content);
    outputDiv.appendChild(line);
    outputDiv.scrollTop = outputDiv.scrollHeight;
}

function addSeparator() {
    const sep = document.createElement('div');
    sep.className = 'output-sep';
    outputDiv.appendChild(sep);
}

async function sendToServer(data) {
    if (location.protocol === 'file:') {
        return { result: 'Erreur: la page est ouverte en `file://`. Démarrez un serveur (ex: XAMPP) et ouvrez via http://localhost/Calculatrice-Telecoms/index.php' };
    }
    try {
        const response = await fetch('./api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data)
        });
        if (!response.ok) {
            return { result: `Erreur HTTP ${response.status} ${response.statusText}. Vérifiez qu'Apache tourne et que l'URL est correcte.` };
        }
        try { return await response.json(); }
        catch(err) {
            const text = await response.text();
            try { return JSON.parse(text); } catch(e) { return { result: text }; }
        }
    } catch(err) {
        return { result: 'Erreur de communication avec le serveur: ' + (err.message || String(err)) };
    }
}

async function handleInput() {
    let userInput = input.value.trim();
    if (userInput === '' && currentStep === null) return;

    if (currentStep === null && commandMap.hasOwnProperty(userInput)) {
        userInput = commandMap[userInput];
    }

    addUserLine(userInput);

    if (currentStep === null) {
        // Highlight active sidebar
        document.querySelectorAll('.cmd-item').forEach(b => b.classList.remove('active'));
        const match = document.querySelector('[data-cmd="' + userInput + '"]');
        if (match) match.classList.add('active');

        const response = await sendToServer({ action: 'choose', command: userInput });
        const result = response.result;

        if (result === '__CLEAR__') {
            outputDiv.innerHTML = '';
            document.querySelectorAll('.cmd-item').forEach(b => b.classList.remove('active'));
            afficherMenu();
        } else if (result.startsWith('__ASK__')) {
            const parts = result.split('|');
            if (parts.length >= 3) {
                currentStep = userInput;
                commandData = JSON.parse(parts[1]);
                addLine(parts[2], 'ask');
            } else {
                addLine('Erreur format réponse.', 'error');
            }
        } else if (result.startsWith('__RESULT__')) {
            const parts = result.split('|');
            addLine(parts[1], 'result');
            addSeparator();
            document.querySelectorAll('.cmd-item').forEach(b => b.classList.remove('active'));
        } else {
            const isErr = result.includes('inconnue') || result.includes('Erreur');
            addLine(result, isErr ? 'error' : 'normal');
            if (isErr) errorOccurred = true;
        }
    } else {
        const response = await sendToServer({
            action: 'param',
            command: currentStep,
            value: userInput,
            data: JSON.stringify(commandData)
        });
        const result = response.result;

        if (result.startsWith('__ASK__')) {
            const parts = result.split('|');
            if (parts.length >= 3) {
                commandData = JSON.parse(parts[1]);
                addLine(parts[2], 'ask');
            } else {
                addLine('Erreur format réponse.', 'error');
            }
        } else if (result.startsWith('__RESULT__')) {
            const parts = result.split('|');
            addLine(parts[1], 'result');
            addSeparator();
            currentStep = null;
            commandData = {};
            document.querySelectorAll('.cmd-item').forEach(b => b.classList.remove('active'));
        } else {
            addLine(result, 'error');
            errorOccurred = true;
            currentStep = null;
            commandData = {};
        }
    }

    input.value = '';
    input.focus();
}

input.addEventListener('keydown', async function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        await handleInput();
    }
    if ((e.key === 'Backspace' || e.key === 'Delete') && errorOccurred) {
        e.preventDefault();
        input.value = '';
        errorOccurred = false;
    }
});

sendBtn.addEventListener('click', handleInput);

// Sidebar buttons
document.querySelectorAll('.cmd-item').forEach(btn => {
    btn.addEventListener('click', () => {
        input.value = btn.dataset.cmd;
        handleInput();
    });
});

function afficherMenu() {
    addLine('Bienvenue — choisissez une commande à gauche ou tapez ci-dessous.', 'info');
    addLine('Raccourcis : 1 = gain · 2 = directivité · 3 = efficacité · 4 = aide · 5 = effacer · 6 = atténuation · 7 = capacité · 8 = budget optique', 'muted');
    addSeparator();
}

// Theme
const themeToggle = document.getElementById('theme-toggle');
function applyTheme(theme) {
    if (theme === 'light') document.documentElement.setAttribute('data-theme', 'light');
    else document.documentElement.removeAttribute('data-theme');
    localStorage.setItem('theme', theme);
    themeToggle.querySelector('.theme-icon').textContent = theme === 'light' ? '☀️' : '🌙';
}
const savedTheme = localStorage.getItem('theme') || 'dark';
applyTheme(savedTheme);
themeToggle.addEventListener('click', () => {
    const next = localStorage.getItem('theme') === 'light' ? 'dark' : 'light';
    applyTheme(next);
});

afficherMenu();
input.focus();
</script>
</body>
</html>