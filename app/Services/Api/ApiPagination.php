<?php

namespace App\Services\Api;

/** Validates bounded page inputs and calculates offsets without integer overflow. */
final class ApiPagination
{
    public const MAX_PAGE = 10000;

    /** @return array{page:int,per_page:int,offset:int}|null */
    public static function resolve($pageInput, $perPageInput, int $defaultPerPage, int $maxPerPage): ?array
    {
        $page = 1;
        if ($pageInput !== null && $pageInput !== '') {
            if (!is_scalar($pageInput)) return null;
            $parsedPage = filter_var((string)$pageInput, FILTER_VALIDATE_INT);
            if ($parsedPage === false || $parsedPage < 1 || $parsedPage > self::MAX_PAGE) return null;
            $page = $parsedPage;
        }

        $perPage = $defaultPerPage;
        if ($perPageInput !== null && $perPageInput !== '') {
            if (!is_scalar($perPageInput)) return null;
            $parsedPerPage = filter_var((string)$perPageInput, FILTER_VALIDATE_INT);
            if ($parsedPerPage === false || $parsedPerPage < 1) return null;
            $perPage = min($maxPerPage, $parsedPerPage);
        }

        return ['page' => $page, 'per_page' => $perPage, 'offset' => ($page - 1) * $perPage];
    }
}
