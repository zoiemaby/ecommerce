<?php

class Database
{
    /** @var mysqli|null */
    protected $conn = null;


    public function __construct($credPath = null)
    {
        // Always use the absolute path to db_cred.php
        $credFile = __DIR__ . '/db_cred.php';
        if (file_exists($credFile)) {
            require_once $credFile;
        } else {
            throw new Exception('Database credentials file not found at ' . $credFile);
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
// ...existing code...


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
