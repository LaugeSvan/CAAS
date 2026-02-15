<?php
session_start();
include('../../db_connect.php');

if (!isset($_GET['id'])) {
    header("Location: ../../dashboard/");
    exit();
}

$community_id = $conn->real_escape_string($_GET['id']);
$user_id = $_SESSION['user_id'];

$check = $conn->query("SELECT c.*, m.role, m.alias_name FROM communities c 
                      JOIN community_members m ON c.id = m.community_id 
                      WHERE c.id = '$community_id' AND m.user_id = '$user_id'");
if ($check->num_rows == 0) { header("Location: ../../dashboard/"); exit(); }
$community = $check->fetch_assoc();

// Kommende events først, derefter tidligere
$events_sql = "SELECT e.*, m.alias_name as creator_name,
               (SELECT COUNT(*) FROM event_attendees ea WHERE ea.event_id = e.id) as attendee_count
               FROM events e
               JOIN community_members m ON e.created_by = m.user_id AND m.community_id = e.community_id
               WHERE e.community_id = '$community_id'
               ORDER BY e.event_at ASC";
$events = $conn->query($events_sql);

// Tjek om brugeren allerede er tilmeldt hvert event (til "Jeg kommer" knap)
$my_attendances = [];
$ar = $conn->query("SELECT event_id FROM event_attendees WHERE user_id = '$user_id'");
while ($row = $ar->fetch_assoc()) $my_attendances[$row['event_id']] = true;

$page_title = htmlspecialchars($community['name']);
include('../../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Kalender - <?php echo htmlspecialchars($community['name']); ?> - CAAS</title>
</head>
<body class="bg-[#f8fafc] min-h-screen antialiased">

    <main class="max-w-7xl mx-auto px-6 py-10">
        <div class="grid lg:grid-cols-4 gap-8">
            <aside class="lg:col-span-1 space-y-2">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 ml-2">Menu</p>
                <a href="../?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white transition font-bold italic">
                    <i class="fas fa-bullhorn w-5 text-indigo-400"></i> Væggen
                </a>
                <a href="../dele/?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white transition font-bold italic">
                    <i class="fas fa-tools w-5 text-indigo-500"></i> Deleøkonomi
                </a>
                <a href="./?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 transition italic">
                    <i class="fas fa-calendar-days w-5"></i> Kalender
                </a>
            </aside>

            <div class="lg:col-span-3 space-y-8">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <h2 class="text-3xl font-black text-slate-900 italic tracking-tight">Kalender & events 📅</h2>
                        <p class="text-slate-500 italic text-sm">Se kommende begivenheder og tilmeld dig.</p>
                    </div>
                    <a href="new/?id=<?php echo $community_id; ?>" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition flex items-center gap-2 italic">
                        <i class="fas fa-plus text-xs"></i> Opret event
                    </a>
                </div>

                <div class="space-y-4">
                    <?php if ($events->num_rows > 0): ?>
                        <?php while($ev = $events->fetch_assoc()): 
                            $is_past = strtotime($ev['event_at']) < time();
                            $is_going = isset($my_attendances[$ev['id']]);
                        ?>
                        <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4 p-6 <?php echo $is_past ? 'opacity-75' : ''; ?>">
                            <a href="view.php?id=<?php echo $community_id; ?>&event_id=<?php echo $ev['id']; ?>" class="flex-1 group">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl <?php echo $is_past ? 'bg-slate-100 text-slate-400' : 'bg-indigo-50 text-indigo-600'; ?>">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-black text-slate-900 italic group-hover:text-indigo-600 transition"><?php echo htmlspecialchars($ev['title']); ?></h3>
                                        <p class="text-slate-500 text-sm italic">
                                            <?php echo date('l d. M Y, H:i', strtotime($ev['event_at'])); ?>
                                            <?php if (!empty($ev['location'])) echo ' · ' . htmlspecialchars($ev['location']); ?>
                                        </p>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase mt-1"><?php echo htmlspecialchars($ev['creator_name']); ?> · <?php echo (int)$ev['attendee_count']; ?> tilmeldt</p>
                                    </div>
                                </div>
                            </a>
                            <div class="flex items-center gap-3 shrink-0">
                                <?php if (!$is_past): ?>
                                    <?php if ($is_going): ?>
                                        <span class="bg-emerald-100 text-emerald-700 px-4 py-2 rounded-xl text-xs font-black italic uppercase">Du deltager</span>
                                    <?php endif; ?>
                                    <a href="view.php?id=<?php echo $community_id; ?>&event_id=<?php echo $ev['id']; ?>" class="bg-slate-900 text-white px-5 py-2.5 rounded-xl text-xs font-black hover:bg-indigo-600 transition italic">Se event</a>
                                <?php else: ?>
                                    <span class="text-[10px] font-black uppercase text-slate-400 italic">Afholdt</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="bg-white rounded-[2rem] border-2 border-dashed border-slate-200 p-16 text-center">
                            <p class="text-slate-400 font-black italic uppercase tracking-tighter">Ingen events endnu</p>
                            <p class="text-slate-400 text-sm italic mt-2">Opret det første event og inviter fællesskabet.</p>
                            <a href="new/?id=<?php echo $community_id; ?>" class="inline-block mt-6 bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold hover:bg-indigo-700 transition italic">Opret event</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
