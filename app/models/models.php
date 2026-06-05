<?php 

require_once __DIR__ . '/../core/conn.php';

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