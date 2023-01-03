<?php

namespace Cirelramos\ErrorNotification\Services;

use Illuminate\Support\Collection;

/**
 *
 */
class GetAllValuesFromHeaderService
{
    /**
     * @param $request
     * @return Collection
     */
    public static function execute($request): Collection
    {
        $headers = $request->headers->all();
        $headers = collect($headers);

        return $headers->map(self::mapReplaceGetValuesHeaders());
    }

    private static function mapReplaceGetValuesHeaders(): callable
    {
        return static function ($header, $key) {
            return collect($header)->first();
        };
    }
}
