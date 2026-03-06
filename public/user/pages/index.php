<?php
session_start();
include('../../db_connect.php');
include('../../includes/pages_functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /login/");
    exit();
}

$profile_id = $_SESSION['user_id'];
$pages = get_pages_by_profile($conn, $profile_id);

$created_msg = isset($_GET['created']) ? "Siden blev oprettet!" : "";

$page_title = "Mine sider";
include('../../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>Mine sider - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased font-sans">

    <main class="max-w-4xl mx-auto px-6 py-12">
        
        <?php if ($created_msg): ?>
            <div class="mb-8 bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-emerald-600 font-bold italic text-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $created_msg; ?>
            </div>
        <?php endif; ?>

        <div class="flex items-center justify-between mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tighter italic">Mine sider</h1>
                <p class="text-slate-400 mt-1 font-bold italic text-xs uppercase tracking-widest">Forretningssider under din profil</p>
            </div>
            <a href="new/" class="inline-flex items-center gap-2 bg-slate-900 text-white px-6 py-4 rounded-2xl font-black italic hover:bg-indigo-600 transition shadow-lg">
                <i class="fas fa-plus"></i> Opret side
            </a>
        </div>

        <div class="space-y-4">
            <?php if ($pages && $pages->num_rows > 0): ?>
                <?php while ($p = $pages->fetch_assoc()): ?>
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center justify-between hover:border-indigo-200 transition">
                        <div class="flex-1">
                            <a href="/pages/view.php?id=<?php echo $p['id']; ?>" class="block">
                                <h3 class="font-black text-xl italic hover:text-indigo-600 transition"><?php echo htmlspecialchars($p['name']); ?></h3>
                            </a>
                            <p class="text-slate-400 text-sm font-bold italic mt-1">/<?php echo htmlspecialchars($p['slug']); ?></p>
                            <?php
                            $community_name = '';
                            if (!empty($p['community_id'])) {
                                $cid = (int) $p['community_id'];
                                $c = $conn->query("SELECT name FROM communities WHERE id = '$cid'");
                                if ($c && $row = $c->fetch_assoc()) $community_name = $row['name'];
                            }
                            ?>
                            <?php if ($community_name): ?>
                                <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mt-2 italic">
                                    <i class="fas fa-users mr-1"></i> <?php echo htmlspecialchars($community_name); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($p['description'])): ?>
                                <p class="text-slate-500 text-sm mt-2"><?php echo htmlspecialchars($p['description']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="/pages/view.php?id=<?php echo $p['id']; ?>" class="text-indigo-600 hover:text-indigo-700 font-bold italic text-sm">
                                <i class="fas fa-store mr-1"></i> Shop
                            </a>
                            <a href="/pages/manage.php?id=<?php echo $p['id']; ?>" class="text-slate-500 hover:text-indigo-600 font-bold italic text-sm">
                                <i class="fas fa-cog mr-1"></i> Administrer
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="bg-white p-12 rounded-[2rem] border-2 border-dashed border-slate-200 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-2xl mx-auto mb-4 flex items-center justify-center text-2xl text-slate-300">
                        <i class="fas fa-file"></i>
                    </div>
                    <p class="text-slate-500 font-bold italic">Du har ikke oprettet nogen sider endnu.</p>
                    <a href="new/" class="inline-block mt-6 bg-slate-900 text-white px-6 py-3 rounded-2xl font-black italic hover:bg-indigo-600 transition">
                        Opret din første side
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
