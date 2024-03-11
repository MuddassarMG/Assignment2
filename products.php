<?php

//including the database file.
include 'db_connection.php';

//JSON format response.
header('Content-Type: application/json');

//Get method to get all the products
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        //SQL query for selecting all the products in table name "products".
        $statement = $pdo->query("SELECT * FROM products");
        //fetching all rows
        $products = $statement->fetchAll(PDO::FETCH_ASSOC);
        //converting array of product into a JSON format and sending the response
        echo json_encode($products);
    } catch (PDOException $e) {
        //handlling the error
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

// GET request for getting the specific product by it's ID
    //checking the get request method and the id
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    //getting the id of a product which a user wants to get
    $id = $_GET['id'];
    try {
        //SQL query for getting the product by a particular id
        $statement = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        //executing 
        $statement->execute([$id]);
        //fetching that product details.
        $product = $statement->fetch(PDO::FETCH_ASSOC);
        //checking that the product is there or not
        if ($product) {
            echo json_encode($product);
        } else {
            //if there is no such product then give the error 
            http_response_code(404);
            echo json_encode(array('error' => 'Product not found'));
        }
    } catch (PDOException $e) {
        //handlling the error.
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

// POST method for creating a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //getting the data from the JSON body and decode it into an array.
    $data = json_decode(file_get_contents("php://input"), true);

    //validate that all the fields are filled or not.
    if (!isset($data['description']) || !isset($data['price']) || !isset($data['shipping_cost'])) {
        http_response_code(400);
        //otherwise giving error that fields are required.
        echo json_encode(array('error' => 'Missing required fields'));
        exit;
    }

    // filtering the data
    $description = filter_var($data['description'], FILTER_SANITIZE_STRING);
    $price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
    $shipping_cost = filter_var($data['shipping_cost'], FILTER_VALIDATE_FLOAT);

    // it will check the input fields.
    if (!$description || !$price || !$shipping_cost) {
        http_response_code(400);
        //otherwise there will be error 
        echo json_encode(array('error' => 'Invalid input'));
        exit;
    }

    // after checking the valid input fields data will be inserted into that database. 
    try {
        //SQL query for inserting the data into that database.
        $statement = $pdo->prepare("INSERT INTO products (description, price, shipping_cost) VALUES (?, ?, ?)");
        $statement->execute([$description, $price, $shipping_cost]);
        //giving the message that product added successfully
        echo json_encode(array('message' => 'Product added successfully'));
    } catch (PDOException $e) {
        //handlling the error. 
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}


// PUT method to update the product by it's ID
    //checking the method is put and the paramater.
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    //checking by id
    $id = $_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);

    // checking all the neccessary fields.
    if (!isset($data['description']) || !isset($data['price']) || !isset($data['shipping_cost'])) {
        http_response_code(400);
        //showing error if all fields are not filled there.
        echo json_encode(array('error' => 'Missing required fields'));
        exit;
    }

    // Sanitize & filter the input
    $description = filter_var($data['description'], FILTER_SANITIZE_STRING);
    $price = filter_var($data['price'], FILTER_VALIDATE_FLOAT);
    $shipping_cost = filter_var($data['shipping_cost'], FILTER_VALIDATE_FLOAT);

    // Checking that all inputs are valid
    if (!$description || !$price || !$shipping_cost) {
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid input'));
        exit;
    }

    // Updating that product into databse.
    try {
        //SQL query to update a particular product by using the product id
        $statement = $pdo->prepare("UPDATE products SET description = ?, price = ?, shipping_cost = ? WHERE id = ?");
        $statement->execute([$description, $price, $shipping_cost, $id]);
        //showing the message that product is updated succesffully
        echo json_encode(array('message' => 'Product is updated successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        //handelling the error
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

// DELETE method to delete a particular product by ID
    //check the delete method and the parameter
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    //checking the id
    $id = $_GET['id'];
    try {
        //SQL query to delete a product by their id.
        $statement = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $statement->execute([$id]);
        //showing the message that the product is deleted successfully
        echo json_encode(array('message' => 'Product is deleted successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        //handelling the error.
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}
?>
