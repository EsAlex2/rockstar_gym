<?php 

require_once __DIR__ . '/../core/conn.php';
header('Content-Type: application/json; charset=utf-8');

    class Model {
        private $pdo;
        protected string $tablas;


        public function __construct($pdo) {
            global $pdo;
            $this->pdo = $pdo;
        }

        public function addLogs()
        {
            
        }
    }