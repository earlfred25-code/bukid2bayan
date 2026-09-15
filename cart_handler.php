<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}
if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = array();
}

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']) && !isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$redirect_to = 'cart.php';

if (isset($_POST['product_id']) && isset($_POST['action'])) {
    $product_id = (int)$_POST['product_id'];
    $action = $_POST['action'];
    $input_qty = max(1, (int)($_POST['quantity'] ?? 1));

    $stmt = $conn->prepare("SELECT name FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $product_name = $res->num_rows > 0 ? $res->fetch_assoc()['name'] : null;
    $stmt->close();

    if (!$product_name) {
        $_SESSION['flash']['error'] = "Product not found.";
        header('Location: products.php');
        exit();
    }

    if ($action == 'add') {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $input_qty;
        } else {
            $_SESSION['cart'][$product_id] = array('quantity' => $input_qty);
        }
        $_SESSION['flash']['success'] = $input_qty . " x " . htmlspecialchars($product_name) . " added to cart!";
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if (strpos($referer, 'index.php') !== false) {
            $redirect_to = 'index.php';
        } else {
            $redirect_to = 'products.php';
        }
    }

    if ($action == 'buy_now') {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $input_qty;
        } else {
            $_SESSION['cart'][$product_id] = array('quantity' => $input_qty);
        }
        $redirect_to = 'cart.php';
    }

    if ($action == 'update') {
        $quantity = (int)($_POST['quantity'] ?? 1);
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            }
        }
        $redirect_to = 'cart.php';
    }

    if ($action == 'remove') {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
        $redirect_to = 'cart.php';
    }
}

$conn->close();
header('Location: ' . $redirect_to);
exit();
?>