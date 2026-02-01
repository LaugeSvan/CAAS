<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// 1. Hent de communities brugeren ER medlem af
$sql_comm = "SELECT c.id, c.name, c.description, m.role, m.alias_name 
        FROM communities c 
        JOIN community_members m ON c.id = m.community_id 
        WHERE m.user_id = '$user_id'
        ORDER BY m.joined_at DESC";
$my_communities = $conn->query($sql_comm);

// 2. Tjek for pending anmodninger (Notifikation til ejer)
$sql_pending_owner = "SELECT COUNT(*) as pending_count 
                      FROM reservations r 
                      JOIN assets a ON r.asset_id = a.id 
                      WHERE a.owner_id = '$user_id' AND r.status = 'pending'";
$pending_owner_res = $conn->query($sql_pending_owner)->fetch_assoc();
$has_actions = $pending_owner_res['pending_count'] > 0;

// 3. Hent kommende reservationer
$sql_res = "SELECT r.start_date, r.end_date, r.status, a.title, c.name as community_name, c.id as community_id
            FROM reservations r
            JOIN assets a ON r.asset_id = a.id
            JOIN communities c ON a.community_id = c.id
            WHERE r.user_id = '$user_id' AND r.end_date >= CURRENT_DATE
            ORDER BY r.start_date ASC LIMIT 5";
$my_reservations = $conn->query($sql_res);

// 4. UDFORSK LOGIK: Hent communities brugeren IKKE er medlem af
$sql_explore = "SELECT * FROM communities 
                WHERE id NOT IN (SELECT community_id FROM community_members WHERE user_id = '$user_id') 
                LIMIT 4";
$explore_communities = $conn->query($sql_explore);

