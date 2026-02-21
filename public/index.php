<?php
session_start();
// Hvis man allerede er logget ind, send dem direkte til dashboardet
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/");
    exit();
}
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAAS - Communities As A Service</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient { background: radial-gradient(circle at top right, #4f46e5 0%, #1e1b4b 100%); }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased">

    <nav class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200 py-4 px-6">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2">
                <h1 class="font-black text-2xl text-indigo-950 tracking-tight italic uppercase">
                    CAAS
                </h1>
            </div>
            <div class="flex items-center gap-6">
                <a href="login/" class="text-sm font-black italic uppercase text-slate-400 hover:text-indigo-600 transition">Log ind</a>
                <a href="opret/" class="bg-slate-900 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase italic hover:bg-indigo-600 transition shadow-xl shadow-slate-200">
                    Kom i gang
                </a>
            </div>
        </div>
    </nav>

    <header class="hero-gradient text-white py-32 px-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full flex items-center justify-center opacity-5 pointer-events-none select-none">
            <h2 class="text-[30vw] font-black italic uppercase tracking-tighter">CAAS</h2>
        </div>

        <div class="max-w-5xl mx-auto text-center relative z-10">
            
            <h1 class="text-6xl md:text-8xl font-black mb-8 leading-[0.9] italic uppercase tracking-tighter">
                Gør dit fællesskab <br><span class="text-indigo-400">stærkere.</span>
            </h1>
            
            <p class="text-lg md:text-xl mb-12 text-indigo-100/80 max-w-2xl mx-auto font-medium italic">
                Deleøkonomi, booking og sikker kommunikation. <br>
                Vi har fjernet støjen – og beholdt fællesskabet.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="login/" class="bg-white text-slate-900 px-12 py-5 rounded-[2rem] font-black text-xl shadow-2xl hover:scale-105 transition-transform italic uppercase tracking-tighter">
                    Opret gratis profil
                </a>
            </div>
        </div>
    </header>

    <section class="py-24 px-6 max-w-7xl mx-auto">
        <div class="flex items-center gap-4 mb-16">
            <h2 class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 italic">Værktøjskassen</h2>
            <div class="h-[1px] flex-1 bg-slate-200"></div>
        </div>

        <div class="grid md:grid-cols-3 gap-12">
            <div class="group">
                <div class="w-16 h-16 bg-white border border-slate-200 rounded-[2rem] flex items-center justify-center mb-8 shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 text-slate-400">
                    <i class="fas fa-hand-holding-heart fa-xl"></i>
                </div>
                <h3 class="text-2xl font-black mb-4 italic uppercase tracking-tighter">Deleøkonomi</h3>
                <p class="text-slate-500 font-medium italic leading-relaxed">
                    Lån naboens trailer eller boremaskine. Komplet log over udstyr og anmeldelser.
                </p>
            </div>

            <div class="group">
                <div class="w-16 h-16 bg-white border border-slate-200 rounded-[2rem] flex items-center justify-center mb-8 shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 text-slate-400">
                    <i class="fas fa-calendar-check fa-xl"></i>
                </div>
                <h3 class="text-2xl font-black mb-4 italic uppercase tracking-tighter">Smart Booking</h3>
                <p class="text-slate-500 font-medium italic leading-relaxed">
                    Fælleshus, vaskeri eller gæsteværelse. Book på sekunder med automatisk godkendelse.
                </p>
            </div>

            <div class="group">
                <div class="w-16 h-16 bg-white border border-slate-200 rounded-[2rem] flex items-center justify-center mb-8 shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 text-slate-400">
                    <i class="fas fa-shield-halved fa-xl"></i>
                </div>
                <h3 class="text-2xl font-black mb-4 italic uppercase tracking-tighter">MitID Validering</h3>
                <p class="text-slate-500 font-medium italic leading-relaxed">
                    Sikkerhed i højsædet. Ved præcis hvem du deler dine ting med gennem fuld validering.
                </p>
            </div>
        </div>
    </section>

    <footer class="bg-white border-t border-slate-200 py-12 px-6">
        <div class="max-w-7xl mx-auto flex flex-col md:row justify-between items-center gap-10">
            <h1 class="font-black text-xl text-indigo-950 tracking-tight italic uppercase">
                CAAS
            </h1>
            <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest">&copy; SalsaCat. Designet til mennesker.</p>
            <div class="flex gap-8 text-[10px] font-black uppercase tracking-widest italic text-slate-400">
                <a href="/legal/vilkaar.php" class="hover:text-indigo-600 transition">Vilkår</a>
                <a href="/legal/privatliv.php" class="hover:text-indigo-600 transition">Privatliv</a>
            </div>
        </div>
    </footer>

</body>
</html>