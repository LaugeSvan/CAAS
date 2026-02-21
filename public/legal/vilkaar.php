<?php
session_start();
$page_title = "Vilkår";
// Vi går to niveauer op for at finde includes (legal -> caas)
include('../includes/header.php');
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Vilkår - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased">

    <main class="max-w-4xl mx-auto px-6 py-16">
        <div class="mb-12">
            <h1 class="text-5xl font-black italic uppercase tracking-tighter">Vilkår for brug <span class="text-indigo-600">.</span></h1>
            <p class="text-slate-400 mt-2 font-bold italic uppercase text-xs tracking-widest text-slate-400">Senest opdateret: Februar 2026</p>
        </div>

        <div class="space-y-8">
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <h3 class="text-xl font-black italic uppercase mb-4 text-indigo-950">1. Platformens rolle</h3>
                <p class="text-slate-500 font-medium italic leading-relaxed">
                    CAAS stiller teknologien til rådighed for at facilitere deling mellem naboer. Vi ejer ikke udstyret og er ikke part i de aftaler, der indgås mellem brugere.
                </p>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <h3 class="text-xl font-black italic uppercase mb-4 text-indigo-950">2. Ansvar ved udlån</h3>
                <p class="text-slate-500 font-medium italic leading-relaxed">
                    Alt udlån sker på eget ansvar. Vi anbefaler altid, at man tjekker sin egen indboforsikring, før man udlåner eller lejer dyrt værktøj eller køretøjer.
                </p>
            </div>

            <div class="bg-indigo-900 p-8 rounded-[2.5rem] text-white shadow-xl">
                <h3 class="text-xl font-black italic uppercase mb-4">3. Spørgsmål?</h3>
                <p class="text-indigo-200 font-medium italic mb-6">Er du i tvivl om reglerne i dit specifikke community? Kontakt Admin.</p>
                <a href="mailto:caas+support@salsac.at" class="inline-block bg-white text-indigo-900 px-6 py-3 rounded-xl font-black italic uppercase text-xs hover:bg-indigo-50 transition">Kontakt Support</a>
            </div>
        </div>
    </main>

</body>
</html>