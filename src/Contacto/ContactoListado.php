<?php

declare(strict_types=1);

namespace App\Contacto;

use PDO;

/**
 * Consultas de lectura para el listado de contactos. Todas preparadas.
 */
final class ContactoListado
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function contar(): int
    {
        $sentencia = $this->pdo->prepare('SELECT COUNT(*) FROM contactos');
        $sentencia->execute();

        return (int) $sentencia->fetchColumn();
    }

    /**
     * Una página de contactos, del más reciente al más antiguo.
     *
     * @return list<array{id: int, nombre: string, email: string, telefono: string|null, created_at: string}>
     */
    public function pagina(int $limit, int $offset): array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT id, nombre, email, telefono, created_at FROM contactos'
            . ' ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset',
        );
        $sentencia->bindValue(':limit', $limit, PDO::PARAM_INT);
        $sentencia->bindValue(':offset', $offset, PDO::PARAM_INT);
        $sentencia->execute();

        $filas = [];
        /** @var list<array{id: int|string, nombre: string, email: string, telefono: string|null, created_at: string}> $rows */
        $rows = $sentencia->fetchAll();
        foreach ($rows as $fila) {
            $filas[] = [
                'id' => (int) $fila['id'],
                'nombre' => $fila['nombre'],
                'email' => $fila['email'],
                'telefono' => $fila['telefono'],
                'created_at' => $fila['created_at'],
            ];
        }

        return $filas;
    }
}
