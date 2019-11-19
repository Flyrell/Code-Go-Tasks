<?php

namespace App\Helper;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    /**
     * @var int
     */
    const PAGE_SIZE_DEFAULT = 10;

    /**
     * @param mixed $value
     *
     * @throws InvalidArgumentException
     *
     * @return mixed
     */
    public function filterScalar($value)
    {
        if (!isset($value)) {
            return null;
        }

        if (is_array($value)) {
            $value = reset($value);
        }

        if (!is_scalar($value)) {
            throw new InvalidArgumentException('Only scalar values are allowed.');
        }

        return $value;
    }

    /**
     * @param Request $request
     * @param string  $pageParameterName     Name of the request get parameter which to use for $page
     * @param string  $pageSizeParameterName Name of the request get parameter which to use for $pageSize
     *
     * @return array [$offset, $limit]
     */
    public static function getOffsetLimit(Request $request, $pageParameterName = 'page', $pageSizeParameterName = 'pageSize'): array
    {
        $page = max($request->query->getInt($pageParameterName), 1);
        if ($request->query->has($pageSizeParameterName)) {
            $pageSize = max($request->query->getInt($pageSizeParameterName), 1);
        } else {
            $pageSize = static::PAGE_SIZE_DEFAULT;
        }
        $offset = ($page - 1) * $pageSize;

        return [$offset, $pageSize];
    }
}
