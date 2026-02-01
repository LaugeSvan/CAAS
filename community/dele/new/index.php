<?php
session_start();
include('../../../db_connect.php'); 

if (!isset($_GET['id'])) {
    header("Location: ../../../dashboard/");
    exit();
}

$community_id = $conn->real_escape_string($_GET['id']);
$user_id = $_SESSION['user_id'];

// Sikkerhedstjek: Er brugeren medlem?
$check_sql = "SELECT c.name, m.role, m.alias_name FROM communities c 
              JOIN community_members m ON c.id = m.community_id 
              WHERE c.id = '$community_id' AND m.user_id = '$user_id'";
$check_res = $conn->query($check_sql);

if ($check_res->num_rows == 0) { die("Ingen adgang."); }
$community = $check_res->fetch_assoc();

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $conn->real_escape_string($_POST['title']);
    $desc = $conn->real_escape_string($_POST['description']);
    $cat = $conn->real_escape_string($_POST['category']);

    $sql = "INSERT INTO assets (community_id, owner_id, title, description, category) 
            VALUES ('$community_id', '$user_id', '$title', '$desc', '$cat')";

    if ($conn->query($sql) === TRUE) {
        header("Location: ../?id=" . $community_id);
        exit();
    } else {
        $error = "Fejl: " . $conn->error;
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Del noget nyt - <?php echo htmlspecialchars($community['name']); ?></title>
    <style>
    html, body {
        max-width: 100%;
        overflow-x: hidden;
    }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen">

    <main class="max-w-7xl mx-auto px-6 py-10">
        <div class="grid grid-cols-12 gap-8">
            
            <aside class="col-span-12 lg:col-span-3 space-y-2">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-4 ml-2">Menu</p>
                <a href="../../?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl text-slate-600 hover:bg-white transition font-bold italic">
                    <i class="fas fa-bullhorn w-5 text-indigo-400"></i> Væggen
                </a>
                <a href="../?id=<?php echo $community_id; ?>" class="flex items-center gap-3 p-4 rounded-2xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 transition italic">
                    <i class="fas fa-tools w-5"></i> Deleøkonomi
                </a>
            </aside>

            <div class="col-span-12 lg:col-span-9">
                <div class="bg-white rounded-[2.5rem] border border-slate-200 shadow-sm p-10">
                    <div class="mb-8 text-left">
                        <h2 class="text-3xl font-black text-slate-900 italic tracking-tight">Del noget nyt 🔨</h2>
                        <p class="text-slate-500 italic">Gør dit community rigere ved at låne dine ting ud.</p>
                    </div>

                    <?php if($error): ?>
                        <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-sm italic font-bold border border-red-100"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="./?id=<?php echo $community_id; ?>" method="POST" class="space-y-6 max-w-2xl"> <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-1 italic">Navn på genstand</label>
                            <input type="text" name="title" required placeholder="Hvad vil du dele?" 
                                   class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium italic">
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-1 italic">Kategori</label>
                            <div class="relative">
                                <select name="category" class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium italic appearance-none">
                                    <option value="Værktøj">Værktøj</option>
                                    <option value="Have">Havemaskiner</option>
                                    <option value="Transport">Transport (Trailer/Bil)</option>
                                    <option value="Andet">Andet</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2 ml-1 italic">Beskrivelse</label>
                            <textarea name="description" rows="4" placeholder="Skriv lidt om tingen..." 
                                      class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-500 transition font-medium italic"></textarea>
                        </div>

                        <div class="pt-4 flex gap-4">
                            <button type="submit" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black text-lg hover:bg-indigo-600 transition shadow-lg shadow-slate-200 italic">
                                Bekræft og del
                            </button>
                            <a href="../?id=<?php echo $community_id; ?>" class="bg-slate-100 text-slate-400 px-8 py-4 rounded-2xl font-black text-center hover:bg-slate-200 transition italic">
                                Fortryd
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

</body>
</html>