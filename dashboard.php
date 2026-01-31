<?php
session_start();

// Hvis brugeren IKKE er logget ind, send dem tilbage til login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Dashboard - CAAS</title>
</head>
<body class="bg-slate-100 p-10">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
            <h1 class="text-2xl font-bold">Hej, <?php echo $_SESSION['user_name']; ?>! 👋</h1>
            <p class="text-slate-600 mt-2">Du er nu logget ind på CAAS.</p>
            
            <div class="mt-10 p-6 border-2 border-dashed border-slate-200 rounded-xl text-center">
                <p class="text-slate-400">Her kommer dine fællesskaber til at ligge...</p>
                <button class="mt-4 bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold">Opret dit første fællesskab</button>
            </div>

            <a href="logout.php" class="inline-block mt-8 text-red-500 hover:underline">Log ud</a>
        </div>
    </div>
</body>
</html>