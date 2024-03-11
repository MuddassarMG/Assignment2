<?php
include 'db_connection.php';

header('Content-Type: application/json');

//GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $statement = $pdo->query("SELECT * FROM comments");
        $comments = $statement->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($comments);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $statement = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
        $statement->execute([$id]);
        $comment = $statement->fetch(PDO::FETCH_ASSOC);
        if ($comment) {
            echo json_encode($comment);
        } else {
            http_response_code(404);
            echo json_encode(array('error' => 'Comment not found'));
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

//POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['product_id']) || !isset($data['user_id']) || !isset($data['rating'])) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing required fields'));
        exit;
    }

    $product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);
    $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
    $rating = filter_var($data['rating'], FILTER_VALIDATE_INT);
    $image = isset($data['image']) ? filter_var($data['image'], FILTER_SANITIZE_URL) : null;
    $text = isset($data['text']) ? filter_var($data['text'], FILTER_SANITIZE_STRING) : null;

    if (!$product_id || !$user_id || !$rating) {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid input'));
        exit;
    }

    try {
        $statement = $pdo->prepare("INSERT INTO comments (product_id, user_id, rating, image, text) VALUES (?, ?, ?, ?, ?)");
        $statement->execute([$product_id, $user_id, $rating, $image, $text]);
        echo json_encode(array('message' => 'Comment created successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}


//PUT
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);

    if (!isset($data['product_id']) || !isset($data['user_id']) || !isset($data['rating']) || !isset($data['text'])) {
        http_response_code(400);
        echo json_encode(array('error' => 'Missing required fields'));
        exit;
    }

    $product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);
    $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
    $rating = filter_var($data['rating'], FILTER_VALIDATE_INT);
    $text = filter_var($data['text'], FILTER_SANITIZE_STRING);

    if (!$product_id || !$user_id || !$rating || !$text) {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid input'));
        exit;
    }

    try {
        $statement = $pdo->prepare("UPDATE comments SET product_id = ?, user_id = ?, rating = ?, text = ? WHERE id = ?");
        $statement->execute([$product_id, $user_id, $rating, $text, $id]);
        echo json_encode(array('message' => 'Comment updated successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}


//DELETE
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $statement = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $statement->execute([$id]);
        echo json_encode(array('message' => 'Comment deleted successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
} else {
    http_response_code(400);
    echo json_encode(array('error' => 'Missing comment ID'));
}
?>
