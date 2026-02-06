<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Űrhajó Misszió</title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#00ffcc">
    <style>
        body, html { margin: 0; padding: 0; width: 100%; height: 100%; overflow: hidden; background: #050510; font-family: sans-serif; }
        canvas { display: block; }
        #ui-layer { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; pointer-events: none; color: #00ffcc; }
        .menu { pointer-events: auto; background: rgba(10, 10, 30, 0.9); padding: 30px; border: 2px solid #00ffcc; border-radius: 20px; text-align: center; }
        button { padding: 12px 20px; margin: 10px; cursor: pointer; background: transparent; border: 1px solid #00ffcc; color: #00ffcc; border-radius: 5px; }
        button:hover { background: #00ffcc; color: #050510; }
        #score-display { position: absolute; top: 20px; font-size: 1.5rem; }
    </style>
</head>
<body>
    <div id="ui-layer">
        <div id="score-display">PONT: 0</div>
        <div id="start-menu" class="menu">
            <h1>ŰRHAJÓ MISSZIÓ</h1>
            <button onclick="startGame('easy')">EASY</button>
            <button onclick="startGame('normal')">NORMAL</button>
            <button onclick="startGame('hard')">HARD</button>
        </div>
        <div id="game-over" class="menu" style="display: none;">
            <h1 style="color: #ff00ff;">BUMM!</h1>
            <button onclick="showMenu()">VISSZA</button>
        </div>
    </div>
    <canvas id="gameCanvas"></canvas>

    <audio id="snd-hit" src="https://www.soundjay.com/buttons/sounds/beep-07.mp3"></audio>
    <audio id="snd-start" src="https://www.soundjay.com/transportation/sounds/rocket-launch-01.mp3"></audio>

    <script>
        // PWA Regisztráció
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js');
            });
        }

        const canvas = document.getElementById('gameCanvas');
        const ctx = canvas.getContext('2d');
        const scoreEl = document.getElementById('score-display');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;

        let score = 0, gameActive = false, obstacles = [];
        let ship = { x: canvas.width / 2, y: canvas.height - 100, size: 50 };
        let config = { speedMin: 1.5, speedMax: 2.5, spawnRate: 0.02, randomFactor: 1 };

        function startGame(level) {
            if (level === 'easy') config = { speedMin: 1.5, speedMax: 2.5, spawnRate: 0.02, randomFactor: 1 };
            if (level === 'normal') config = { speedMin: 3, speedMax: 5, spawnRate: 0.04, randomFactor: 3 };
            if (level === 'hard') config = { speedMin: 5, speedMax: 9, spawnRate: 0.07, randomFactor: 5 };
            score = 0; obstacles = []; gameActive = true;
            document.getElementById('start-menu').style.display = 'none';
            document.getElementById('game-over').style.display = 'none';
            document.getElementById('snd-start').play().catch(()=>{});
            update();
        }

        function showMenu() { gameActive = false; document.getElementById('game-over').style.display = 'none'; document.getElementById('start-menu').style.display = 'block'; }

        function move(e) { if(gameActive) { ship.x = e.touches ? e.touches[0].clientX : e.clientX; ship.y = e.touches ? e.touches[0].clientY : e.clientY; } }
        window.addEventListener('mousemove', move);
        window.addEventListener('touchmove', move);

        function update() {
            if (!gameActive) return;
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.font = "50px serif"; ctx.textAlign = "center";
            ctx.fillText("🚀", ship.x, ship.y);

            if (Math.random() < config.spawnRate) {
                obstacles.push({ x: Math.random()*canvas.width, y: -50, speed: Math.random()*(config.speedMax-config.speedMin)+config.speedMin, icon: Math.random()>0.1?"☄️":"👾" });
            }

            for (let i = obstacles.length - 1; i >= 0; i--) {
                let o = obstacles[i]; o.y += o.speed;
                ctx.fillText(o.icon, o.x, o.y);
                if (Math.hypot(ship.x - o.x, ship.y - o.y) < 40) {
                    gameActive = false;
                    document.getElementById('snd-hit').play().catch(()=>{});
                    document.getElementById('game-over').style.display = 'block';
                }
                if (o.y > canvas.height + 50) { obstacles.splice(i, 1); score++; scoreEl.innerText = "PONT: " + score; }
            }
            requestAnimationFrame(update);
        }
    </script>
</body>
</html>
{
  "short_name": "Űrhajó",
  "name": "Űrhajó Misszió",
  "icons": [
    {
      "src": "https://cdn-icons-png.flaticon.com/512/1066/1066371.png",
      "type": "image/png",
      "sizes": "512x512",
      "purpose": "any maskable"
    }
  ],
  "start_url": "index.html",
  "background_color": "#050510",
  "display": "standalone",
  "scope": "./",
  "theme_color": "#00ffcc"
}
const CACHE_NAME = 'urhajo-v2';
const assets = [
  './',
  './index.html',
  './manifest.json',
  'https://www.soundjay.com/buttons/sounds/beep-07.mp3',
  'https://www.soundjay.com/transportation/sounds/rocket-launch-01.mp3'
];

self.addEventListener('install', e => {
  e.waitUntil(caches.open(CACHE_NAME).then(cache => cache.addAll(assets)));
});

self.addEventListener('fetch', e => {
  e.respondWith(caches.match(e.request).then(res => res || fetch(e.request)));
});