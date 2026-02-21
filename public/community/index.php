<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../dashboard/");
    exit();
}

$user_id = $_SESSION['user_id'];
$community_id = $conn->real_escape_string($_GET['id']);

// 1. Hent info om community + tjek medlemsskab
$sql = "SELECT c.*, m.role, m.alias_name 
        FROM communities c 
        JOIN community_members m ON c.id = m.community_id 
        WHERE c.id = '$community_id' AND m.user_id = '$user_id'";

$result = $conn->query($sql);
if ($result->num_rows == 0) { die("Du har ikke adgang til dette community."); }
$community = $result->fetch_assoc();

// 2. Håndter nyt opslag på væggen
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['post_content'])) {
    $content = $conn->real_escape_string($_POST['post_content']);
    $conn->query("INSERT INTO posts (community_id, user_id, content) VALUES ('$community_id', '$user_id', '$content')");
    header("Location: ./?id=" . $community_id);
    exit();
}

// 3. Hent de 3 nyeste ting til teaser
$assets_sql = "SELECT title, category FROM assets WHERE community_id = '$community_id' ORDER BY created_at DESC LIMIT 3";
$latest_assets = $conn->query($assets_sql);

// 4. Hent alle opslag på væggen
$posts = $conn->query("SELECT p.*, m.alias_name FROM posts p 
                       JOIN community_members m ON p.user_id = m.user_id 
                       WHERE p.community_id = '$community_id' AND m.community_id = '$community_id'
                       ORDER BY p.created_at DESC");

// 5. Kommende events til teaser
$upcoming_events = $conn->query("SELECT id, title, event_at FROM events 
    WHERE community_id = '$community_id' AND event_at >= NOW() 
    ORDER BY event_at ASC LIMIT 3");

$page_title = htmlspecialchars($community['name']);
include('../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title><?php echo htmlspecialchars($community['name']); ?> - CAAS</title>
</head>
<body class="bg-[#f8fafc] min-h-screen">

    <main class="max-w-7xl mx-auto px-6 py-10">
        <div class="grid lg:grid-cols-4 gap-8">
            
            <aside class="lg:col-span-1 space-y-2">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 ml-2">Menu</p>
                <a href="./?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 transition italic">
                    <i class="fas fa-bullhorn w-5"></i> Væggen
                </a>
                <a href="./dele/?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white hover:shadow-sm transition font-bold italic">
                    <i class="fas fa-tools w-5 text-indigo-500"></i> Deleøkonomi
                </a>
                <a href="./kalender/?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white hover:shadow-sm transition font-bold italic">
                    <i class="fas fa-calendar-days w-5 text-indigo-500"></i> Kalender
                </a>
            </aside>

            <div class="lg:col-span-3 space-y-8">
                
                <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm relative overflow-hidden">
                    <div class="relative z-10 flex justify-between items-start">
                        <div>
                            <h2 class="text-3xl font-black text-slate-900 italic"><?php echo htmlspecialchars($community['name']); ?></h2>
                            <p class="text-slate-500 mt-2 italic"><?php echo htmlspecialchars($community['description']); ?></p>
                        </div>
                        <?php if ($community['role'] == 'admin'): ?>
                            <div class="bg-indigo-50 border border-indigo-100 p-3 rounded-2xl text-center">
                                <p class="text-[10px] font-black uppercase text-indigo-400 mb-1">Invite Code</p>
                                <code class="text-indigo-700 font-mono font-bold"><?php echo $community['invite_code']; ?></code>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-8 items-start">
                    
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                            <form action="./?id=<?php echo $community_id; ?>" method="POST">
                                <textarea name="post_content" required placeholder="Skriv noget til væggen..." 
                                          class="w-full bg-slate-50 border-none rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 outline-none italic text-sm mb-4" rows="3"></textarea>
                                <button type="submit" class="w-full bg-slate-900 text-white py-3 rounded-xl font-bold hover:bg-indigo-600 transition italic">Del opslag</button>
                            </form>
                        </div>

                        <div class="space-y-4">
                            <?php while($post = $posts->fetch_assoc()): ?>
                                <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="font-black text-slate-900 italic text-sm"><?php echo htmlspecialchars($post['alias_name']); ?></span>
                                        <span class="text-[9px] text-slate-300 font-bold uppercase"><?php echo date('d. M - H:i', strtotime($post['created_at'])); ?></span>
                                    </div>
                                    <p class="text-slate-600 leading-relaxed italic text-sm">
                                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                                    </p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="space-y-6">
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-black text-slate-900 italic">Seneste i Deleøkonomi</h3>
                            <a href="./dele/?id=<?php echo $community_id; ?>" class="text-indigo-600 text-xs font-bold hover:underline italic">Se alle &rarr;</a>
                        </div>
                        <div class="space-y-3">
                            <?php if ($latest_assets->num_rows > 0): ?>
                                <?php while($item = $latest_assets->fetch_assoc()): ?>
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                                        <span class="text-sm font-bold text-slate-700 italic"><?php echo htmlspecialchars($item['title']); ?></span>
                                        <span class="text-[9px] bg-white border px-2 py-1 rounded-lg text-slate-400 font-black italic uppercase"><?php echo $item['category']; ?></span>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-slate-400 text-sm italic p-4 text-center">Ingen ting delt endnu...</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 sticky top-24">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="font-black text-slate-900 italic">Kommende events</h3>
                            <a href="./kalender/?id=<?php echo $community_id; ?>" class="text-indigo-600 text-xs font-bold hover:underline italic">Se alle &rarr;</a>
                        </div>
                        <div class="space-y-3">
                            <?php if ($upcoming_events->num_rows > 0): ?>
                                <?php while($ev = $upcoming_events->fetch_assoc()): ?>
                                    <a href="./kalender/view.php?id=<?php echo $community_id; ?>&event_id=<?php echo $ev['id']; ?>" class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100 hover:bg-indigo-50 hover:border-indigo-100 transition group">
                                        <span class="text-sm font-bold text-slate-700 italic group-hover:text-indigo-700"><?php echo htmlspecialchars($ev['title']); ?></span>
                                        <span class="text-[9px] bg-white border px-2 py-1 rounded-lg text-slate-400 font-black italic uppercase"><?php echo date('d/m H:i', strtotime($ev['event_at'])); ?></span>
                                    </a>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-slate-400 text-sm italic p-4 text-center">Ingen kommende events...</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    </div>

                </div> </div>
        </div>
    </main>

</body>
</html>