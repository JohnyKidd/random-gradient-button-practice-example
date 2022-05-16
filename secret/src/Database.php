<?php
//separate class for database management
class Database
{
    public function __construct
    (
        private $host,
        private $db,
        private $user,
        private $password
    )
    {
    }

    public function getConnection(){
        $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8";

        return new PDO($dsn, $this->user, $this->password, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]);
    }
}
?>