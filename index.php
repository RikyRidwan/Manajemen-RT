<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>Login - Manajemen RT | DeepSeek Theme</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(ellipse at 20% 30%, #0a0f2a, #030614);
            position: relative;
            overflow: hidden;
        }
        .grid-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                linear-gradient(rgba(0, 255, 255, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
            z-index: 0;
        }
        .glow {
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(0, 255, 255, 0.2), transparent 70%);
            border-radius: 50%;
            filter: blur(60px);
            animation: float 12s infinite alternate;
            z-index: 0;
        }
        @keyframes float {
            0% { transform: translate(0, 0) scale(1); opacity: 0.5; }
            100% { transform: translate(30px, -30px) scale(1.2); opacity: 0.8; }
        }
        .input-neon:focus {
            box-shadow: 0 0 12px rgba(0, 255, 255, 0.6);
            border-color: #00ffff;
            outline: none;
        }
        .btn-deep {
            background: linear-gradient(90deg, #00c6ff, #0072ff);
            transition: 0.3s;
        }
        .btn-deep:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 114, 255, 0.5);
        }
        .glass-card {
            background: rgba(15, 25, 45, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(0, 255, 255, 0.25);
        }
    </style>
</head>
<body>
    <div class="grid-bg"></div>
    <div class="glow" style="top: -100px; right: -100px;"></div>
    <div class="glow" style="bottom: -50px; left: -50px; width: 400px; height: 400px; animation-duration: 18s;"></div>
    <div class="glow" style="top: 40%; left: 30%; width: 250px; height: 250px; animation-duration: 10s;"></div>

    <div class="relative z-10 min-h-screen flex items-center justify-center px-4">
        <div class="glass-card rounded-2xl p-8 w-full max-w-md shadow-2xl transform transition-all duration-300 hover:shadow-[0_0_25px_rgba(0,255,255,0.3)]">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-tr from-cyan-400 to-blue-600 shadow-lg mb-4">
                    <i class="fas fa-brain text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-cyan-300 to-blue-400 bg-clip-text text-transparent">MANAGEMENT RT</h1>
                <p class="text-gray-400 mt-2 text-sm">Sistem Manajemen RT cerdas</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="mb-4 p-3 rounded-lg bg-red-500/20 border border-red-500/50 text-red-200 text-sm text-center">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="proses_login.php" class="space-y-5">
                <div>
                    <label class="block text-cyan-300 text-sm font-medium mb-2">Username / Email</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-cyan-400 text-sm"></i>
                        <input type="text" name="username" required
                               class="w-full pl-10 pr-3 py-3 bg-white/5 border border-cyan-500/30 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-cyan-400 input-neon transition"
                               placeholder="Masukkan username atau email">
                    </div>
                </div>
                <div>
                    <label class="block text-cyan-300 text-sm font-medium mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-cyan-400 text-sm"></i>
                        <input type="password" name="password" required
                               class="w-full pl-10 pr-3 py-3 bg-white/5 border border-cyan-500/30 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-cyan-400 input-neon transition"
                               placeholder="Masukkan password">
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-gray-400">
                        <input type="checkbox" name="remember" class="mr-2 accent-cyan-500">
                        Ingat saya
                    </label>
                    <a href="#" class="text-cyan-400 hover:text-cyan-300 transition">Lupa password?</a>
                </div>
                <button type="submit" class="btn-deep w-full py-3 rounded-xl text-white font-semibold flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-right-to-bracket"></i> Masuk ke Sistem
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-gray-400 text-sm">Belum punya akun? 
                    <a href="warga/registrasi.php" class="text-cyan-400 hover:text-cyan-300 font-medium">Daftar sebagai Warga</a>
                </p>
            </div>
            <div class="mt-4 text-center text-gray-500 text-xs">
                <i class="fas fa-shield-alt mr-1"></i> Aman & Terpercaya | Powered by Rics
            </div>
        </div>
    </div>
</body>
</html>