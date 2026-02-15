<?php
session_start();
include('../../db_connect.php');

if (!isset($_GET['id']) || !isset($_GET['event_id'])) {
    header("Location: ../../dashboard/");
    exit();
}

$community_id = $conn->real_escape_string($_GET['id']);
$event_id = (int)$_GET['event_id'];
$user_id = $_SESSION['user_id'];

$check = $conn->query("SELECT c.*, m.role, m.alias_name FROM communities c 
                      JOIN community_members m ON c.id = m.community_id 
                      WHERE c.id = '$community_id' AND m.user_id = '$user_id'");
if ($check->num_rows == 0) { header("Location: ../../dashboard/"); exit(); }
$community = $check->fetch_assoc();

$event_sql = "SELECT e.*, m.alias_name as creator_name 
              FROM events e
              JOIN community_members m ON e.created_by = m.user_id AND m.community_id = e.community_id
              WHERE e.id = '$event_id' AND e.community_id = '$community_id'";
$event_res = $conn->query($event_sql);
if ($event_res->num_rows == 0) { header("Location: index.php?id=" . $community_id); exit(); }
$event = $event_res->fetch_assoc();

$attendees_sql = "SELECT m.alias_name FROM event_attendees ea 
                 JOIN community_members m ON ea.user_id = m.user_id AND m.community_id = '$community_id'
                 WHERE ea.event_id = '$event_id' ORDER BY ea.created_at ASC";
$attendees = $conn->query($attendees_sql);
$attendee_count = $attendees->num_rows;

$is_going = $conn->query("SELECT 1 FROM event_attendees WHERE event_id = '$event_id' AND user_id = '$user_id'")->num_rows > 0;
$is_past = strtotime($event['event_at']) < time();
$is_full = $event['max_attendees'] !== null && $attendee_count >= (int)$event['max_attendees'];
$can_join = !$is_past && (!$is_full || $is_going);

$page_title = htmlspecialchars($event['title']);
include('../../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title><?php echo htmlspecialchars($event['title']); ?> - CAAS</title>
</head>
<body class="bg-[#f8fafc] min-h-screen antialiased">

    <main class="max-w-7xl mx-auto px-6 py-10">
        <?php if (isset($_GET['created'])): ?>
            <div class="mb-8 bg-emerald-50 border border-emerald-200 p-6 rounded-[2rem] flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-500 text-white rounded-2xl flex items-center justify-center text-xl"><i class="fas fa-check"></i></div>
                <div>
                    <h4 class="font-black text-emerald-900 italic">Event oprettet!</h4>
                    <p class="text-emerald-700 text-sm italic">Medlemmer kan nu tilmelde sig.</p>
                </div>
            </div>
        <?php endif; ?>

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
                <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-8 md:p-10">
                        <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                            <div>
                                <h1 class="text-3xl font-black text-slate-900 italic tracking-tight"><?php echo htmlspecialchars($event['title']); ?></h1>
                                <div class="flex flex-wrap items-center gap-4 mt-4 text-slate-500">
                                    <span class="flex items-center gap-2"><i class="fas fa-calendar-day text-indigo-500"></i> <?php echo date('l d. F Y \k\l. H:i', strtotime($event['event_at'])); ?></span>
                                    <?php if (!empty($event['location'])): ?>
                                        <span class="flex items-center gap-2"><i class="fas fa-location-dot text-indigo-500"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-2 italic">Oprettet af <?php echo htmlspecialchars($event['creator_name']); ?></p>
                            </div>
                            <?php if (!$is_past && $can_join): ?>
                                <form action="attend.php" method="POST" class="shrink-0">
                                    <input type="hidden" name="id" value="<?php echo $community_id; ?>">
                                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                    <?php if ($is_going): ?>
                                        <input type="hidden" name="action" value="leave">
                                        <button type="submit" class="bg-slate-100 text-slate-600 px-6 py-3 rounded-2xl font-bold hover:bg-red-50 hover:text-red-600 transition italic">Træk tilbage</button>
                                    <?php elseif (!$is_full): ?>
                                        <input type="hidden" name="action" value="join">
                                        <button type="submit" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition italic">Jeg kommer</button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty(trim($event['description']))): ?>
                            <div class="mt-8 pt-8 border-t border-slate-100">
                                <p class="text-slate-600 leading-relaxed italic whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="bg-slate-50 border-t border-slate-100 p-6 md:p-8">
                        <h3 class="font-black text-slate-900 italic mb-4">Tilmeldte (<?php echo $attendee_count; ?><?php echo $event['max_attendees'] ? ' / ' . (int)$event['max_attendees'] . ' max' : ''; ?>)</h3>
                        <?php if ($is_full && !$is_going): ?>
                            <p class="text-amber-600 text-sm font-bold italic">Eventet er fuldt booket.</p>
                        <?php endif; ?>
                        <?php if ($attendee_count > 0): ?>
                            <ul class="flex flex-wrap gap-3">
                                <?php $attendees->data_seek(0); while ($a = $attendees->fetch_assoc()): ?>
                                    <li class="bg-white px-4 py-2 rounded-xl border border-slate-100 font-bold text-slate-700 italic"><?php echo htmlspecialchars($a['alias_name']); ?></li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-slate-400 text-sm italic">Ingen tilmeldt endnu. Vær den første!</p>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="index.php?id=<?php echo $community_id; ?>" class="inline-flex items-center gap-2 text-indigo-600 font-bold italic hover:underline">
                    <i class="fas fa-arrow-left"></i> Tilbage til kalenderen
                </a>
            </div>
        </div>
    </main>

</body>
</html>
