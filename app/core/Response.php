<?php

namespace App\Core;

use App\Interfaces\ResponseInterface;
use JsonSerializable;

/**
 * Class Response
 * Representa una respuesta estándar e inmutable de la aplicación,
 * facilitando la serialización polimórfica a Array o JSON.
 */
class Response implements ResponseInterface, JsonSerializable
{
    private bool $success;
    private string $message;
    private mixed $data;
    private int $statusCode;

    /**
     * @param bool $success Indica si la operación concluyó exitosamente
     * @param string $message Mensaje descriptivo del resultado
     * @param mixed $data Datos adjuntos o payload
     * @param int $statusCode Código de estado HTTP
     */
    public function __construct(bool $success, string $message, mixed $data = null, int $statusCode = 200)
    {
        $this->success    = $success;
        $this->message    = $message;
        $this->data       = $data;
        $this->statusCode = $statusCode;
    }

    public static function success(string $message = "Operación exitosa", mixed $data = null, int $code = 200): self
    {
        return new self(true, $message, $data, $code);
    }

    public static function error(string $message = "Ocurrió un error", mixed $data = null, int $code = 400): self
    {
        return new self(false, $message, $data, $code);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function toArray(): array
    {
        $response = [
            "status"  => $this->success ? "success" : "error",
            "success" => $this->success,
            "message" => $this->message
        ];

        if ($this->data !== null) {
            $response["data"] = $this->data;
        }

        return $response;
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return $this->toJson();
    }
}
