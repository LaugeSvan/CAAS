<?php
// will include connection later when needed
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAAS - Communities As A Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); }
        .hero-pattern { background-color: #4f46e5; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath opacity='.5' d='M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900">

    <nav class="sticky top-0 z-50 glass border-b border-slate-200 py-4 px-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <div class="bg-indigo-600 text-white p-2 rounded-lg">
                    <i class="fas fa-users-rays fa-lg"></i>
                </div>
                <span class="text-2xl font-black tracking-tight text-indigo-900">CAAS</span>
            </div>
            <div class="hidden md:flex items-center gap-8 font-medium text-slate-600">
                <a href="#features" class="hover:text-indigo-600 transition">Funktioner</a>
                <a href="#about" class="hover:text-indigo-600 transition">Om platformen</a>
                <div class="h-6 w-px bg-slate-200"></div>
                <a href="/login/" class="hover:text-indigo-600 transition">Log ind</a>
                <a href="/opret/" class="bg-indigo-600 text-white px-6 py-2.5 rounded-full hover:bg-indigo-700 shadow-md shadow-indigo-200 transition">Start dit community</a>
            </div>
        </div>
    </nav>

    <header class="hero-pattern text-white py-24 px-6 relative overflow-hidden">
        <div class="max-w-5xl mx-auto text-center relative z-10">
            <span class="bg-indigo-500/30 border border-indigo-400/50 px-4 py-1.5 rounded-full text-sm font-semibold mb-6 inline-block">Version 1.0 er landet 🚀</span>
            <h1 class="text-5xl md:text-7xl font-black mb-8 leading-tight">Gør dit lokale fællesskab <span class="text-indigo-200">stærkere.</span></h1>
            <p class="text-xl md:text-2xl mb-12 text-indigo-100 max-w-3xl mx-auto font-light">
                Alt hvad jeres community har brug for: Deleøkonomi, booking, forum og sikker kommunikation. Samlet på én platform.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="/opret/" class="bg-white text-indigo-600 px-10 py-4 rounded-xl font-bold text-lg shadow-xl hover:bg-indigo-50 transition flex items-center justify-center gap-2">
                    Opret gratis profil <i class="fas fa-arrow-right text-sm"></i>
                </a>
                <a href="#demo" class="bg-indigo-800/40 border border-indigo-400/30 backdrop-blur px-10 py-4 rounded-xl font-bold text-lg hover:bg-indigo-800/60 transition">
                    Se hvordan det virker
                </a>
            </div>
        </div>
    </header>

    <section id="features" class="py-24 px-6 max-w-7xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold text-slate-900 mb-4">Bygget til virkeligheden</h2>
            <p class="text-slate-500">Vi har fjernet støjen fra Facebook og beholdt det, der skaber værdi.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl transition group">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <i class="fas fa-hand-holding-heart fa-2x"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Deleøkonomi</h3>
                <p class="text-slate-600 leading-relaxed">Log over udstyr, checkout-system og anmeldelser. Lån naboens værktøj uden besvær.</p>
            </div>

            <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl transition group">
                <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <i class="fas fa-calendar-check fa-2x"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Smart Kalender</h3>
                <h3 class="text-xl font-bold mb-3">Smart Kalender</h3>
                <p class="text-slate-600 leading-relaxed">Events med tilmelding, betaling og automatisk synkronisering til din telefon.</p>
            </div>

            <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-xl transition group">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                    <i class="fas fa-shield-halved fa-2x"></i>
                </div>
                <h3 class="text-xl font-bold mb-3">Sikker Validering</h3>
                <p class="text-slate-600 leading-relaxed">Mulighed for MitID validering, så du altid ved, hvem du handler og deler med.</p>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 text-slate-400 py-12 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="bg-indigo-600 text-white p-1.5 rounded">
                    <i class="fas fa-users-rays"></i>
                </div>
                <span class="text-xl font-bold text-white tracking-tight">CAAS</span>
            </div>
            <p class="text-sm">&copy; 2026 CAAS Platform. Alle rettigheder forbeholdes.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-white transition">Vilkår</a>
                <a href="#" class="hover:text-white transition">Privatliv</a>
                <a href="#" class="hover:text-white transition">Kontakt</a>
            </div>
        </div>
    </footer>

</body>
</html>