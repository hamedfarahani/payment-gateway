<?php

use Illuminate\Http\Response;

if (!function_exists('makeException')) {

    /**
     * @param $error
     * @param string $message
     * @return mixed
     * @throws OdinException
     */
    function makeException($error, $message = ''): mixed
    {
        $code = $error instanceof Throwable ? $error->getCode() : $error;

        if (empty($message)) {
            $message = $error instanceof OdinException ? $error->getMessage() : __('messages.response.undefined_error');
        }

        if ($error instanceof Throwable) {
            throw $error;
        }

        if ($error instanceof  Error){
            $code=500;
        }

        if(!in_array($code, [Response::HTTP_BAD_REQUEST, Response::HTTP_UNPROCESSABLE_ENTITY])){
            $messageLog = $error instanceof Throwable ? $error->getMessage() : $message;
            logger()->error($messageLog);
        }

        throw new OdinException($code, $message);
    }
}