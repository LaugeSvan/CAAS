<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// 1. Hent de communities brugeren er medlem af
$sql_comm = "SELECT c.id, c.name, c.description, m.role, m.alias_name 
        FROM communities c 
        JOIN community_members m ON c.id = m.community_id 
        WHERE m.user_id = '$user_id'
        ORDER BY m.joined_at DESC";
$my_communities = $conn->query($sql_comm);

// 2. NYT: Tjek om der er anmodninger ejeren skal godkende (Notifikation)
$sql_pending_owner = "SELECT COUNT(*) as pending_count 
                      FROM reservations r 
                      JOIN assets a ON r.asset_id = a.id 
                      WHERE a.owner_id = '$user_id' AND r.status = 'pending'";
$pending_owner_res = $conn->query($sql_pending_owner)->fetch_assoc();
$has_actions = $pending_owner_res['pending_count'] > 0;

// 3. Hent kommende reservationer (Inkluderer nu status)
$sql_res = "SELECT r.start_date, r.end_date, r.status, a.title, c.name as community_name, c.id as community_id
            FROM reservations r
            JOIN assets a ON r.asset_id = a.id
            JOIN communities c ON a.community_id = c.id
            WHERE r.user_id = '$user_id' AND r.end_date >= CURRENT_DATE
            ORDER BY r.start_date ASC LIMIT 5";
$my_reservations = $conn->query($sql_res);
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Dashboard - CAAS</title>
    <style>
        html, body { max-width: 100%; overflow-x: hidden; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased">

<nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="text-indigo-600"><i class="fas fa-house"></i></div>
                <h1 class="font-black text-xl text-indigo-950 tracking-tight italic">
                    CAAS <span class="text-slate-300 font-light mx-2">|</span> Dashboard
                </h1>
            </div>
            <div class="text-sm font-medium text-slate-500 italic">
                Logget ind som: <span class="text-slate-900 font-bold"><?php echo htmlspecialchars($user_name); ?></span>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
            <div>
                <h1 class="text-5xl font-black text-slate-900 tracking-tighter italic">Velkommen hjem! 👋</h1>
                <p class="text-slate-400 mt-2 text-lg italic font-medium uppercase tracking-tight">Du er med i <?php echo $my_communities->num_rows; ?> fællesskaber.</p>
            </div>
            
            <?php if ($has_actions): ?>
            <div class="bg-amber-400 p-4 rounded-3xl shadow-xl shadow-amber-100 flex items-center gap-4 animate-bounce">
                <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-amber-500">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <p class="text-xs font-black uppercase text-amber-900 leading-none">Naboer venter!</p>
                    <p class="text-amber-800 text-[11px] font-bold italic">Du har anmodninger at godkende.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid lg:grid-cols-3 gap-10">
            
            <div class="lg:col-span-2 space-y-8">
                <div class="flex items-center gap-3">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Dine Communities</h3>
                    <div class="h-[1px] flex-1 bg-slate-200"></div>
                </div>
                
                <?php if ($my_communities->num_rows > 0): ?>
                    <div class="grid sm:grid-cols-2 gap-6">
                        <?php while($row = $my_communities->fetch_assoc()): ?>
                            <div class="group bg-white p-8 rounded-[2.5rem] border border-slate-200 shadow-sm hover:shadow-2xl hover:border-indigo-500 transition-all duration-500 relative overflow-hidden">
                                <div class="flex justify-between items-start mb-6">
                                    <div class="w-12 h-12 bg-slate-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl shadow-inner group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-500">
                                        <i class="fas fa-door-open"></i>
                                    </div>
                                    <span class="text-[9px] font-black uppercase tracking-widest bg-slate-100 text-slate-500 px-3 py-1 rounded-lg">
                                        <?php echo $row['role']; ?>
                                    </span>
                                </div>
                                
                                <h4 class="text-2xl font-black text-slate-900 mb-2 italic tracking-tighter group-hover:text-indigo-600 transition-colors"><?php echo htmlspecialchars($row['name']); ?></h4>
                                <p class="text-slate-400 text-sm font-medium italic mb-8 line-clamp-2 italic">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </p>
                                
                                <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                                    <span class="text-[10px] font-black text-slate-300 uppercase tracking-tighter italic">
                                        <i class="fas fa-id-badge mr-1"></i> <?php echo htmlspecialchars($row['alias_name']); ?>
                                    </span>
                                    <a href="../community/?id=<?php echo $row['id']; ?>" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-xs font-black hover:bg-indigo-600 transition italic shadow-sm">
                                        Indgang <i class="fas fa-chevron-right ml-1 text-[8px]"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-[3rem] border-2 border-dashed border-slate-200 p-16 text-center">
                        <p class="text-slate-400 font-black italic uppercase tracking-tighter">Du har ikke fundet din klan endnu...</p>
                        <a href="../udforsk/" class="mt-4 inline-block text-indigo-600 font-black italic underline decoration-2 underline-offset-4">Gå på opdagelse</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-10">
                
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Aktive Aftaler 📅</h3>
                    <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden p-2">
                        <?php if ($my_reservations->num_rows > 0): ?>
                            <div class="space-y-1">
                                <?php while($res = $my_reservations->fetch_assoc()): ?>
                                    <a href="../community/dele/?id=<?php echo $res['community_id']; ?>" class="block p-4 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <p class="text-[9px] font-black uppercase text-indigo-400 mb-1"><?php echo $res['community_name']; ?></p>
                                                <h5 class="font-black text-slate-900 text-sm italic"><?php echo htmlspecialchars($res['title']); ?></h5>
                                            </div>
                                            <span class="text-[8px] font-black uppercase px-2 py-1 rounded-md <?php echo $res['status'] == 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-emerald-100 text-emerald-600'; ?>">
                                                <?php echo $res['status'] == 'pending' ? 'Venter' : 'OK'; ?>
                                            </span>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-400 mt-2 uppercase tracking-tighter">
                                            <i class="far fa-calendar-alt mr-1"></i> 
                                            <?php echo date('d/m', strtotime($res['start_date'])); ?> - <?php echo date('d/m', strtotime($res['end_date'])); ?>
                                        </p>
                                    </a>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="p-8 text-center italic text-xs text-slate-400 font-bold uppercase tracking-tighter">
                                Ingen lån i kalenderen...
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-indigo-900 rounded-[2rem] p-8 text-white shadow-2xl shadow-indigo-100 relative group overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 text-white/5 text-8xl group-hover:scale-110 transition-transform duration-700">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h4 class="font-black text-xl mb-3 italic tracking-tighter">AI Lab 🤖</h4>
                    <p class="text-indigo-200 text-xs font-medium italic leading-relaxed opacity-80 mb-6">
                        Vi træner CAAS til at læse din lejekontrakt, så du ikke behøver. Coming soon!
                    </p>
                    <div class="h-1.5 w-full bg-white/10 rounded-full overflow-hidden">
                        <div class="bg-indigo-400 h-full w-2/5 shadow-[0_0_10px_rgba(129,140,248,0.5)]"></div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] p-6 border border-slate-200 shadow-sm">
                    <div class="flex items-center gap-3 text-xs font-black italic text-slate-400 uppercase tracking-widest">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                        CAAS Core: Live
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>