<?php
session_start();
include('../../../db_connect.php');

if (!isset($_GET['id']) || !isset($_GET['asset_id'])) {
    header("Location: ../../");
    exit();
}

$community_id = $conn->real_escape_string($_GET['id']);
$asset_id = $conn->real_escape_string($_GET['asset_id']);
$user_id = $_SESSION['user_id'];

// 1. Hent info om tingen og ejeren
$asset_sql = "SELECT a.*, u.name as owner_name, m.alias_name as owner_alias 
              FROM assets a 
              JOIN users u ON a.owner_id = u.id 
              JOIN community_members m ON a.owner_id = m.user_id AND m.community_id = a.community_id
              WHERE a.id = '$asset_id'";
$asset_res = $conn->query($asset_sql);
$item = $asset_res->fetch_assoc();

// 2. Håndter booking-anmodning
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];

    // VIKTIG RETTELSE: Tjek om datoerne er optaget af en BEKRÆFTET booking
    // Vi lader folk sende anmodninger, selvom der er andre 'pending', 
    // men ikke hvis der er en 'confirmed'.
    $check_sql = "SELECT * FROM reservations 
                  WHERE asset_id = '$asset_id' 
                  AND status = 'confirmed'
                  AND (('$start' BETWEEN start_date AND end_date) 
                       OR ('$end' BETWEEN start_date AND end_date))";
    $check_res = $conn->query($check_sql);

    if ($check_res->num_rows > 0) {
        $msg = "Øv! Tingen er allerede udlånt i den periode.";
    } else {
        // HER ER SKIFTET: Vi sætter status til 'pending' i stedet for 'confirmed'
        $sql = "INSERT INTO reservations (asset_id, user_id, start_date, end_date, status) 
                VALUES ('$asset_id', '$user_id', '$start', '$end', 'pending')";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: ../?id=$community_id&booked=pending");
            exit();
        } else {
            $msg = "Fejl ved oprettelse: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <title>Book <?php echo $item['title']; ?> - CAAS</title>
    <style>
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen antialiased">

    <nav class="bg-white border-b border-slate-200 px-6 py-4 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex justify-between items-center text-sm font-bold italic">
            <a href="../?id=<?php echo $community_id; ?>" class="text-slate-400 hover:text-indigo-600 transition flex items-center gap-2">
                <i class="fas fa-chevron-left text-[10px]"></i> Fortryd booking
            </a>
            <span class="text-slate-900 italic uppercase tracking-tighter">Booking-system</span>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-6 py-12">
        <div class="bg-white rounded-[3rem] border border-slate-200 shadow-sm overflow-hidden grid md:grid-cols-2">
            
            <div class="p-10 bg-slate-50 border-r border-slate-100">
                <div class="w-16 h-16 bg-white rounded-3xl flex items-center justify-center text-indigo-600 text-2xl shadow-sm mb-6">
                    <i class="fas <?php echo ($item['category'] == 'Værktøj' ? 'fa-hammer' : ($item['category'] == 'Transport' ? 'fa-trailer' : 'fa-box-open')); ?>"></i>
                </div>
                <h2 class="text-3xl font-black text-slate-900 italic leading-tight mb-2"><?php echo htmlspecialchars($item['title']); ?></h2>
                <p class="text-slate-500 italic mb-8"><?php echo htmlspecialchars($item['description']); ?></p>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <p class="text-sm font-bold text-slate-700">Ejes af <span class="text-indigo-600 italic"><?php echo htmlspecialchars($item['owner_alias']); ?></span></p>
                    </div>
                </div>
            </div>

            <div class="p-10">
                <h3 class="text-xl font-black text-slate-900 italic mb-6">Vælg dine datoer</h3>
                
                <?php if($msg): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-2xl mb-6 text-xs font-bold italic border border-red-100">
                        <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">Fra dato</label>
                        <input type="date" name="start_date" required min="<?php echo date('Y-m-d'); ?>"
                               class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 transition font-bold italic">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">Til dato</label>
                        <input type="date" name="end_date" required min="<?php echo date('Y-m-d'); ?>"
                               class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 transition font-bold italic">
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-lg hover:bg-indigo-600 transition shadow-lg shadow-slate-200 italic mt-4">
                        Bekræft booking
                    </button>
                    
                    <p class="text-center text-[10px] text-slate-400 font-bold uppercase italic tracking-tighter px-4">
                        Ved at booke accepterer du at aflevere tingen tilbage i samme stand.
                    </p>
                </form>
            </div>

        </div>
    </main>

</body>
</html>