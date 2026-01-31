<?php
include('../db_connect.php');
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Tjek om e-mail findes i forvejen
    $checkEmail = $conn->query("SELECT id FROM users WHERE email = '$email'");
    
    if ($checkEmail->num_rows > 0) {
        $error = "Denne e-mail er allerede i brug.";
    } else {
        $sql = "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')";
        if ($conn->query($sql) === TRUE) {
            $success = "Din profil er oprettet! Du kan nu logge ind.";
        } else {
            $error = "Der skete en fejl: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Opret profil - CAAS</title>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">

    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-slate-100">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-indigo-900">CAAS</h1>
            <p class="text-slate-500 mt-2">Opret din profil og find dit fællesskab</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-6 text-sm border border-red-100 italic">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if($success): ?>
            <div class="bg-emerald-50 text-emerald-600 p-3 rounded-lg mb-6 text-sm border border-emerald-100 italic">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <form action="./" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Fulde navn</label>
                <input type="text" name="name" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">E-mail adresse</label>
                <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Adgangskode</label>
                <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition">
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-xl font-bold text-lg hover:bg-indigo-700 shadow-lg shadow-indigo-100 transition transform active:scale-[0.98]">
                Opret min profil
            </button>
        </form>

        <p class="text-center text-slate-500 mt-8 text-sm">
            Har du allerede en profil? <a href="../login/" class="text-indigo-600 font-bold hover:underline">Log ind her</a>
        </p>
    </div>

</body>
</html>