<?php
session_start();
include('../db_connect.php');

$page_title = "Indstillinger";
include('../includes/header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

$user_id = $_SESSION['user_id'];

// Hent brugerdata
$user_query = $conn->query("SELECT * FROM users WHERE id = '$user_id'");
$user = $user_query->fetch_assoc();

// Beskeder ved succes/fejl
$status_msg = "";
if (isset($_GET['updated'])) $status_msg = "Profilen blev opdateret!";
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Indstillinger - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased">

    <main class="max-w-4xl mx-auto px-6 py-12">
        
        <?php if($status_msg): ?>
            <div class="mb-8 bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-emerald-600 font-bold italic text-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $status_msg; ?>
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-3 gap-10">
            
            <div class="space-y-6">
                <div class="bg-white p-8 rounded-[3rem] border border-slate-200 shadow-sm text-center">
                    <div class="w-24 h-24 bg-slate-100 rounded-[2rem] mx-auto mb-4 flex items-center justify-center text-3xl text-slate-300 border-2 border-dashed border-slate-200">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3 class="font-black text-xl italic italic leading-tight"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Bruger ID: #<?php echo $user_id; ?></p>
                </div>

                <div class="bg-slate-900 p-6 rounded-[2.5rem] text-white shadow-xl">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-white/10 rounded-xl flex items-center justify-center text-xs">
                            <i class="fas fa-shield-halved"></i>
                        </div>
                        <h4 class="font-black italic text-sm uppercase">Sikkerhed</h4>
                    </div>
                    <div class="space-y-4">
                        <div class="bg-white/5 p-4 rounded-2xl border border-white/10">
                            <p class="text-[10px] font-black text-indigo-300 uppercase italic">MitID Verificering</p>
                            <p class="text-xs font-bold text-slate-400 mt-1">Ikke tilsluttet</p>
                            <button class="mt-3 w-full bg-white text-slate-900 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-indigo-400 transition">Start Verificering</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 space-y-8">
                <div class="bg-white p-10 rounded-[3rem] border border-slate-200 shadow-sm">
                    <h2 class="text-2xl font-black italic mb-8 uppercase tracking-tighter">Ret din profil</h2>
                    
                    <form action="update_profile.php" method="POST" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">Fulde Navn</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" 
                                   class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 transition font-bold italic">
                        </div>

                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">E-mail Adresse</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 transition font-bold italic">
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-lg hover:bg-indigo-600 transition shadow-lg shadow-slate-200 italic">
                                Gem ændringer
                            </button>
                        </div>
                    </form>
                </div>

                <div class="p-6 border-2 border-dashed border-red-100 rounded-[2.5rem] flex items-center justify-between">
                    <div>
                        <h4 class="font-black text-red-600 italic uppercase text-sm">Slet konto</h4>
                        <p class="text-xs text-slate-400 font-bold italic">Dette kan ikke fortrydes.</p>
                    </div>
                    <button class="text-red-400 hover:text-red-600 transition font-black italic text-xs uppercase underline">Slet alt</button>
                </div>
            </div>

        </div>
    </main>

</body>
</html>