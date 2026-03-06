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

// Community access: only members can view pages
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

$products = get_products_by_page($conn, $page_id);
$is_owner = $user_id > 0 && $user_id === (int) $page['profile_id'];
$order_success = isset($_GET['order']) ? "Din bestilling er registreret! Ordre #" . (int) $_GET['order'] : "";
$self_order_msg = isset($_GET['self_order']) ? "Du kan ikke bestille fra din egen shop." : "";

$cart_count = 0;
if (isset($_SESSION['shop_cart'][$page_id])) {
    foreach ($_SESSION['shop_cart'][$page_id] as $c) $cart_count += (int) ($c['quantity'] ?? 0);
}

$page_title = htmlspecialchars($page['name']);
include('../includes/header.php');
?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <title><?php echo htmlspecialchars($page['name']); ?> - CAAS</title>
</head>
<body class="bg-[#f8fafc] text-slate-900 antialiased font-sans">

    <main class="max-w-5xl mx-auto px-6 py-12">
        <?php if ($order_success): ?>
            <div class="mb-8 bg-emerald-50 border border-emerald-200 p-4 rounded-2xl text-emerald-600 font-bold italic text-sm">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $order_success; ?>
            </div>
        <?php endif; ?>
        <?php if ($self_order_msg): ?>
            <div class="mb-8 bg-amber-50 border border-amber-200 p-4 rounded-2xl text-amber-800 font-bold italic text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?php echo $self_order_msg; ?>
            </div>
        <?php endif; ?>
        <div class="mb-10">
            <?php if ($is_owner): ?>
                <a href="/user/pages/" class="text-indigo-600 hover:text-indigo-700 text-sm font-bold italic mb-2 inline-block mr-4">
                    <i class="fas fa-arrow-left mr-1"></i> Mine sider
                </a>
                <a href="/pages/manage.php?id=<?php echo $page_id; ?>" class="ml-4 text-slate-500 hover:text-indigo-600 text-sm font-bold italic mr-4">
                    <i class="fas fa-cog mr-1"></i> Administrer shop
                </a>
            <?php endif; ?>
            <?php if (!$is_owner && $cart_count > 0): ?>
                <a href="/pages/cart.php?id=<?php echo $page_id; ?>" class="text-indigo-600 hover:text-indigo-700 font-bold italic text-sm">
                    <i class="fas fa-shopping-cart mr-1"></i> Kurv (<?php echo $cart_count; ?>)
                </a>
            <?php endif; ?>
            <h1 class="text-4xl font-black text-slate-900 uppercase tracking-tighter italic mt-2"><?php echo htmlspecialchars($page['name']); ?></h1>
            <?php if (!empty($page['description'])): ?>
                <p class="text-slate-500 mt-2 font-bold italic"><?php echo htmlspecialchars($page['description']); ?></p>
            <?php endif; ?>
        </div>

        <?php if ($is_owner): ?>
            <div class="mb-8 bg-amber-50 border border-amber-200 p-4 rounded-2xl text-amber-800 font-bold italic text-sm">
                <i class="fas fa-info-circle mr-2"></i> Dette er din egen shop – du kan ikke bestille herfra. Brug "Administrer shop" for at håndtere produkter og ordrer.
            </div>
        <?php endif; ?>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($products && $products->num_rows > 0): ?>
                <?php while ($prod = $products->fetch_assoc()): ?>
                    <div class="bg-white p-6 rounded-[2rem] border border-slate-200 shadow-sm flex flex-col">
                        <h3 class="font-black text-xl italic"><?php echo htmlspecialchars($prod['name']); ?></h3>
                        <p class="text-indigo-600 text-xl font-black mt-2"><?php echo number_format((float) $prod['price'], 2, ',', '.'); ?> kr</p>
                        <?php if (!empty($prod['description'])): ?>
                            <p class="text-slate-500 text-sm mt-2 flex-1"><?php echo htmlspecialchars($prod['description']); ?></p>
                        <?php endif; ?>
                        <?php if (!$is_owner): ?>
                            <form action="/pages/cart.php?id=<?php echo $page_id; ?>" method="POST" class="mt-4">
                                <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($prod['name']); ?>">
                                <input type="hidden" name="price" value="<?php echo htmlspecialchars($prod['price']); ?>">
                                <div class="flex items-center gap-2">
                                    <input type="number" name="quantity" min="1" value="1" class="w-20 bg-slate-50 border border-slate-100 px-3 py-2 rounded-xl font-bold">
                                    <button type="submit" name="add_to_cart" class="flex-1 bg-slate-900 text-white py-3 px-4 rounded-xl font-black italic hover:bg-indigo-600 transition text-sm">
                                        <i class="fas fa-cart-plus mr-2"></i> Tilføj til kurv
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full bg-white p-12 rounded-[2rem] border-2 border-dashed border-slate-200 text-center">
                    <div class="w-20 h-20 bg-slate-100 rounded-2xl mx-auto mb-4 flex items-center justify-center text-3xl text-slate-300">
                        <i class="fas fa-store"></i>
                    </div>
                    <p class="text-slate-500 font-bold italic">Der er ingen produkter i shoppen endnu.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
