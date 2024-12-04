<?php

namespace Api;

use Zernite\util\ResponseHandler;

abstract class BaseApi {
    protected mixed $requestData;

    public function __construct() {
        $this->requestData = json_decode(file_get_contents('php://input'), true);
    }

    public function handleRequest(string $action) {
        if (method_exists($this, $action)) {
            return $this->$action();
        } else {
            ResponseHandler::sendError("Invalid action: $action");
            return null;
        }
    }

    protected function validateRequiredFields(array $fields): void {
        foreach ($fields as $field) {
            if (!isset($this->requestData[$field])) {
                ResponseHandler::sendMissingFieldsError();
                exit;
            }
        }
    }
}