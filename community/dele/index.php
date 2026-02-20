<?php
session_start();
include('../../db_connect.php');

if (!isset($_GET['id'])) {
    header("Location: ../../dashboard/");
    exit();
}

$community_id = $conn->real_escape_string($_GET['id']);
$user_id = $_SESSION['user_id'];

// 1. Sikkerhedstjek & Community info
$check = $conn->query("SELECT c.*, m.role, m.alias_name FROM communities c 
                      JOIN community_members m ON c.id = m.community_id 
                      WHERE c.id = '$community_id' AND m.user_id = '$user_id'");

if ($check->num_rows == 0) { die("Ingen adgang."); }
$community = $check->fetch_assoc();

// 2. Hent ALT grej i dette community (undtagen mit eget)
$others_assets = $conn->query("SELECT a.*, u.name as owner_name,
        (SELECT COUNT(*) FROM reservations r 
         WHERE r.asset_id = a.id AND r.status = 'confirmed' AND CURRENT_DATE BETWEEN r.start_date AND r.end_date) as is_busy
        FROM assets a JOIN users u ON a.owner_id = u.id 
        WHERE a.community_id = '$community_id' AND a.owner_id != '$user_id'
        ORDER BY a.created_at DESC");

// 3. Hent KUN mit eget grej
$my_assets = $conn->query("SELECT a.*, 
        (SELECT COUNT(*) FROM reservations r 
         WHERE r.asset_id = a.id AND r.status = 'confirmed' AND CURRENT_DATE BETWEEN r.start_date AND r.end_date) as is_busy
        FROM assets a 
        WHERE a.community_id = '$community_id' AND a.owner_id = '$user_id'
        ORDER BY a.created_at DESC");

// 4. Hent aktive/kommende bookinger på MINE ting
$my_lendings = $conn->query("SELECT r.*, a.title, m.alias_name as borrower_name 
    FROM reservations r
    JOIN assets a ON r.asset_id = a.id
    JOIN community_members m ON r.user_id = m.user_id AND m.community_id = a.community_id
    WHERE a.owner_id = '$user_id' 
    AND a.community_id = '$community_id'
    AND r.end_date >= CURRENT_DATE
    ORDER BY r.start_date ASC");

// 5. Hent ting JEG har booket hos andre (aktive/kommende)
$my_borrowings = $conn->query("SELECT r.*, a.title, m.alias_name as owner_alias 
    FROM reservations r
    JOIN assets a ON r.asset_id = a.id
    JOIN community_members m ON a.owner_id = m.user_id AND m.community_id = a.community_id
    WHERE r.user_id = '$user_id' 
    AND r.end_date >= CURRENT_DATE
    ORDER BY r.start_date ASC");

$page_title = htmlspecialchars($community['name']); 
include('../../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>Deleøkonomi - CAAS</title>
    <style>
    html, body {
        max-width: 100%;
        overflow-x: hidden;
    }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen antialiased">

    <main class="max-w-7xl mx-auto px-6 py-10">
        <?php if (isset($_GET['status']) && $_GET['status'] == 'approved'): ?>
            <div class="mb-8 bg-emerald-50 border border-emerald-200 p-6 rounded-[2rem] flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-500 text-white rounded-2xl flex items-center justify-center text-xl">
                    <i class="fas fa-check"></i>
                </div>
                <div>
                    <h4 class="font-black text-emerald-900 italic">Udlån godkendt! 🤝</h4>
                    <p class="text-emerald-700 text-sm italic font-medium">Du har nu hjulpet en nabo. Systemet har automatisk ryddet op i andre anmodninger.</p>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['booked'])): ?>
            <div class="mb-8 bg-amber-50 border border-amber-200 p-6 rounded-[2rem] flex items-center gap-4 animate-in fade-in slide-in-from-top-4 duration-500">
                <div class="w-12 h-12 bg-amber-400 text-white rounded-2xl flex items-center justify-center text-xl shadow-lg shadow-amber-200">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div>
                    <h4 class="font-black text-amber-900 italic">Anmodning sendt! 🚀</h4>
                    <p class="text-amber-700 text-sm italic font-medium">Vi har givet ejeren besked. Du får besked her på siden, når den er godkendt.</p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'cancelled'): ?>
            <div class="mb-8 bg-slate-100 border border-slate-200 p-6 rounded-[2rem] flex items-center gap-4">
                <div class="w-12 h-12 bg-white text-slate-400 rounded-2xl flex items-center justify-center text-xl shadow-sm">
                    <i class="fas fa-trash-alt"></i>
                </div>
                <div>
                    <h4 class="font-black text-slate-900 italic">Booking aflyst</h4>
                    <p class="text-slate-500 text-sm italic font-medium">Reservationen er nu fjernet og tingen er ledig for andre.</p>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid lg:grid-cols-4 gap-8">
            
            <aside class="lg:col-span-1 space-y-2">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 ml-2">Menu</p>
                <a href="../?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white transition font-bold italic">
                    <i class="fas fa-bullhorn w-5 text-indigo-400"></i> Væggen
                </a>
                <a href="./?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 transition italic">
                    <i class="fas fa-tools w-5"></i> Deleøkonomi
                </a>
                <a href="../kalender/?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white transition font-bold italic">
                    <i class="fas fa-calendar-days w-5 text-indigo-500"></i> Kalender
                </a>
            </aside>

            <div class="lg:col-span-3 space-y-12">
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 italic tracking-tight">Grej & Deleøkonomi 🛠️</h2>
                        <p class="text-slate-500 italic text-sm">Find udstyr eller del dit eget med naboerne.</p>
                    </div>
                    <a href="new/?id=<?php echo $community_id; ?>" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition flex items-center gap-2 italic">
                        <i class="fas fa-plus text-xs"></i> Tilføj ting
                    </a>
                </div>

                <?php if ($my_borrowings->num_rows > 0): ?>
                <section class="mb-10">
                    <div class="flex items-center gap-3 border-b border-slate-200 pb-2 mb-4">
                        <h3 class="text-sm font-black uppercase tracking-widest text-emerald-500 italic">Dine lån</h3>
                        <span class="bg-emerald-100 text-emerald-600 text-[10px] px-2 py-0.5 rounded-full font-bold italic">Husk at aflevere!</span>
                    </div>
                    
                    <div class="grid sm:grid-cols-2 gap-4">
                        <?php while($borrow = $my_borrowings->fetch_assoc()): ?>
                        <div class="bg-white border-l-4 <?php echo $borrow['status'] == 'pending' ? 'border-amber-400' : 'border-emerald-500'; ?> rounded-2xl p-5 shadow-sm flex items-center justify-between group hover:shadow-md transition">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 <?php echo $borrow['status'] == 'pending' ? 'bg-amber-50 text-amber-500' : 'bg-emerald-50 text-emerald-600'; ?> rounded-xl flex items-center justify-center text-sm">
                                    <i class="fas <?php echo $borrow['status'] == 'pending' ? 'fa-hourglass-half' : 'fa-check-double'; ?>"></i>
                                </div>
                                <div>
                                    <h4 class="font-black text-slate-900 italic leading-tight">
                                        <?php echo htmlspecialchars($borrow['title']); ?>
                                        <?php if($borrow['status'] == 'pending'): ?>
                                            <span class="text-[9px] bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full ml-2 uppercase tracking-tighter">Venter...</span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="text-[11px] text-slate-400 font-bold italic">Hos: <?php echo htmlspecialchars($borrow['owner_alias']); ?></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-[9px] font-black uppercase text-slate-300 tracking-tighter">Retur dato</p>
                                    <p class="text-sm font-black <?php echo $borrow['status'] == 'pending' ? 'text-slate-400' : 'text-emerald-600'; ?> italic">
                                        <?php echo date('d/m', strtotime($borrow['end_date'])); ?>
                                    </p>
                                </div>
                                
                                <a href="cancel_booking.php?id=<?php echo $community_id; ?>&res_id=<?php echo $borrow['id']; ?>" 
                                onclick="return confirm('Vil du aflyse din booking?')"
                                class="w-8 h-8 bg-slate-50 text-slate-300 rounded-lg flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition shadow-sm">
                                    <i class="fas fa-times text-xs"></i>
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    </div>
                </section>
                <?php endif; ?>

                <?php if ($my_lendings->num_rows > 0): ?>
                <section class="space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-200 pb-2">
                        <h3 class="text-sm font-black uppercase tracking-widest text-indigo-400 italic">Hvem låner dit grej?</h3>
                    </div>
                    <div class="bg-indigo-900 rounded-[2rem] p-8 text-white shadow-xl shadow-indigo-100">
                        <div class="space-y-4">
                            <?php while($loan = $my_lendings->fetch_assoc()): ?>
                                <div class="flex items-center justify-between bg-white/10 p-4 rounded-2xl border border-white/5 mb-4">
                                    <div class="flex items-center gap-4 text-white">
                                        <i class="fas <?php echo $loan['status'] == 'pending' ? 'fa-clock text-amber-400' : 'fa-check text-emerald-400'; ?>"></i>
                                        <div>
                                            <p class="text-[10px] font-bold opacity-60 uppercase"><?php echo htmlspecialchars($loan['borrower_name']); ?> spørger om:</p>
                                            <h4 class="font-black italic"><?php echo htmlspecialchars($loan['title']); ?></h4>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <?php if ($loan['status'] == 'pending'): ?>
                                            <a href="approve_booking.php?id=<?php echo $community_id; ?>&res_id=<?php echo $loan['id']; ?>" 
                                            class="bg-emerald-500 hover:bg-emerald-400 text-white px-4 py-2 rounded-xl text-[10px] font-black transition italic shadow-lg">
                                                GODKEND
                                            </a>
                                            <a href="reject_booking.php?id=<?php echo $community_id; ?>&res_id=<?php echo $loan['id']; ?>" 
                                            onclick="return confirm('Afvis denne anmodning?')"
                                            class="bg-white/10 hover:bg-red-500/20 text-white/70 px-4 py-2 rounded-xl text-[10px] font-black transition italic border border-white/10">
                                                AFVIS
                                            </a>
                                        <?php else: ?>
                                            <span class="text-[10px] font-black text-emerald-400 italic bg-emerald-500/10 px-3 py-1 rounded-lg border border-emerald-500/20">
                                                AFTALEN ER I HUS
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </section>
                <?php endif; ?>

                <?php if ($my_assets->num_rows > 0): ?>
                <section class="space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-200 pb-2">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 italic">Dit grej</h3>
                        <span class="bg-indigo-100 text-indigo-600 text-[10px] px-2 py-0.5 rounded-full font-bold"><?php echo $my_assets->num_rows; ?></span>
                    </div>
                    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php while($item = $my_assets->fetch_assoc()): ?>
                            <div class="bg-white rounded-[2rem] border-2 border-indigo-100 overflow-hidden shadow-sm flex flex-col">
                                <div class="p-6 flex-1">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center font-bold">
                                            <i class="fas <?php echo ($item['category'] == 'Værktøj' ? 'fa-hammer' : ($item['category'] == 'Transport' ? 'fa-trailer' : 'fa-box-open')); ?>"></i>
                                        </div>
                                        <span class="text-[9px] font-black uppercase tracking-widest <?php echo ($item['is_busy'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'); ?> px-2 py-1 rounded-lg border border-current opacity-70">
                                            <?php echo ($item['is_busy'] > 0 ? 'Udlånt' : 'Ledig'); ?>
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-black text-slate-900 mb-1 italic"><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p class="text-slate-400 text-[11px] italic mb-4 uppercase font-bold tracking-tighter"><?php echo $item['category']; ?></p>
                                </div>
                                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-[10px] font-black text-indigo-400 uppercase italic">Ejet af dig</span>
                                    <a href="delete.php?id=<?php echo $community_id; ?>&asset_id=<?php echo $item['id']; ?>" 
                                       onclick="return confirm('Vil du fjerne denne fra fællesskabet?')"
                                       class="w-10 h-10 bg-white text-red-400 rounded-xl flex items-center justify-center hover:bg-red-500 hover:text-white transition shadow-sm border border-red-50">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </section>
                <?php endif; ?>

                <section class="space-y-6">
                    <div class="flex items-center gap-3 border-b border-slate-200 pb-2">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-400 italic">Fællesskabets grej</h3>
                    </div>
                    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
                        <?php if ($others_assets->num_rows > 0): ?>
                            <?php while($item = $others_assets->fetch_assoc()): ?>
                                <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
                                    <div class="p-6 flex-1">
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="w-10 h-10 bg-slate-50 text-slate-400 rounded-xl flex items-center justify-center">
                                                <i class="fas <?php echo ($item['category'] == 'Værktøj' ? 'fa-hammer' : ($item['category'] == 'Transport' ? 'fa-trailer' : 'fa-box-open')); ?>"></i>
                                            </div>
                                            <span class="text-[9px] font-black uppercase tracking-widest <?php echo ($item['is_busy'] > 0 ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'); ?> px-2 py-1 rounded-lg border border-current">
                                                <?php echo ($item['is_busy'] > 0 ? 'Udlånt' : 'Ledig'); ?>
                                            </span>
                                        </div>
                                        <h3 class="text-lg font-black text-slate-900 mb-1 italic"><?php echo htmlspecialchars($item['title']); ?></h3>
                                        <p class="text-slate-400 text-[11px] italic mb-4 uppercase font-bold tracking-tighter"><?php echo $item['category']; ?></p>
                                    </div>
                                    <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                                        <div class="flex flex-col">
                                            <span class="text-[8px] font-bold text-slate-300 uppercase">Ejes af</span>
                                            <span class="text-[11px] font-bold text-slate-600 italic"><?php echo htmlspecialchars($item['owner_name']); ?></span>
                                        </div>
                                        <?php if ($item['is_busy'] > 0): ?>
                                            <button disabled class="bg-slate-200 text-slate-400 px-5 py-2.5 rounded-xl text-xs font-black cursor-not-allowed italic">
                                                Optaget
                                            </button>
                                        <?php else: ?>
                                            <a href="book/?id=<?php echo $community_id; ?>&asset_id=<?php echo $item['id']; ?>" 
                                            class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-xs font-black hover:bg-indigo-600 transition shadow-sm italic">
                                                Book nu
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-span-full py-10 text-center italic text-slate-400 text-sm">
                                Ingen andre har delt noget endnu...
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

            </div>
        </div>
    </main>

</body>
</html>