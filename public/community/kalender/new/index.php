<?php
session_start();
include('../../../db_connect.php');

if (!isset($_GET['id'])) {
    header("Location: ../../../dashboard/");
    exit();
}

$community_id = $conn->real_escape_string($_GET['id']);
$user_id = $_SESSION['user_id'];

$check = $conn->query("SELECT c.name, m.role FROM communities c 
                      JOIN community_members m ON c.id = m.community_id 
                      WHERE c.id = '$community_id' AND m.user_id = '$user_id'");
if ($check->num_rows == 0) { header("Location: ../../../dashboard/"); exit(); }
$community = $check->fetch_assoc();

$error = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $description = $conn->real_escape_string(trim($_POST['description'] ?? ''));
    $event_at = $conn->real_escape_string($_POST['event_at'] ?? '');
    $location = $conn->real_escape_string(trim($_POST['location'] ?? ''));
    $max_attendees = isset($_POST['max_attendees']) && $_POST['max_attendees'] !== '' ? (int)$_POST['max_attendees'] : null;

    if ($title === '') {
        $error = 'Give eventet et navn.';
    } elseif ($event_at === '') {
        $error = 'Vælg dato og tid.';
    } else {
        $title_esc = $conn->real_escape_string($title);
        $max_sql = $max_attendees !== null ? "'$max_attendees'" : 'NULL';
        $loc_sql = $location !== '' ? "'$location'" : 'NULL';
        $sql = "INSERT INTO events (community_id, created_by, title, description, event_at, location, max_attendees) 
                VALUES ('$community_id', '$user_id', '$title_esc', '$description', '$event_at', $loc_sql, $max_sql)";
        if ($conn->query($sql)) {
            $event_id = $conn->insert_id;
            header("Location: ../view.php?id=" . $community_id . "&event_id=" . $event_id . "&created=1");
            exit();
        }
        $error = 'Kunne ikke oprette event. Prøv igen.';
    }
}

$page_title = htmlspecialchars($community['name']);
include('../../../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>Opret event - CAAS</title>
</head>
<body class="bg-[#f8fafc] min-h-screen antialiased">

    <main class="max-w-7xl mx-auto px-6 py-10">
        <div class="grid grid-cols-12 gap-8">
            <aside class="col-span-12 lg:col-span-3 space-y-2">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 ml-2">Menu</p>
                <a href="../../?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white transition font-bold italic">
                    <i class="fas fa-bullhorn w-5 text-indigo-400"></i> Væggen
                </a>
                <a href="../../dele/?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white transition font-bold italic">
                    <i class="fas fa-tools w-5 text-indigo-500"></i> Deleøkonomi
                </a>
                <a href="../?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 transition italic">
                    <i class="fas fa-calendar-days w-5"></i> Kalender
                </a>
            </aside>

            <div class="col-span-12 lg:col-span-9">
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm p-10">
                    <div class="mb-8">
                        <h2 class="text-3xl font-black text-slate-900 italic tracking-tight">Opret event 📅</h2>
                        <p class="text-slate-500 italic mt-1">Inviter fællesskabet til en begivenhed.</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-sm italic font-bold border border-red-100"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form action="./?id=<?php echo $community_id; ?>" method="POST" class="space-y-6 max-w-2xl">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-1 italic">Titel</label>
                            <input type="text" name="title" required placeholder="F.eks. Fælles grillaften" 
                                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium italic">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-1 italic">Dato og tid</label>
                            <input type="datetime-local" name="event_at" required 
                                   value="<?php echo htmlspecialchars($_POST['event_at'] ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium italic">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-1 italic">Sted (valgfrit)</label>
                            <input type="text" name="location" placeholder="F.eks. Fælleshuset" 
                                   value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium italic">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-1 italic">Max antal deltagere (valgfrit)</label>
                            <input type="number" name="max_attendees" min="1" placeholder="Ubegrænset" 
                                   value="<?php echo htmlspecialchars($_POST['max_attendees'] ?? ''); ?>"
                                   class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium italic">
                        </div>
                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-1 italic">Beskrivelse</label>
                            <textarea name="description" rows="4" placeholder="Hvad sker der?" 
                                      class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium italic"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="pt-4 flex gap-4">
                            <button type="submit" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-lg hover:bg-indigo-600 transition shadow-lg shadow-slate-200 italic">Opret event</button>
                            <a href="../?id=<?php echo $community_id; ?>" class="bg-slate-100 text-slate-400 px-8 py-4 rounded-2xl font-black text-center hover:bg-slate-200 transition italic">Annuller</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
