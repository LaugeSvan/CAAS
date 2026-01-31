<?php
session_start();
// Vi skal to mapper op for at finde db_connect i roden
include('../../db_connect.php'); 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $desc = $conn->real_escape_string($_POST['description']);
    $user_id = $_SESSION['user_id'];
    $invite_code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));

    $sql = "INSERT INTO communities (name, description, owner_id, invite_code) 
            VALUES ('$name', '$desc', '$user_id', '$invite_code')";

    if ($conn->query($sql) === TRUE) {
        $community_id = $conn->insert_id;
        $user_name = $_SESSION['user_name'];

        $conn->query("INSERT INTO community_members (user_id, community_id, alias_name, role) 
                      VALUES ('$user_id', '$community_id', '$user_name', 'admin')");

        // Send tilbage til dashboardet (to mapper op)
        header("Location: ../../dashboard/");
        exit();
    } else {
        $error = "Der skete en fejl: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Start nyt community - CAAS</title>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col items-center justify-center p-6">

    <div class="max-w-xl w-full bg-white rounded-3xl shadow-xl shadow-slate-200/50 p-10 border border-slate-100">
        <div class="mb-8">
            <a href="../../dashboard/" class="text-indigo-600 text-sm font-bold hover:underline">&larr; Tilbage til dashboard</a>
            <h1 class="text-3xl font-black text-slate-900 mt-4">Start et nyt fællesskab</h1>
            <p class="text-slate-500">Skab et sted til din boligforening, sportsklub eller vennegruppe.</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 border border-red-100 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="./" method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Navn på fællesskabet</label>
                <input type="text" name="name" required placeholder="f.eks. Solbakken Ejerforening" 
                       class="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition">
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Kort beskrivelse</label>
                <textarea name="description" rows="4" placeholder="Hvad samles I om?" 
                          class="w-full px-5 py-4 rounded-2xl border border-slate-200 outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition"></textarea>
            </div>

            <div class="bg-indigo-50 p-5 rounded-2xl border border-indigo-100 mb-6">
                <p class="text-xs text-indigo-700 leading-relaxed">
                    <strong>Tip:</strong> Når du har oprettet fællesskabet, får du en unik invitationskode, som du kan sende til dem, der skal være med.
                </p>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold text-lg hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition active:scale-[0.98]">
                Opret fællesskab
            </button>
        </form>
    </div>

</body>
</html>