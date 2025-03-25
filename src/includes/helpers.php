<?php

/**
 * 将数字格式化为金币显示格式
 * 例如：1234 -> 1,234g
 *
 * @param int|float $amount 金币数量
 * @return string 格式化后的字符串
 */
function formatGold($amount) {
    return number_format($amount) . 'g';
}

/**
 * 格式化日期时间
 * 
 * @param string $datetime 日期时间字符串
 * @param string $format 输出格式
 * @return string 格式化后的日期时间
 */
function formatDateTime($datetime, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($datetime));
}

/**
 * 格式化时间间隔
 * 
 * @param string|int $duration 时间间隔（分钟）或开始时间
 * @param string|null $endTime 结束时间（可选）
 * @return string 格式化后的时间间隔
 */
function formatDuration($duration, $endTime = null) {
    if ($endTime !== null) {
        // 如果提供了两个时间参数，计算时间差
        $start = new DateTime($duration); // 这里 $duration 是开始时间
        $end = new DateTime($endTime);
        $interval = $start->diff($end);
        $minutes = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;
    } else {
        // 如果只提供了一个参数，假设它是分钟数
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
 * 格式化百分比
 * 
 * @param float $value 小数值
 * @param int $decimals 小数位数
 * @return string 格式化后的百分比
 */
function formatPercentage($value, $decimals = 1) {
    return number_format($value * 100, $decimals) . '%';
}

/**
 * 截断文本
 * 
 * @param string $text 原始文本
 * @param int $length 最大长度
 * @param string $suffix 后缀
 * @return string 截断后的文本
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * 生成随机颜色
 * 
 * @return string 十六进制颜色代码
 */
function randomColor() {
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}

/**
 * 计算经验等级
 * 
 * @param int $exp 经验值
 * @return int 等级
 */
function calculateLevel($exp) {
    return floor(sqrt($exp / 100)) + 1;
}

/**
 * 获取季节名称
 * 
 * @param int $month 月份（1-12）
 * @return string 季节名称
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
 * 计算两个日期之间的天数
 * 
 * @param string $date1 第一个日期
 * @param string $date2 第二个日期
 * @return int 天数差
 */
function daysBetween($date1, $date2) {
    $d1 = new DateTime($date1);
    $d2 = new DateTime($date2);
    $diff = $d1->diff($d2);
    return abs($diff->days);
}

/**
 * 格式化文件大小
 * 
 * @param int $bytes 字节数
 * @return string 格式化后的大小
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
} 