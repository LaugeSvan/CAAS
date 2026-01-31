<?php
session_start();
include('../db_connect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /login/");
    exit();
}

$user_id = $_SESSION['user_id'];
$search = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : "";

// Hent communities der matcher søgning (og som man ikke allerede er medlem af)
$sql = "SELECT * FROM communities 
        WHERE (name LIKE '%$search%' OR description LIKE '%$search%')
        AND id NOT IN (SELECT community_id FROM community_members WHERE user_id = '$user_id')
        LIMIT 10";

$communities = $conn->query($sql);

// Håndtering af Join via Invite Code
$msg = "";
if (isset($_POST['join_code'])) {
    $code = $conn->real_escape_string($_POST['join_code']);
    $res = $conn->query("SELECT id FROM communities WHERE invite_code = '$code'");
    
    if ($res->num_rows > 0) {
        $c = $res->fetch_assoc();
        $c_id = $c['id'];
        $alias = $_SESSION['user_name'];
        
        // Tjek om de allerede er medlem (just in case)
        $check = $conn->query("SELECT id FROM community_members WHERE user_id = '$user_id' AND community_id = '$c_id'");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO community_members (user_id, community_id, alias_name) VALUES ('$user_id', '$c_id', '$alias')");
            header("Location: /dashboard/?joined=success");
            exit();
        }
    } else {
        $msg = "Ugyldig kode. Prøv igen.";
    }
}
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Udforsk fællesskaber - CAAS</title>
</head>
<body class="bg-slate-50 min-h-screen">

    <nav class="bg-white border-b p-4 mb-8">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <a href="/dashboard/" class="text-xl font-black text-indigo-600">CAAS</a>
            <a href="/dashboard/" class="text-sm font-bold text-slate-600 hover:text-indigo-600">&larr; Tilbage til Dashboard</a>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-6">
        
        <div class="bg-indigo-900 text-white p-8 rounded-2xl shadow-lg mb-12">
            <h2 class="text-xl font-bold mb-2">Har du en invitation?</h2>
            <p class="text-indigo-200 mb-6">Indtast koden fra din forening eller klub for at blive medlem med det samme.</p>
            <form action="./" method="POST" class="flex gap-3">
                <input type="text" name="join_code" placeholder="F.eks. a1b2c3d4" required 
                       class="flex-1 bg-white/10 border border-white/20 rounded-xl px-4 py-3 outline-none focus:bg-white/20 transition placeholder:text-indigo-300">
                <button type="submit" class="bg-white text-indigo-900 px-8 py-3 rounded-xl font-bold hover:bg-indigo-50 transition">
                    Join
                </button>
            </form>
            <?php if($msg): ?> <p class="mt-3 text-red-300 text-sm font-bold"><?php echo $msg; ?></p> <?php endif; ?>
        </div>

        <div class="mb-10 text-center">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Opdag nye fællesskaber</h2>
            <form action="./" method="GET" class="max-w-md mx-auto relative">
                <input type="text" name="q" value="<?php echo $search; ?>" placeholder="Søg på navn eller emne..." 
                       class="w-full pl-12 pr-4 py-4 rounded-2xl border-none shadow-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                <span class="absolute left-4 top-4 text-slate-400">🔍</span>
            </form>
        </div>

        <div class="grid gap-4">
            <?php if ($communities->num_rows > 0): ?>
                <?php while($row = $communities->fetch_assoc()): ?>
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 flex justify-between items-center hover:border-indigo-300 transition group">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900 group-hover:text-indigo-600 transition"><?php echo $row['name']; ?></h3>
                            <p class="text-slate-500 text-sm"><?php echo $row['description']; ?></p>
                        </div>
                        <form action="./" method="POST">
                            <input type="hidden" name="join_code" value="<?php echo $row['invite_code']; ?>">
                            <button type="submit" class="bg-slate-100 text-slate-700 px-5 py-2 rounded-lg font-bold hover:bg-indigo-600 hover:text-white transition">
                                Ansøg
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-slate-400 py-10 italic">Ingen fællesskaber fundet...</p>
            <?php endif; ?>
        </div>

    </main>

</body>
</html>