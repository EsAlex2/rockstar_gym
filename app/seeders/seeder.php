<?php 

    require_once __DIR__ . '/../core/conn.php';

    class Seeder
    {
        protected $pdo;

        public function __construct($pdo)
        {
            $this->pdo = $pdo;
        }

        public function runSeeder()
        {
            
        }
    }