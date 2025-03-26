<?php

/**
 * change number to gold format
 * for example: 1234 -> 1,234g
 *
 * @param int|float $amount gold amount
 * @return string formatted string
 */
function formatGold($amount) {
    return number_format($amount) . 'g';
}

/**
 * format datetime
 * 
 * @param string $datetime datetime string
 * @param string $format output format
 * @return string formatted datetime
 */
function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($datetime));
}

/**
 * format duration
 * 
 * @param string|int $duration duration (minutes) or start time
 * @param string|null $endTime end time (optional)
 * @return string formatted duration
 */
function formatDuration($duration, $endTime = null) {
    if ($endTime !== null) {
        // if two time parameters are provided, calculate the duration
        $start = new DateTime($duration); // $duration is start time
        $end = new DateTime($endTime);
        $interval = $start->diff($end);
        $minutes = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
    } else {
        // if only one parameter is provided, assume it's minutes
        $minutes = intval($duration);
    }
    
    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;
    
    if ($hours > 0) {
        return $hours . 'h ' . $remainingMinutes . 'm';
    } else {
        return $minutes . ' minutes';
    }
}

/**
 * format percentage
 * 
 * @param float $value float value
 * @param int $decimals decimal places
 * @return string formatted percentage
 */
function formatPercentage($value, $decimals = 1) {
    return number_format($value * 100, $decimals) . '%';
}

/**
 * truncate text
 * 
 * @param string $text original text
 * @param int $length max length
 * @param string $suffix suffix
 * @return string truncated text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * generate random color
 * 
 * @return string hex color code
 */
function randomColor() {
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}

/**
 * calculate level
 * 
 * @param int $exp experience value
 * @return int level
 */
function calculateLevel($exp) {
    return floor(sqrt($exp / 100)) + 1;
}

/**
 * get season name
 * 
 * @param int $month month (1-12)
 * @return string season name
 */
function getSeason($month) {
    $seasons = [
        'Spring' => [3, 4, 5],
        'Summer' => [6, 7, 8],
        'Fall' => [9, 10, 11],
        'Winter' => [12, 1, 2]
    ];
    
    foreach ($seasons as $season => $months) {
        if (in_array($month, $months)) {
            return $season;
        }
    }
    
    return 'Unknown';
}

/**
 * calculate the days between two dates
 * 
 * @param string $date1 first date
 * @param string $date2 second date
 * @return int days difference
 */
function daysBetween($date1, $date2) {
    $d1 = new DateTime($date1);
    $d2 = new DateTime($date2);
    $diff = $d1->diff($d2);
    return abs($diff->days);
}

/**
 * format file size
 * 
 * @param int $bytes bytes
 * @return string formatted size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
} 