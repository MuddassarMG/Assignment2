<?php
include 'db_connection.php';

header('Content-Type: application/json');

//GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        try {
            $statement = $pdo->prepare("SELECT * FROM orders WHERE user_id = ?");
            $statement->execute([$user_id]);
            $orders = $statement->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($orders);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array('error' => 'Internal Server Error'));
            exit;
        }
    } else {
        try {
            $statement = $pdo->prepare("SELECT * FROM orders");
            $statement->execute();
            $orders = $statement->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($orders);
            exit;
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array('error' => 'Internal Server Error'));
            exit;
        }
    }
}

//Post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['user_id']) || !isset($data['total_amount']) || !isset($data['status'])) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing required fields'));
        exit;
    }

    $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
    $total_amount = filter_var($data['total_amount'], FILTER_VALIDATE_FLOAT);
    $status = filter_var($data['status'], FILTER_SANITIZE_STRING);

    if (!$user_id || !$total_amount || !$status) {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid input'));
        exit;
    }

    try {
        $statement = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, ?)");
        $statement->execute([$user_id, $total_amount, $status]);
        echo json_encode(array('message' => 'Order created successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

//PUT
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    if (isset($_GET['id'])) {
        $order_id = $_GET['id'];
        
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['total_amount']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(array('error' => 'Missing required fields'));
            exit;
        }
        
        $total_amount = filter_var($data['total_amount'], FILTER_SANITIZE_STRING);
        $status = filter_var($data['status'], FILTER_SANITIZE_STRING);

        try {
            $statement = $pdo->prepare("UPDATE orders SET total_amount = ?, status = ? WHERE id = ?");
            $statement->execute([$total_amount, $status, $order_id]);

            if ($statement->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(array('message' => 'Order updated successfully'));
            } else {
                http_response_code(404);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array('error' => 'Internal Server Error'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Order ID not provided'));
    }
}

//DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['id'])) {
        $order_id = $_GET['id'];
        
        try {
            $statement = $pdo->prepare("DELETE FROM orders WHERE id = ?");
            $statement->execute([$order_id]);

            if ($statement->rowCount() > 0) {
                http_response_code(200);
                echo json_encode(array('message' => 'Order is deleted successfully'));
            } else {
                http_response_code(404);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(array('error' => 'Internal Server Error'));
        }
    } else {
        http_response_code(400);
        echo json_encode(array('error' => 'Order ID not given'));
    }
}
?>
