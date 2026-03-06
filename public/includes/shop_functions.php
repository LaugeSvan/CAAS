<?php
/**
 * Shop functions: products and orders belong to pages.
 */

/**
 * Add a product to a page's shop.
 *
 * @param mysqli $conn
 * @param int $page_id Page ID
 * @param array $data name, description, price, stock (optional)
 * @return int|false Product ID or false
 */
function add_product_to_page($conn, $page_id, $data) {
    $page_id = (int) $page_id;
    if ($page_id <= 0) return false;

    $name = trim($data['name'] ?? '');
    if ($name === '') return false;

    $price = isset($data['price']) ? (float) $data['price'] : 0;
    $description = trim($data['description'] ?? '');
    $stock = isset($data['stock']) && $data['stock'] !== '' ? (int) $data['stock'] : null;

    $name_esc = $conn->real_escape_string($name);
    $desc_esc = $conn->real_escape_string($description);
    $price_esc = number_format($price, 2, '.', '');
    $stock_sql = $stock === null ? 'NULL' : "'$stock'";

    $sql = "INSERT INTO products (page_id, name, description, price, stock) 
            VALUES ('$page_id', '$name_esc', '$desc_esc', '$price_esc', $stock_sql)";

    if ($conn->query($sql) === true) {
        return (int) $conn->insert_id;
    }
    return false;
}

/**
 * Get all products for a page.
 */
function get_products_by_page($conn, $page_id) {
    $page_id = (int) $page_id;
    if ($page_id <= 0) return false;
    return $conn->query("SELECT * FROM products WHERE page_id = '$page_id' ORDER BY name ASC");
}

/**
 * Get a single product by ID, only if it belongs to the page.
 */
function get_product_by_page($conn, $product_id, $page_id) {
    $product_id = (int) $product_id;
    $page_id = (int) $page_id;
    if ($product_id <= 0 || $page_id <= 0) return null;
    $res = $conn->query("SELECT * FROM products WHERE id = '$product_id' AND page_id = '$page_id'");
    return $res && $res->num_rows > 0 ? $res->fetch_assoc() : null;
}

/**
 * Delete a product (only if it belongs to the page).
 */
function delete_product_from_page($conn, $product_id, $page_id) {
    $product_id = (int) $product_id;
    $page_id = (int) $page_id;
    if ($product_id <= 0 || $page_id <= 0) return false;
    return $conn->query("DELETE FROM products WHERE id = '$product_id' AND page_id = '$page_id'") === true;
}

/**
 * Create an order from cart items.
 * @param mysqli $conn
 * @param int $page_id
 * @param array $items [['product_id' => x, 'quantity' => n, 'price' => p], ...]
 * @param array $customer name, email, phone (optional)
 * @param int|null $user_id Logged-in user or null for guest
 * @return int|false Order ID or false
 */
function create_order($conn, $page_id, $items, $customer, $user_id = null) {
    $page_id = (int) $page_id;
    if ($page_id <= 0 || empty($items)) return false;

    $name = trim($customer['name'] ?? '');
    $email = trim($customer['email'] ?? '');
    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

    $user_sql = $user_id ? "'" . (int) $user_id . "'" : 'NULL';
    $phone = trim($customer['phone'] ?? '');
    $name_esc = $conn->real_escape_string($name);
    $email_esc = $conn->real_escape_string($email);
    $phone_esc = $conn->real_escape_string($phone);

    $total = 0;
    $valid_items = [];
    foreach ($items as $item) {
        $pid = (int) ($item['product_id'] ?? 0);
        $qty = max(1, (int) ($item['quantity'] ?? 1));
        $price = (float) ($item['price'] ?? 0);
        if ($pid > 0 && $price > 0) {
            $valid_items[] = ['product_id' => $pid, 'quantity' => $qty, 'price' => $price];
            $total += $price * $qty;
        }
    }
    if (empty($valid_items)) return false;

    $total_esc = number_format($total, 2, '.', '');
    $sql = "INSERT INTO orders (page_id, user_id, customer_name, customer_email, customer_phone, total, status) 
            VALUES ('$page_id', $user_sql, '$name_esc', '$email_esc', '$phone_esc', '$total_esc', 'pending')";
    if ($conn->query($sql) !== true) return false;

    $order_id = (int) $conn->insert_id;
    foreach ($valid_items as $item) {
        $price_esc = number_format($item['price'], 2, '.', '');
        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) 
                      VALUES ('$order_id', '{$item['product_id']}', '{$item['quantity']}', '$price_esc')");
    }
    return $order_id;
}

/**
 * Delete an order (only if it belongs to the page). Order items are cascaded.
 */
function delete_order_from_page($conn, $order_id, $page_id) {
    $order_id = (int) $order_id;
    $page_id = (int) $page_id;
    if ($order_id <= 0 || $page_id <= 0) return false;
    return $conn->query("DELETE FROM orders WHERE id = '$order_id' AND page_id = '$page_id'") === true;
}

/**
 * Get all orders for a page.
 */
function get_orders_by_page($conn, $page_id) {
    $page_id = (int) $page_id;
    if ($page_id <= 0) return false;
    return $conn->query("SELECT * FROM orders WHERE page_id = '$page_id' ORDER BY created_at DESC");
}

/**
 * Get order items for an order.
 */
function get_order_items($conn, $order_id) {
    $order_id = (int) $order_id;
    if ($order_id <= 0) return false;
    return $conn->query("SELECT oi.*, p.name as product_name 
                         FROM order_items oi 
                         LEFT JOIN products p ON oi.product_id = p.id 
                         WHERE oi.order_id = '$order_id'");
}
