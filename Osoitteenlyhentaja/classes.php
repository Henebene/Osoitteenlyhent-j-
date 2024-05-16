<?php
    class yhteysinfo
    {
      private $dsn;
      private $user;
      private $pass;
      private $options;
      private $pdo;
    
      public function __construct()
      {
        $this->dsn = "mysql:host=localhost;" . "dbname={$_SERVER['DB_DATABASE']};" . "charset=utf8mb4";
        $this->user = $_SERVER['DB_USERNAME'];
        $this->pass = $_SERVER['DB_PASSWORD'];
        $this->options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->pdo = new PDO($this->dsn, $this->user, $this->pass, $this->options); 
      }
    
      public function getPdo()
      {
        return $this->pdo;
      }
    }
?>