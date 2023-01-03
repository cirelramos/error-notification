<?php

namespace Cirelramos\ErrorNotification\Services;

use Illuminate\Support\Facades\Auth;
use Cirelramos\Logs\Services\GetTrackerService;

/**
 *
 */
class GetInfoFromExceptionService
{
    /**
     * @param       $exception
     * @param array $extraValues
     * @return array
     */
    public static function execute($exception, $extraValues = [], $enableTracker = true): array
    {
        $request      = request();
        $message = empty($exception->getMessage()) ? "-" : $exception->getMessage();
        $infoEndpoint = [
            'message_error' => $message,
            'uri'           => $request->getRequestUri(),
            'method'        => $request->method(),
            'user'          => Auth::id(),
        ];

        foreach (config('error-notification.get_special_values_from_header') as $key => $item) {
            if ($request->$item) {
                $infoEndpoint[ $key ] = $request->header($item);
            }
        }
        foreach (config('error-notification.get_special_values_from_request') as $key => $item) {
            if ($request->$item) {
                $infoEndpoint[ $key ] = $request->$item;
            }
        }

        $infoEndpoint[ 'environment' ]    = env('APP_ENV');
        $infoEndpoint[ 'code' ]           = $exception->getCode();
        $infoEndpoint[ 'line' ]           = $exception->getLine();
        $infoEndpoint[ 'file' ]           = $exception->getFile();
        if ($enableTracker === true) {
            $tracker = GetTrackerService::execute($exception->getTrace());
            $infoEndpoint[ 'tracker' ] = $tracker;
        }
        $infoEndpoint[ 'name_exception' ] = get_class($exception);
        $infoEndpoint[ 'count_errors' ]   = 1;

        $headers = GetAllValuesFromHeaderService::execute($request);

        $infoEndpoint = array_merge($infoEndpoint, $headers->toArray());

        $infoEndpoint = array_merge($infoEndpoint, $extraValues);

        if (array_key_exists('authorization', $infoEndpoint)) {
            $authorization = $infoEndpoint[ 'authorization' ];
            unset($infoEndpoint[ 'authorization' ]);
            $infoEndpoint[ 'authorization' ] = $authorization;
        }

        return $infoEndpoint;
    }
}
