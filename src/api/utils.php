<?php
// 引入配置文件
require_once 'config.php';

/**
 * Verify request method
 * Check if the current request uses the specified HTTP method
 * 
 * @param string $method Expected HTTP method (GET, POST, PUT, DELETE)
 * @return bool If the method matches, return true, otherwise return false
 */
function validateMethod($method) {
    return $_SERVER['REQUEST_METHOD'] === $method;
}

/**
 * Get request data
 * Get data from the request based on the request method
 * 
 * @return array Request data
 */
function getRequestData() {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            return $_GET;
        case 'POST':
            return $_POST;
        case 'PUT':
        case 'DELETE':
            parse_str(file_get_contents('php://input'), $data);
            return $data;
        default:
            return [];
    }
}

/**
 * Validate required fields
 * Check if all required fields are present in the data array
 * 
 * @param array $data Data array to check
 * @param array $requiredFields Array of required fields
 * @return bool If all required fields are present, return true, otherwise return false
 */
function validateRequiredFields($data, $requiredFields) {
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            return false;
        }
    }
    return true;
}
