<?php

//including the database file.
include 'db_connection.php';

//JSON format response.
header('Content-Type: application/json');

//Get method to get all the users
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        //SQL query for selecting all the users in table name "users".
        $statement = $pdo->query("SELECT id, email, username, purchase_history, shipping_address FROM users");
        //fetching all rows
        $users = $statement->fetchAll(PDO::FETCH_ASSOC);
        //converting array of users into a JSON format and sending the response
        echo json_encode($users);
    } catch (PDOException $e) {
         //handlling the error
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

// GET request for getting the specific user by it's ID
    //checking the get request method and the id
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    //getting the id of a user 
    $id = $_GET['id'];
    try {
         //SQL query for getting the user by a particular id
        $statement = $pdo->prepare("SELECT id, email, username, purchase_history, shipping_address FROM users WHERE id = ?");
        //executing 
        $statement->execute([$id]);
        //fetching that user details.
        $user = $statement->fetch(PDO::FETCH_ASSOC);
          //checking that the user is there or not
        if ($user) {
            echo json_encode($user);
        } else {
            http_response_code(404);
            //if there is no such user then give the error 
            echo json_encode(array('error' => 'User not found'));
        }
    } catch (PDOException $e) {
        //handlling the error.
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

// POST method to create a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     //getting the data from the JSON body and decode it into an array.
    $data = json_decode(file_get_contents("php://input"), true);

    //validate that all the fields are filled or not.
    if (!isset($data['email']) || !isset($data['password']) || !isset($data['username'])) {
        http_response_code(400);
        //otherwise giving error that fields are required.
        echo json_encode(array('error' => 'Missing required fields'));
        exit;
    }

    // filtering the data
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash password for security
    $username = filter_var($data['username'], FILTER_SANITIZE_STRING);

    // it will check the input fields.
    if (!$email || !$password || !$username) {
        //otherwise there will be error 
        http_response_code(400);
        echo json_encode(array('error' => 'Invalid input'));
        exit;
    }

    // after checking the valid input fields data will be inserted into that database. 
    try {
        //SQL query for inserting the data into that database.
        $statement = $pdo->prepare("INSERT INTO users (email, password, username) VALUES (?, ?, ?)");
        $statement->execute([$email, $password, $username]);
        //giving the message that product added successfully
        echo json_encode(array('message' => 'User created successfully'));
    } catch (PDOException $e) {
        //handlling the error. 
        http_response_code(500);
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

// PUT method to update the user by it's ID
    //checking the method is put and the paramater.
if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['id'])) {
    //checking by id
    $id = $_GET['id'];
    $data = json_decode(file_get_contents("php://input"), true);

    // checking all the neccessary fields.
    if (!isset($data['email']) || !isset($data['password']) || !isset($data['username'])) {
        http_response_code(400);
        //showing error if all fields are not filled there.
        echo json_encode(array('error' => 'Missing required fields'));
        exit;
    }

    //Checking that all inputs are valid
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($data['password'], PASSWORD_DEFAULT); // Hash the password
    $username = filter_var($data['username'], FILTER_SANITIZE_STRING);

    // Updating that user into databse.
    try {
        //SQL query to update a particular user by using the product id
        $statement = $pdo->prepare("UPDATE users SET email = ?, password = ?, username = ? WHERE id = ?");
        $statement->execute([$email, $password, $username, $id]);
        //showing the message that user is updated succesffully
        echo json_encode(array('message' => 'User is updated successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        //handelling the error.
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

// DELETE method to delete a particular user by ID
    //check the delete method and the parameter
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Delete user from the database
    try {
        //SQL query to delete a user by their id.
        $statement = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $statement->execute([$id]);
         //showing the message that the user is deleted successfully
        echo json_encode(array('message' => 'User deleted successfully'));
    } catch (PDOException $e) {
        http_response_code(500);
        //handelling the error.
        echo json_encode(array('error' => 'Internal Server Error'));
    }
}

?>
