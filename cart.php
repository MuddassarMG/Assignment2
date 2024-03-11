<?php
include 'db_connection.php';

header('Content-Type: application/json');

//GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        try {
            $statement = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
            $statement->execute([$user_id]);
            $cart_items = $statement->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($cart_items);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array('error' => 'Internal Server Error'));
        }
    } else {
        try {
            $statement = $pdo->prepare("SELECT * FROM cart");
            $statement->execute();
            $cart_items = $statement->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($cart_items);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array('error' => 'Internal Server Error'));
        }
    }
}


//POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['user_id']) || !isset($data['product_id']) || !isset($data['quantity'])) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing required fields'));
        exit;
    }

    $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
    $product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);
    $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);

    if (!$user_id || !$product_id || !$quantity) {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid input'));
        exit;
    }

    try {
        $statement = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $statement->execute([$user_id, $product_id]);
        $existing_item = $statement->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            $new_quantity = $existing_item['quantity'] + $quantity;
            $statement = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $statement->execute([$new_quantity, $user_id, $product_id]);
        } else {
            $statement = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $statement->execute([$user_id, $product_id, $quantity]);
        }

        echo json_encode(array('message' => 'Item added to cart successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

//PUT
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['user_id']) || !isset($data['quantity']) || !isset($data['product_id'])) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing required fields'));
        exit;
    }

    $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
    $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
    $product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);

    if (!$user_id || !$quantity || !$product_id) {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid input'));
        exit;
    }

    try {
        $statement = $pdo->prepare("UPDATE cart SET user_id = ?, quantity = ?, product_id = ? WHERE id = ?");
        $statement->execute([$user_id, $quantity, $product_id, $id]);
        echo json_encode(array('message' => 'Cart item updated successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}


//DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $id = $_GET['id'];

    try {
        $statement = $pdo->prepare("DELETE FROM cart WHERE id = ?");
        $statement->execute([$id]);
        echo json_encode(array('message' => 'Cart item deleted successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}
?>
