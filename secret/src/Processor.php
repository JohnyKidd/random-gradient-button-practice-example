<?php
//creating a separete class for processing the requests

class Processor
{
    //using the constructor to initialize the connection to the database

    private $connection;
    public function __construct($database){
        $this->connection = $database->getConnection();
    }

    //method processing the request based on the method (only GET and POST requests are allowed, everything else gives 405)

    public function processRequest($method, $hash){
        if ($method == "GET"){
            $this->getSecret($hash);
        }
        elseif ($method == "POST"){
            $this->createSecret();
        }
        else{
            http_response_code(405);
        }
    }
    //method for getting a single secret by hash

    private function getSecret($hash){
        if ($hash != null){
            //decrementing the remaining views before processing the request

            $sql = "UPDATE secret
                    SET remainingViews=remainingViews-1
                    WHERE hash = '".$hash."'";
            
            $this->connection->query($sql);

            //fetching the data to an associative array for further processing

            $sql = "SELECT *
                FROM secret
                WHERE hash = '".$hash."'";
            
            $stmt = $this->connection->query($sql);
            $data = $stmt->fetch(PDO::FETCH_ASSOC) ?? null;
            
            /*Printing the data with the desired format (XML/JSON) or if the data is null,
            or the hash is expired,
            giving back a 404 error*/

            if ($data != null && $data["remainingViews"]>=0 && $data["expiresAt"] > date("Y-m-d")){
                switch ($_SERVER["HTTP_ACCEPT"]):

                    case "application/json":
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode($data);
                        break;

                    case "application/xml":
                        header('Content-Type: application/xml; charset=utf-8');
                        echo "
                        <?xml version='1.0' encoding='UTF-8'?>
                        <Secret>
                        <hash>{$data['hash']}</hash>
                        <secretText>{$data['secretText']}</secretText>
                        <createdAt>{$data['createdAt']}</createdAt>
                        <expiresAt>{$data['expiresAt']}</expiresAt>
                        <remainingViews>{$data['remainingViews']}</remainingViews>
                        </Secret>
                        ";
                        break;

                    default:
                        http_response_code(415);

                endswitch;
            }
            else{
                http_response_code(404);
            }
            
        }
        else{
            http_response_code(404);
        }
    }
//method for reading from the input stream and creating a secret in the database
    private function createSecret(){
        //creating a data array from input stream. I need to refactor this later, there must be a better method for this
        $data = file_get_contents("php://input");
        $data = str_replace("&","/",$data);
        $data = str_replace("=","/",$data);
        $data = explode("/", $data);

        //creating a unique id and creating date for the incoming secret
        $uniqueId = uniqid();
        $date = date("Y-m-d");

        //inserting the elements of the data array into the database
        $sql = "INSERT INTO
                secret(`hash`, `secretText`, `createdAt`, `expiresAt`, `remainingViews`)
                VALUES ('".$uniqueId."','".$data[1]."','".$date."','".$data[5]."','".$data[3]."');";
            
        $this->connection->query($sql);

        //respond with the data of the added secret
        $sql = "SELECT *
                FROM secret
                WHERE hash = '".$uniqueId."'";
            
            $stmt = $this->connection->query($sql);
            $data = $stmt->fetch(PDO::FETCH_ASSOC) ?? null;

            switch ($_SERVER["HTTP_ACCEPT"]):

                case "application/json":
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($data);
                    break;

                case "application/xml":
                    header('Content-Type: application/xml; charset=utf-8');
                    echo "
                    <?xml version='1.0' encoding='UTF-8'?>
                    <Secret>
                    <hash>{$data['hash']}</hash>
                    <secretText>{$data['secretText']}</secretText>
                    <createdAt>{$data['createdAt']}</createdAt>
                    <expiresAt>{$data['expiresAt']}</expiresAt>
                    <remainingViews>{$data['remainingViews']}</remainingViews>
                    </Secret>
                    ";
                    break;

                default:
                    http_response_code(415);

            endswitch;
    }
}
?>