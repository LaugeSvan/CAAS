<?php
session_start();
include('../../db_connect.php');
include('../../includes/pages_functions.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../../dashboard/");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$community_id = (int) ($_GET['id'] ?? 0);

if ($community_id <= 0) {
    header("Location: ../../dashboard/");
    exit();
}

// Member check + community info
$check = $conn->query("SELECT c.*, m.role, m.alias_name
                      FROM communities c
                      JOIN community_members m ON c.id = m.community_id
                      WHERE c.id = '$community_id' AND m.user_id = '$user_id'");
if (!$check || $check->num_rows === 0) {
    die("Ingen adgang.");
}
$community = $check->fetch_assoc();

$pages = get_pages_by_community($conn, $community_id);

$page_title = htmlspecialchars($community['name']) . " - Sider";
include('../../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>Sider - <?php echo htmlspecialchars($community['name']); ?> - CAAS</title>
</head>
<body class="bg-[#f8fafc] min-h-screen antialiased">

    <main class="max-w-7xl mx-auto px-6 py-10">
        <div class="grid lg:grid-cols-4 gap-8">

            <aside class="lg:col-span-1 space-y-2">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 ml-2">Menu</p>
                <a href="../?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white hover:shadow-sm transition font-bold italic">
                    <i class="fas fa-bullhorn w-5 text-indigo-500"></i> Væggen
                </a>
                <a href="../dele/?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white hover:shadow-sm transition font-bold italic">
                    <i class="fas fa-tools w-5 text-indigo-500"></i> Deleøkonomi
                </a>
                <a href="../kalender/?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white hover:shadow-sm transition font-bold italic">
                    <i class="fas fa-calendar-days w-5 text-indigo-500"></i> Kalender
                </a>
                <a href="./?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 transition italic">
                    <i class="fas fa-store w-5"></i> Sider
                </a>
            </aside>

            <div class="lg:col-span-3 space-y-8">
                <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
                    <div class="flex items-start justify-between gap-6">
                        <div>
                            <h1 class="text-3xl font-black text-slate-900 italic">Sider i <?php echo htmlspecialchars($community['name']); ?></h1>
                            <p class="text-slate-500 mt-2 italic">Find lokale sider og deres shops.</p>
                        </div>
                        <a href="/user/pages/new/" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black italic hover:bg-indigo-600 transition">
                            <i class="fas fa-plus mr-2"></i> Opret side
                        </a>
                    </div>
                </div>

                <?php if ($pages && $pages->num_rows > 0): ?>
                    <div class="grid md:grid-cols-2 gap-6">
                        <?php while ($p = $pages->fetch_assoc()): ?>
                            <a href="/pages/view.php?id=<?php echo (int) $p['id']; ?>" class="block bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm hover:border-indigo-200 hover:shadow-md transition">
                                <h3 class="font-black text-2xl italic text-slate-900"><?php echo htmlspecialchars($p['name']); ?></h3>
                                <?php if (!empty($p['description'])): ?>
                                    <p class="text-slate-500 mt-2 italic"><?php echo htmlspecialchars($p['description']); ?></p>
                                <?php endif; ?>
                                <div class="mt-6 flex items-center justify-between">
                                    <span class="text-indigo-600 font-bold italic text-sm"><i class="fas fa-store mr-2"></i> Se shop</span>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-300">#<?php echo (int) $p['id']; ?></span>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-12 rounded-[2rem] border-2 border-dashed border-slate-200 text-center">
                        <div class="w-20 h-20 bg-slate-100 rounded-2xl mx-auto mb-4 flex items-center justify-center text-3xl text-slate-300">
                            <i class="fas fa-store"></i>
                        </div>
                        <p class="text-slate-500 font-bold italic">Der er ingen sider i dette community endnu.</p>
                        <a href="/user/pages/new/" class="inline-block mt-6 bg-slate-900 text-white px-6 py-3 rounded-2xl font-black italic hover:bg-indigo-600 transition">
                            Opret den første side
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>

