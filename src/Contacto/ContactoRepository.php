<?php

declare(strict_types=1);

namespace App\Contacto;

use PDO;
use RuntimeException;

/**
 * Acceso a la tabla contactos. Todas las consultas van preparadas.
 */
final class ContactoRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function existeEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM contactos WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        return $stmt->fetchColumn() !== false;
    }

    public function existeTelefono(string $telefono): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM contactos WHERE telefono = :telefono LIMIT 1');
        $stmt->execute(['telefono' => $telefono]);

        return $stmt->fetchColumn() !== false;
    }

    /**
     * @return array{id: int, nombre: string, email: string, telefono: ?string, created_at: string}
     */
    public function crear(string $nombre, string $email, ?string $telefono): array
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contactos (nombre, email, telefono) VALUES (:nombre, :email, :telefono)',
        );
        $stmt->execute(['nombre' => $nombre, 'email' => $email, 'telefono' => $telefono]);

        $stmt = $this->pdo->prepare('SELECT id, nombre, email, telefono, created_at FROM contactos WHERE id = :id');
        $stmt->execute(['id' => (int) $this->pdo->lastInsertId()]);
        $fila = $stmt->fetch();
        if (!is_array($fila)) {
            throw new RuntimeException('No se encontró el contacto recién insertado.');
        }

        return [
            'id' => (int) $fila['id'],
            'nombre' => (string) $fila['nombre'],
            'email' => (string) $fila['email'],
            'telefono' => $fila['telefono'] === null ? null : (string) $fila['telefono'],
            'created_at' => (string) $fila['created_at'],
        ];
    }
}
