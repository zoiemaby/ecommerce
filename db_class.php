<?php

class Database
{
    /** @var mysqli|null */
    protected $conn = null;


    public function __construct($credPath = null)
    {

        if ($credPath && file_exists($credPath)) {
            require_once $credPath;
        } else {
            $trypath = __DIR__ . '/../settings/db_cred.php';
            if (file_exists($trypath)) {
                require_once $trypath;
            } else {

                $local = __DIR__ . '/db_cred.php';
                if (file_exists($local)) {
                    require_once $local;
                }
            }
        }

        if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_NAME')) {
            throw new Exception(
                'Database credentials not found. 
                Make sure db_cred.php is available and defines 
                DB_HOST, DB_USER, DB_PASS, DB_NAME.'
            );
        }

        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            throw new Exception('Database connection failed: ' . $this->conn->connect_error);
        }
    }


    public function getConnection()
    {
        return $this->conn;
    }

   
    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
