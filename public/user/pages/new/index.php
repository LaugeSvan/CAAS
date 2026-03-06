<?php
session_start();
include('../../../db_connect.php');
include('../../../includes/pages_functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /login/");
    exit();
}

$error = "";
$profile_id = (int) $_SESSION['user_id'];
$communities = get_user_communities($conn, $profile_id);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $community_id = (int) ($_POST['community_id'] ?? 0);
    $page_id = create_page_under_profile($conn, $profile_id, $community_id, [
        'name' => $_POST['name'] ?? '',
        'description' => $_POST['description'] ?? '',
    ]);

    if ($page_id) {
        header("Location: /user/pages/?created=$page_id");
        exit();
    } else {
        $error = "Der skete en fejl. Tjek at navn er udfyldt og at du har valgt et community.";
    }
}

$page_title = "Opret side";
include('../../../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>Opret side - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased font-sans">

    <main class="max-w-3xl mx-auto px-6 py-12">
        <div class="bg-white p-10 rounded-[3rem] border border-slate-200 shadow-sm relative overflow-hidden">
            
            <div class="absolute -right-8 -top-8 text-slate-50 text-[10rem] font-black italic select-none">
                PAGE
            </div>

            <div class="relative z-10">
                <div class="mb-10">
                    <h1 class="text-4xl font-black text-slate-900 uppercase tracking-tighter"><em>Ny forretningsside</em></h1>
                    <p class="text-slate-400 mt-2 font-bold italic uppercase text-xs tracking-[0.2em]">Sider tilhører din profil</p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-2xl mb-8 font-bold italic text-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="./" method="POST" class="space-y-8">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 italic">Community</label>
                        <select name="community_id" required
                                class="w-full bg-slate-50 border border-slate-100 px-6 py-5 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 transition font-black italic text-lg shadow-inner">
                            <option value="" selected disabled>Vælg community…</option>
                            <?php if ($communities && $communities->num_rows > 0): ?>
                                <?php while ($c = $communities->fetch_assoc()): ?>
                                    <option value="<?php echo (int) $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                        <?php if (!$communities || $communities->num_rows === 0): ?>
                            <p class="mt-2 text-xs text-slate-400 font-bold italic">Du er ikke medlem af nogen communities endnu. Join et community først.</p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 italic">Navn på siden</label>
                        <input type="text" name="name" required placeholder="f.eks. Min pizzaria" 
                               class="w-full bg-slate-50 border border-slate-100 px-6 py-5 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 transition font-black italic text-lg shadow-inner">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3 italic">Beskrivelse</label>
                        <textarea name="description" rows="4" placeholder="Hvad handler siden om?" 
                                  class="w-full bg-slate-50 border border-slate-100 px-6 py-5 rounded-2xl outline-none focus:ring-4 focus:ring-indigo-600/10 focus:border-indigo-600 transition font-bold italic shadow-inner"></textarea>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-slate-900 text-white py-6 rounded-2xl font-black text-xl hover:bg-indigo-600 transition shadow-2xl shadow-slate-200 italic uppercase tracking-tighter">
                            Opret side <i class="fas fa-arrow-right ml-2 text-sm"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

</body>
</html>
