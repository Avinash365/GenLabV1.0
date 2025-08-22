<?php
// Legacy shim for report templates to run inside Laravel
// Provides `query($sql)` using Laravel's DB facade and avoids fatal errors if DB fails.

if (!function_exists('query')) {
    function query($sql) {
        $defaultRow = function($jobCard = '') {
            return [[
                'JOB_CARD_NO' => (string)$jobCard,
                'ULR_NO' => '',
                'ISSUED_TO' => '',
                'JOB_ORDER_DATE' => '',
                'REFRENCE_NO' => '',
                'ISSUE_DATE' => '',
                'SAMPLE_DISCRIPTION' => '',
                'NAME_OF_WORK' => '',
                'CONTRACTOR' => '',
            ]];
        };

        try {
            $rows = \Illuminate\Support\Facades\DB::select($sql);
            $arr = array_map(fn($r) => (array)$r, $rows);
            if (empty($arr) && stripos($sql, 'from nonulr') !== false) {
                return $defaultRow($_GET['JOB_CARD_NO'] ?? '');
            }
            return $arr;
        } catch (\Throwable $e) {
            if (stripos($sql, 'from nonulr') !== false) {
                return $defaultRow($_GET['JOB_CARD_NO'] ?? '');
            }
            return [];
        }
    }
}
