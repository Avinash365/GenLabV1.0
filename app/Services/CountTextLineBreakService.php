<?php

namespace App\Services;

class CountTextLineBreakService
{
   
    public function countLineBreaks(array $texts): array
    {
        $totalR = 0;
        $totalN = 0;

        foreach ($texts as $text) {
            $totalR += substr_count($text, "\r");
            $totalN += substr_count($text, "\n");
        }

        return [
            'total_r' => $totalR,
            'total_n' => $totalN,
            'total'   => $totalR + $totalN,
        ];
    }
}
