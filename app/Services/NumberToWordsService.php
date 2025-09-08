<?php

namespace App\Services;

class NumberToWordsService
{
    protected $dictionary = [
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'forty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        100000              => 'lakh',
        10000000            => 'crore'
    ];

    public function convert($number)
    {
        if (!is_numeric($number)) return false;
        
        // Handle decimals properly
        if (strpos((string)$number, '.') !== false) {
            $parts = explode('.', (string)$number);
            $whole = (int)$parts[0];
            $decimal = (int)rtrim($parts[1], '0'); // remove trailing zeros

            $result = $this->convert($whole);
            if ($decimal > 0) {
                $result .= ' point';
                foreach (str_split((string)$parts[1]) as $digit) {
                    $result .= ' ' . $this->dictionary[(int)$digit];
                }
            }
            return $result;
        }

        if ($number < 0) {
            return 'negative ' . $this->convert(abs($number));
        }

        if ($number < 21) {
            return $this->dictionary[$number];
        } elseif ($number < 100) {
            $tens   = ((int)($number / 10)) * 10;
            $units  = $number % 10;
            $str = $this->dictionary[$tens];
            if ($units) $str .= '-' . $this->dictionary[$units];
            return $str;
        } elseif ($number < 1000) {
            $hundreds = (int)($number / 100);
            $remainder = $number % 100;
            $str = $this->dictionary[$hundreds] . ' ' . $this->dictionary[100];
            if ($remainder) $str .= ' and ' . $this->convert($remainder);
            return $str;
        } elseif ($number < 100000) { // thousand
            $thousands = (int)($number / 1000);
            $remainder = $number % 1000;
            $str = $this->convert($thousands) . ' thousand';
            if ($remainder) $str .= ', ' . $this->convert($remainder);
            return $str;
        } elseif ($number < 10000000) { // lakh
            $lakhs = (int)($number / 100000);
            $remainder = $number % 100000;
            $str = $this->convert($lakhs) . ' lakh';
            if ($remainder) $str .= ', ' . $this->convert($remainder);
            return $str;
        } else { // crore
            $crores = (int)($number / 10000000);
            $remainder = $number % 10000000;
            $str = $this->convert($crores) . ' crore';
            if ($remainder) $str .= ', ' . $this->convert($remainder);
            return $str;
        }
    }
}
