<?php
//setting up an autoloader for classes
spl_autoload_register(function ($class) {
    require __DIR__ . "/src/$class.php";
});

$requestParts = explode("/", $_SERVER["REQUEST_URI"]);

//check if the user hit the correct endpoint, if not, give a 404 response
if ($requestParts[1] != "secret"){
    http_response_code(404);
}

//get the hash part out of the request for further use, or set it to null if it is not present
$hash = $requestParts[2] ?? null;

//connecting to the database
require_once("config.php");
$database = new Database($host, $db, $user, $password);
$database->getConnection();

//get the type of the method from the request
$method = $_SERVER["REQUEST_METHOD"];

//creating the processor and processing the requests
$requestProcessor = new Processor($database);
$requestProcessor->processRequest($method , $hash);
?>