$page_title = "Dashboard"; 
include('../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Dashboard - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased font-sans">

    <main class="max-w-7xl mx-auto px-6 py-10">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
            <div>
                <h1 class="text-5xl font-black text-slate-900 tracking-tighter italic uppercase">Velkommen hjem! 👋</h1>
                <p class="text-slate-400 mt-2 text-lg italic font-medium uppercase tracking-tight">Du er aktiv i <?php echo $my_communities->num_rows; ?> fællesskaber</p>
            </div>
            
            <?php if ($has_actions): ?>
            <div class="bg-amber-400 p-4 rounded-3xl shadow-xl shadow-amber-100 flex items-center gap-4 animate-bounce">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-amber-500 shadow-sm">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <p class="text-xs font-black uppercase text-amber-900 leading-none tracking-tighter">Naboer venter!</p>
                    <p class="text-amber-800 text-[10px] font-bold italic uppercase mt-1">Tjek dine anmodninger</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-2 space-y-16">
                
                <section class="space-y-6">
                    <div class="flex items-center justify-between gap-3 mb-6">
                        <div class="flex items-center gap-3 flex-1">
                            <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">Dine Communities</h3>
                            <div class="h-[1px] flex-1 bg-slate-200"></div>
                        </div>
                        <a href="../community/new/" class="bg-indigo-600 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase italic hover:bg-slate-900 transition shadow-lg shadow-indigo-100 flex items-center gap-2">
                            <i class="fas fa-plus text-[8px]"></i> Start nyt
                        </a>
                    </div>
                    
                    <?php if ($my_communities->num_rows > 0): ?>
                        <div class="grid sm:grid-cols-2 gap-6">
                            <?php while($row = $my_communities->fetch_assoc()): ?>
                                <div class="group bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm hover:shadow-2xl hover:border-indigo-500 transition-all duration-500 relative overflow-hidden">
                                    <h4 class="text-2xl font-black text-slate-900 mb-2 italic tracking-tighter group-hover:text-indigo-600 transition-colors uppercase"><?php echo htmlspecialchars($row['name']); ?></h4>
                                    <p class="text-slate-400 text-sm font-medium italic mb-8 line-clamp-2"><?php echo htmlspecialchars($row['description']); ?></p>
                                    <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                                        <span class="text-[10px] font-black text-slate-300 uppercase italic">
                                            <i class="fas fa-id-badge mr-1"></i> <?php echo htmlspecialchars($row['alias_name']); ?>
                                        </span>
                                        <a href="../community/?id=<?php echo $row['id']; ?>" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-[10px] font-black hover:bg-indigo-600 transition italic shadow-sm uppercase tracking-widest">Indgang</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="bg-white rounded-[3rem] border-2 border-dashed border-slate-200 p-16 text-center">
                            <p class="text-slate-400 font-black italic uppercase tracking-tighter">Ingen fællesskaber endnu...</p>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="space-y-6">
                    <div class="flex items-center gap-3">
                        <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">Udforsk nye fællesskaber</h3>
                        <div class="h-[1px] flex-1 bg-slate-200"></div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-6">
                        <?php if ($explore_communities->num_rows > 0): ?>
                            <?php while($ex = $explore_communities->fetch_assoc()): ?>
                                <div class="bg-indigo-50/50 p-6 rounded-[2.5rem] border border-indigo-100 flex justify-between items-center group hover:bg-white hover:border-indigo-300 transition-all duration-300">
                                    <div>
                                        <h4 class="font-black italic text-indigo-950 uppercase tracking-tighter leading-tight"><?php echo htmlspecialchars($ex['name']); ?></h4>
                                        <p class="text-[9px] font-black text-indigo-400 italic uppercase tracking-widest mt-1">Åbent for alle</p>
                                    </div>
                                    <a href="join.php?id=<?php echo $ex['id']; ?>" class="w-12 h-12 bg-white text-indigo-600 rounded-2xl flex items-center justify-center shadow-sm group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 border border-indigo-100">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-span-2 py-8 text-center bg-slate-50 rounded-[2.5rem] border border-slate-100">
                                <p class="text-[10px] font-black text-slate-300 uppercase italic">Der er ikke flere grupper at opdage lige nu</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <div class="space-y-10">
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 italic">Aktive Aftaler 📅</h3>
                    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden p-2">
                        <?php if ($my_reservations->num_rows > 0): ?>
                            <div class="space-y-1">
                                <?php while($res = $my_reservations->fetch_assoc()): ?>
                                    <a href="../community/dele/?id=<?php echo $res['community_id']; ?>" class="block p-4 rounded-2xl hover:bg-slate-50 transition border border-transparent">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-[9px] font-black uppercase text-indigo-400 mb-1"><?php echo $res['community_name']; ?></p>
                                                <h5 class="font-black text-slate-900 text-sm italic"><?php echo htmlspecialchars($res['title']); ?></h5>
                                            </div>
                                            <span class="text-[8px] font-black uppercase px-2 py-1 rounded-md <?php echo $res['status'] == 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600'; ?>">
                                                <?php echo $res['status'] == 'pending' ? 'Venter' : 'OK'; ?>
                                            </span>
                                        </div>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center italic text-xs text-slate-400 font-bold uppercase tracking-tighter leading-relaxed">Du har ingen<br>aktive lån i kalenderen.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-indigo-900 rounded-[2rem] p-8 text-white shadow-2xl relative group overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl transition-transform duration-700 group-hover:scale-110">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h4 class="font-black text-xl mb-3 italic uppercase tracking-tighter">AI Lab 🤖</h4>
                    <p class="text-indigo-200 text-[10px] font-medium italic leading-relaxed opacity-70 mb-6">Vi tester sprogmodeller til læsning af referater. Følg med her.</p>
                    <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                        <div class="bg-indigo-400 h-full w-2/5"></div>
                    </div>
                </div>
                
                <div class="bg-white rounded-[2rem] p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 text-[9px] font-black italic text-slate-400 uppercase tracking-[0.2em]">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)] animate-pulse"></span>
                        CAAS Core v.1.0.4
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>