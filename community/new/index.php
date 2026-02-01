<?php
session_start();
// Vi skal 3 mapper op (new -> community -> caas) for at finde db_connect
include('../../../db_connect.php'); 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $user_id = $_SESSION['user_id'];
    // Din smarte invitationskode-logik
    $invite_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

    $sql = "INSERT INTO communities (name, description, owner_id, invite_code) 
            VALUES ('$name', '$desc', '$user_id', '$invite_code')";

    if ($conn->query($sql) === TRUE) {
        $community_id = $conn->insert_id;
        $user_name = $_SESSION['user_name'];

        // Tilføj opretter som admin
        $conn->query("INSERT INTO community_members (user_id, community_id, alias_name, role) 
                      VALUES ('$user_id', '$community_id', '$user_name', 'admin')");

        header("Location: ../../dashboard/?created=true");
        exit();
    } else {
        $error = "Der skete en fejl: " . $conn->error;
    }
}

$page_title = "Nyt Fællesskab"; 
include('../../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Start nyt community - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased font-sans">

    <main class="max-w-3xl mx-auto px-6 py-12">
        <div class="bg-white p-10 rounded-[3rem] border border-slate-200 shadow-sm relative overflow-hidden">
            
            <div class="absolute -right-8 -top-8 text-slate-50 text-[12rem] font-black italic select-none">
                NEW
            </div>

            <div class="relative z-10">
                <div class="mb-10">
                    <h1 class="text-4xl font-black text-slate-900 uppercase tracking-tighter"><em>Skab noget nyt</em> 🏗️</h1>
                    <p class="text-slate-400 mt-2 font-bold italic uppercase text-xs tracking-[0.2em]">Byg fundamentet for dit fællesskab</p>
                </div>

                <?php if($error): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-2xl mb-8 font-bold italic text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="./" method="POST" class="space-y-8">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 italic">Hvad er navnet?</label>
                        <input type="text" name="name" required placeholder="f.eks. Solbakken Ejerforening" 
                               class="w-full bg-slate-50 border border-slate-100 px-6 py-5 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 transition font-black italic text-lg shadow-inner">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 italic">Beskrivelse</label>
                        <textarea name="description" rows="4" placeholder="Hvad samles I om her?" 
                                  class="w-full bg-slate-50 border border-slate-100 px-6 py-5 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 transition font-bold italic shadow-inner"></textarea>
                    </div>

                    <div class="bg-indigo-900 p-6 rounded-[2rem] text-white shadow-xl shadow-indigo-100">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-indigo-300">
                                <i class="fas fa-key text-sm"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300 italic">Invitationskode</p>
                                <p class="text-xs font-bold text-white/80 mt-0.5">Vi genererer automatisk en unik kode, som du kan dele med dine naboer bagefter.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-slate-900 text-white py-6 rounded-2xl font-black text-xl hover:bg-indigo-600 transition shadow-2xl shadow-slate-200 italic uppercase tracking-tighter">
                            Opret Fællesskab <i class="fas fa-arrow-right ml-2 text-sm"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>