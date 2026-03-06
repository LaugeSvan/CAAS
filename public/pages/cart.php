<?php
session_start();
include('../db_connect.php');
include('../includes/pages_functions.php');
include('../includes/shop_functions.php');

$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

$page_id = (int) ($_GET['id'] ?? 0);
if ($page_id <= 0) {
    header("Location: /");
    exit();
}

$page = get_page_by_id($conn, $page_id);
if (!$page) {
    header("Location: /");
    exit();
}

// Community access: only members can shop
$community_id = (int) ($page['community_id'] ?? 0);
if ($community_id > 0) {
    if ($user_id <= 0) {
        header("Location: /login/");
        exit();
    }
    $member = $conn->query("SELECT 1 FROM community_members WHERE community_id = '$community_id' AND user_id = '$user_id' LIMIT 1");
    if (!$member || $member->num_rows === 0) {
        die("Ingen adgang.");
    }
}

// Block self-ordering: owners cannot order from their own shop
$is_owner = $user_id > 0 && $user_id === (int) $page['profile_id'];
if ($is_owner) {
    if (!isset($_SESSION['shop_cart'])) $_SESSION['shop_cart'] = [];
    $_SESSION['shop_cart'][$page_id] = [];
    header("Location: /pages/view.php?id=$page_id&self_order=1");
    exit();
}

// Init cart for this page
if (!isset($_SESSION['shop_cart'])) {
    $_SESSION['shop_cart'] = [];
}
if (!isset($_SESSION['shop_cart'][$page_id])) {
    $_SESSION['shop_cart'][$page_id] = [];
}

// Add to cart (owner check already done above)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_to_cart'])) {
    $pid = (int) ($_POST['product_id'] ?? 0);
    $name = trim($_POST['product_name'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $qty = max(1, (int) ($_POST['quantity'] ?? 1));
    if ($pid > 0 && $name !== '' && $price >= 0) {
        $key = (string) $pid;
        if (isset($_SESSION['shop_cart'][$page_id][$key])) {
            $_SESSION['shop_cart'][$page_id][$key]['quantity'] += $qty;
        } else {
            $_SESSION['shop_cart'][$page_id][$key] = [
                'product_id' => $pid,
                'name' => $name,
                'price' => $price,
                'quantity' => $qty,
            ];
        }
        header("Location: /pages/cart.php?id=$page_id&added=1");
        exit();
    }
}

// Remove from cart
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['remove'])) {
    $key = (string) ($_POST['product_id'] ?? '');
    unset($_SESSION['shop_cart'][$page_id][$key]);
    header("Location: /pages/cart.php?id=$page_id");
    exit();
}

// Checkout
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['checkout'])) {
    $items = array_values($_SESSION['shop_cart'][$page_id]);
    if (!empty($items)) {
        $order_items = [];
        foreach ($items as $item) {
            $order_items[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ];
        }
        $order_id = create_order($conn, $page_id, $order_items, [
            'name' => trim($_POST['customer_name'] ?? ''),
            'email' => trim($_POST['customer_email'] ?? ''),
            'phone' => trim($_POST['customer_phone'] ?? ''),
        ], $user_id > 0 ? $user_id : null);
        if ($order_id) {
            $_SESSION['shop_cart'][$page_id] = [];
            header("Location: /pages/view.php?id=$page_id&order=$order_id");
            exit();
        }
    }
}

$cart = $_SESSION['shop_cart'][$page_id];
$added_msg = isset($_GET['added']) ? "Tilføjet til kurven!" : "";

$page_title = "Kurv - " . htmlspecialchars($page['name']);
include('../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title>Kurv - <?php echo htmlspecialchars($page['name']); ?> - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased font-sans">

    <main class="max-w-3xl mx-auto px-6 py-12">
        <div class="mb-8">
            <a href="/pages/view.php?id=<?php echo $page_id; ?>" class="text-indigo-600 hover:text-indigo-700 text-sm font-bold italic">
                <i class="fas fa-arrow-left mr-1"></i> Tilbage til <?php echo htmlspecialchars($page['name']); ?>
            </a>
        </div>

        <?php if ($added_msg): ?>
            <div class="mb-8 bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-emerald-600 font-bold italic text-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $added_msg; ?>
            </div>
        <?php endif; ?>

        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tighter italic mb-10">Kurv</h1>

        <?php if (!empty($cart)): ?>
            <div class="space-y-4 mb-10">
                <?php
                $total = 0;
                foreach ($cart as $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                ?>
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex items-center justify-between">
                        <div>
                            <h3 class="font-black text-lg italic"><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p class="text-slate-500 text-sm"><?php echo $item['quantity']; ?> stk × <?php echo number_format((float) $item['price'], 2, ',', '.'); ?> kr</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="font-black text-indigo-600"><?php echo number_format($subtotal, 2, ',', '.'); ?> kr</span>
                            <form action="?id=<?php echo $page_id; ?>" method="POST">
                                <input type="hidden" name="remove" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white p-10 rounded-[3rem] border border-slate-200 shadow-sm">
                <p class="text-2xl font-black text-slate-900 mb-6">I alt: <?php echo number_format($total, 2, ',', '.'); ?> kr</p>
                <form action="?id=<?php echo $page_id; ?>" method="POST" class="space-y-4">
                    <input type="hidden" name="checkout" value="1">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">Navn *</label>
                        <input type="text" name="customer_name" required
                               value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>"
                               class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 font-bold italic">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">E-mail *</label>
                        <?php
                        $user_email = '';
                        if ($user_id > 0) {
                            $uid = (int) $user_id;
                            $u = $conn->query("SELECT email FROM users WHERE id = '$uid'");
                            if ($u && $row = $u->fetch_assoc()) $user_email = $row['email'];
                        }
                        ?>
                        <input type="email" name="customer_email" required value="<?php echo htmlspecialchars($user_email); ?>"
                               class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 font-bold italic">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2 italic">Telefon</label>
                        <input type="tel" name="customer_phone"
                               class="w-full bg-slate-50 border border-slate-100 px-6 py-4 rounded-2xl outline-none focus:ring-2 focus:ring-indigo-600 font-bold italic">
                    </div>
                    <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-lg hover:bg-indigo-600 transition shadow-lg italic">
                        Bestil
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="bg-white p-12 rounded-[2rem] border-2 border-dashed border-slate-200 text-center">
                <div class="w-20 h-20 bg-slate-100 rounded-2xl mx-auto mb-4 flex items-center justify-center text-3xl text-slate-300">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <p class="text-slate-500 font-bold italic">Din kurv er tom.</p>
                <a href="/pages/view.php?id=<?php echo $page_id; ?>" class="inline-block mt-6 bg-slate-900 text-white px-6 py-3 rounded-2xl font-black italic hover:bg-indigo-600 transition">
                    Gå til shop
                </a>
            </div>
        <?php endif; ?>
    </main>

</body>
</html>
