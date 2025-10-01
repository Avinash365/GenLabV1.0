<?php

namespace App\Services;

class CountTextLineBreakService
{
    public function countLineBreaks(array $texts): array
    {
        $totalR = 0;
        $totalN = 0;

        foreach ($texts as $index => $text) {
            $countR = substr_count($text, "\r");
            $countN = substr_count($text, "\n");

            if ($index === 0) {
                $countR = max($countR - 3, 0);
                $countN = max($countN - 3, 0);
            } elseif ($index === 1) {
                $countR = max($countR - 2, 0);
                $countN = max($countN - 2, 0);
            }
            $totalR += $countR;
            $totalN += $countN;
        }

        return [
            'total_r' => $totalR,
            'total_n' => $totalN,
            'total'   => $totalR + $totalN,
        ];
    }
}
