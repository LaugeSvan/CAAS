<?php
session_start();
include('../db_connect.php');
include('../includes/pages_functions.php');
include('../includes/shop_functions.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: /login/");
    exit();
}

$page_id = (int) ($_GET['id'] ?? 0);
if ($page_id <= 0) {
    header("Location: /user/pages/");
    exit();
}

$profile_id = $_SESSION['user_id'];
$page = get_page_by_profile($conn, $page_id, $profile_id);
if (!$page) {
    header("Location: /user/pages/");
    exit();
}

$error = "";
$success = "";

// Add product
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $product_id = add_product_to_page($conn, $page_id, [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'price' => $_POST['price'] ?? 0,
            'stock' => $_POST['stock'] ?? null,
        ]);
        if ($product_id) {
            $success = "Produktet blev tilføjet!";
        } else {
            $error = "Kunne ikke tilføje produkt. Tjek navn og pris.";
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['product_id'])) {
        if (delete_product_from_page($conn, (int) $_POST['product_id'], $page_id)) {
            $success = "Produktet blev slettet.";
        }
    } elseif ($_POST['action'] === 'delete_order' && isset($_POST['order_id'])) {
        if (delete_order_from_page($conn, (int) $_POST['order_id'], $page_id)) {
            $success = "Ordren blev slettet.";
        }
    }
}

$products = get_products_by_page($conn, $page_id);
$page_title = "Shop: " . htmlspecialchars($page['name']);
include('../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>Shop - <?php echo htmlspecialchars($page['name']); ?> - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased font-sans">

    <main class="max-w-4xl mx-auto px-6 py-12">
        <div class="mb-10 flex items-center justify-between">
            <div>
                <a href="/user/pages/" class="text-indigo-600 hover:text-indigo-700 text-sm font-bold italic mb-2 inline-block">
                    <i class="fas fa-arrow-left mr-1"></i> Tilbage til mine sider
                </a>
                <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tighter italic"><?php echo htmlspecialchars($page['name']); ?></h1>
                <p class="text-slate-400 mt-1 font-bold italic text-xs uppercase tracking-widest">Administrer shop</p>
            </div>
            <a href="/pages/view.php?id=<?php echo $page_id; ?>" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-black italic hover:bg-indigo-600 transition">
                <i class="fas fa-store mr-2"></i> Se shop
            </a>
        </div>

        <?php if ($success): ?>
            <div class="mb-8 bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-emerald-600 font-bold italic text-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-8 bg-red-50 border border-red-200 p-4 rounded-2xl text-red-600 font-bold italic text-sm">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Add product form -->
        <div class="bg-white p-10 rounded-[3rem] border border-slate-200 shadow-sm mb-10">
            <h2 class="text-xl font-black italic mb-6 uppercase">Tilføj produkt</h2>
            <form action="?id=<?php echo $page_id; ?>" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="add">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">Navn</label>
                        <input type="text" name="name" required placeholder="f.eks. Margherita pizza"
                               class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 font-bold italic">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">Pris (kr)</label>
                        <input type="number" name="price" step="0.01" min="0" required placeholder="0.00"
                               class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 font-bold italic">
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">Beskrivelse</label>
                    <textarea name="description" rows="3" placeholder="Kort beskrivelse..."
                              class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 font-bold italic"></textarea>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">Lagerbeholdning (valgfrit)</label>
                    <input type="number" name="stock" min="0" placeholder="Tom = ubegrænset"
                           class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 font-bold italic">
                </div>
                <button type="submit" class="bg-slate-900 text-white px-8 py-4 rounded-2xl font-black italic hover:bg-indigo-600 transition">
                    <i class="fas fa-plus mr-2"></i> Tilføj produkt
                </button>
            </form>
        </div>

        <!-- Orders -->
        <?php
        $orders = get_orders_by_page($conn, $page_id);
        ?>
        <div class="bg-white p-10 rounded-[3rem] border border-slate-200 shadow-sm mb-10">
            <h2 class="text-xl font-black italic mb-6 uppercase"><i class="fas fa-receipt mr-2"></i> Ordrer</h2>
            <?php if ($orders && $orders->num_rows > 0): ?>
                <div class="space-y-6">
                    <?php while ($o = $orders->fetch_assoc()): ?>
                        <?php $items = get_order_items($conn, $o['id']); ?>
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-start justify-between gap-4">
                            <div class="flex-1">
                            <div class="flex items-center justify-between mb-3">
                                <span class="font-black italic">Ordre #<?php echo $o['id']; ?></span>
                                <span class="text-indigo-600 font-bold"><?php echo number_format((float) $o['total'], 2, ',', '.'); ?> kr</span>
                            </div>
                            <p class="text-sm text-slate-600"><strong><?php echo htmlspecialchars($o['customer_name']); ?></strong> · <?php echo htmlspecialchars($o['customer_email']); ?></p>
                            <?php if (!empty($o['customer_phone'])): ?>
                                <p class="text-sm text-slate-500"><?php echo htmlspecialchars($o['customer_phone']); ?></p>
                            <?php endif; ?>
                            <p class="text-xs text-slate-400 mt-1"><?php echo date('d.m.Y H:i', strtotime($o['created_at'])); ?> · <?php echo htmlspecialchars($o['status']); ?></p>
                            <ul class="mt-3 text-sm text-slate-600 space-y-1">
                                <?php if ($items): while ($it = $items->fetch_assoc()): ?>
                                    <li><?php echo (int) $it['quantity']; ?>× <?php echo htmlspecialchars($it['product_name'] ?? 'Produkt #' . $it['product_id']); ?> · <?php echo number_format((float) $it['price_at_purchase'], 2, ',', '.'); ?> kr</li>
                                <?php endwhile; endif; ?>
                            </ul>
                            </div>
                            <form action="?id=<?php echo $page_id; ?>" method="POST" onsubmit="return confirm('Slet denne ordre? Dette kan ikke fortrydes.');">
                                <input type="hidden" name="action" value="delete_order">
                                <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 p-2" title="Slet ordre">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-slate-500 font-bold italic">Ingen ordrer endnu.</p>
            <?php endif; ?>
        </div>

        <!-- Product list -->
        <div class="bg-white p-10 rounded-[3rem] border border-slate-200 shadow-sm">
            <h2 class="text-xl font-black italic mb-6 uppercase">Produkter</h2>
            <?php if ($products && $products->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while ($p = $products->fetch_assoc()): ?>
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div>
                                <h3 class="font-black text-lg italic"><?php echo htmlspecialchars($p['name']); ?></h3>
                                <p class="text-indigo-600 font-bold"><?php echo number_format((float) $p['price'], 2, ',', '.'); ?> kr</p>
                                <?php if (!empty($p['description'])): ?>
                                    <p class="text-slate-500 text-sm mt-1"><?php echo htmlspecialchars($p['description']); ?></p>
                                <?php endif; ?>
                            </div>
                            <form action="?id=<?php echo $page_id; ?>" method="POST" onsubmit="return confirm('Slet produktet?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 p-2" title="Slet">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-slate-500 font-bold italic">Ingen produkter endnu. Tilføj dit første produkt ovenfor.</p>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
