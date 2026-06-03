<?php 

require_once __DIR__ . '/models.php';
require_once __DIR__ . '/../core/conn.php';

class clientesModel extends Model
{
    protected $pdo;
    protected int $estatus_id; 
    protected int $persona_id;
    protected int $rol_id;
    protected string $username;
    protected string $email;
    protected string $password;
    protected array $mensajes = [];

    public function __construct($pdo)
    {   
        parent::__construct($pdo);
        $this->pdo = $pdo;
    }

    public function obtenerClientes()
    {
        $this->mensajes = [
            "Error de conexion a la base de datos",
            "Error inesperado al obtener los clientes"
        ];

        try{

            if(!$this->pdo){
                return $this->mensajes[0];
            }

            $stmt = $this->pdo->prepare("SELECT id, id_estatus");

        } catch (PDOException $e) {
            return $this->mensajes[1] . ": " . $e->getMessage();
        }
    }
}