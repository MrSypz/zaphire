<?php

namespace Zernite\util;

class ResponseHandler {
    public static function sendSuccess($message, $data = []): void {
        echo json_encode([
                'success' => true,
                'message' => $message,
                'data' => $data,
        ]);
    }
    public static function sendData($data = []): string|bool {
        return json_encode([
                'success' => true,
                'data' => $data,
        ]);
    }

    public static function sendError($error): void {
        echo json_encode([
                'success' => false,
                'error' => $error,
        ]);
    }

    public static function sendInvalidInputError(): void {
        self::sendError('Invalid input.');
    }

    public static function sendMissingFieldsError(): void {
        self::sendError('Missing required fields.');
    }
}