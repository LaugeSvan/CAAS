<?php
session_start();
$page_title = "Privatliv";
include('../includes/header.php');
?>
<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Privatlivspolitik - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased font-sans">

    <main class="max-w-4xl mx-auto px-6 py-16">
        <div class="mb-12">
            <h1 class="text-5xl font-black italic uppercase tracking-tighter">Privatlivspolitik <span class="text-indigo-600">.</span></h1>
            <p class="text-slate-400 mt-2 font-bold italic uppercase text-xs tracking-widest">Vi passer på dine data som var det vores egne</p>
        </div>

        <div class="space-y-8">
            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <div class="flex items-center gap-4 mb-4 text-indigo-600">
                    <i class="fas fa-database"></i>
                    <h3 class="text-xl font-black italic uppercase text-indigo-950">Hvilke data indsamler vi?</h3>
                </div>
                <p class="text-slate-500 font-medium italic leading-relaxed">
                    Vi indsamler kun de data, der er nødvendige for at drive dit community: Dit navn, din e-mailadresse og din aktivitet i forhold til lån og beskeder. Hvis du vælger MitID-validering, gemmer vi kun din "Verified"-status, aldrig dine personlige login-oplysninger.
                </p>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm">
                <div class="flex items-center gap-4 mb-4 text-indigo-600">
                    <i class="fas fa-share-nodes"></i>
                    <h3 class="text-xl font-black italic uppercase text-indigo-950">Deling med tredjepart</h3>
                </div>
                <p class="text-slate-500 font-medium italic leading-relaxed">
                    Vi sælger aldrig dine data. Dine oplysninger er kun synlige for medlemmer af de communities, du selv vælger at deltage i. Admins i dine communities kan se dit navn for at sikre tryghed i gruppen.
                </p>
            </div>

            <div class="bg-slate-900 p-8 rounded-[2.5rem] text-white shadow-xl">
                <div class="flex items-center gap-4 mb-4 text-indigo-400">
                    <i class="fas fa-trash-can"></i>
                    <h3 class="text-xl font-black italic uppercase">Retten til at blive glemt</h3>
                </div>
                <p class="text-indigo-100 font-medium italic mb-6 opacity-80">
                    Du ejer dine data. Hvis du ønsker at slette din profil, fjerner vi alle dine personlige oplysninger fra vores aktive databaser øjeblikkeligt.
                </p>
                <a href="mailto:caas+data@salsac.at" class="inline-block border border-indigo-400 text-indigo-400 px-6 py-3 rounded-xl font-black italic uppercase text-xs hover:bg-indigo-400 hover:text-white transition">Anmod om dataindsigt</a>
            </div>
        </div>
    </main>

</body>
</html>