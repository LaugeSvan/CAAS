<?php
session_start();
include('../db_connect.php');
ini_set('display_errors', 1); error_reporting(E_ALL); // Fejlsøgning

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Tjek om password matcher det hashede password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            
            // Send brugeren videre til dashboardet
            header("Location: ../dashboard");
            exit();
        } else {
            $error = "Forkert adgangskode.";
        }
    } else {
        $error = "Ingen bruger fundet med den e-mail.";
    }
}
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Log ind - CAAS</title>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-slate-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-indigo-900">CAAS</h1>
            <p class="text-slate-500 mt-2">Velkommen tilbage</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm border border-red-100">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="./" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">E-mail</label>
                <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-indigo-500 transition font-mono text-sm">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Adgangskode</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-slate-200 outline-none focus:border-indigo-500 transition">
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold text-lg hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                Log ind
            </button>
        </form>

        <p class="text-center text-slate-500 mt-8 text-sm">
            Mangler du en profil? <a href="../opret" class="text-indigo-600 font-bold hover:underline">Opret dig her</a>
        </p>
    </div>

</body>
</html>