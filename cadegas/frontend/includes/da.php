<?php
// Database Access Helper

function get_db() {
    require_once __DIR__ . '/../../backend/config/db.php';
    return Database::getInstance();
}

function get_json_input() {
    require_once __DIR__ . '/../../backend/config/config.php';
    return get_json_input();
}

function json_response($data, $status = 200) {
    require_once __DIR__ . '/../../backend/config/config.php';
    return json_response($data, $status);
}
