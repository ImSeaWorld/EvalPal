<?php
class DBCon
{
    public $PDOError;
    private $connection;

    private function __construct()
    {
        $dbhost = "127.0.0.1";
        $dbname = "dt";
        $dbuser = "root";
        $dbpass = "";
        try {
            $this->connection = new PDO("mysql:host=" . $dbhost . ";dbname=" . $dbname . ";charset=utf8", $dbuser, $dbpass);
        } catch (PDOException $e) {
            $this->PDOError = $e->getMessage();
            echo "DBCon Error!\n" . $e->getMessage();
        }
    }

    public static function getConnection()
    {
        if (!isset($db)) $db = new DBCon();
        return $db->connection;
    }
}
