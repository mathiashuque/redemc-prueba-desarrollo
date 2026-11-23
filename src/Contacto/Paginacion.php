<?php

declare(strict_types=1);

namespace App\Contacto;

/**
 * Cálculo de página, offset y total de páginas del listado. No toca la base.
 *
 * Reglas: una página inválida (nula, no entera o menor que 1) cuenta como la 1;
 * una página mayor que la última devuelve la última. Sin registros hay una
 * sola página (la 1), así la UI siempre tiene una página válida que mostrar.
 */
final class Paginacion
{
    public const POR_PAGINA = 8;

    public readonly int $page;
    public readonly int $offset;
    public readonly int $totalPages;

    /**
     * @param string|null $paginaSolicitada Valor crudo de `$_GET['page']`.
     * @param int         $total            Cantidad total de registros (>= 0).
     */
    public function __construct(?string $paginaSolicitada, public readonly int $total)
    {
        $this->totalPages = $total === 0 ? 1 : intdiv($total + self::POR_PAGINA - 1, self::POR_PAGINA);

        $solicitada = $paginaSolicitada === null
            ? false
            : filter_var($paginaSolicitada, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $pagina = $solicitada === false ? 1 : $solicitada;

        $this->page = min($pagina, $this->totalPages);
        $this->offset = ($this->page - 1) * self::POR_PAGINA;
    }
}
