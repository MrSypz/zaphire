<?php

use Api\TeamApi;
use Api\UserApi;

require_once __DIR__ . '/../../vendor/autoload.php';

$entityMap = [
        'User' => UserApi::class,
        'Team' => TeamApi::class,
];

$entity = $_GET['entity'] ?? null;
$action = $_GET['action'] ?? null;

$entity = filter_var($entity, FILTER_SANITIZE_STRING);
$action = filter_var($action, FILTER_SANITIZE_STRING);

if ($entity && isset($entityMap[$entity])) {
    $apiClass = new $entityMap[$entity]();
    $apiClass->handleRequest($action);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid entity or action']);
}
