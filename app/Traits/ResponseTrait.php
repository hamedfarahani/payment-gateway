<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

trait ResponseTrait
{
    /**
     * @param array   $error    Get array of errors.
     * @param integer $httpCode Get httpCode.
     * @param string  $message  Get message.
     * @return JsonResponse
     */
    public function sendError(array $error=[], int $httpCode = Response::HTTP_BAD_REQUEST, string $message = ''): JsonResponse
    {
        $body = [
            'message' => empty($message) ? trans('messages.response.bad_request') : $message,
            'error' => $error,
        ];

        return $this->makeResponse($body, $httpCode);
    }

    /**
     * @param mixed   $data     Get client Data.
     * @param integer $httpCode Get httpCode.
     * @param string  $message  Get message.
     * @return JsonResponse
     */
    public function sendSuccess(mixed $data = [], int $httpCode = Response::HTTP_OK, string $message = ''): JsonResponse
    {
        $body = [
            'message' => empty($message) ? trans('messages.response.ok') : $message,
            'data' => $data,
        ];

        return $this->makeResponse($body, $httpCode);
    }

    /**
     * @param array   $body     Get whole body to show user.
     * @param integer $httpCode Get httpCode.
     * @return JsonResponse
     */
    private function makeResponse(array $body, int $httpCode): JsonResponse
    {
        return response()->json(
            $body,
            $httpCode,
            ['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'],
            JSON_UNESCAPED_UNICODE
        );
    }
}
