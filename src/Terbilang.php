<?php

# PHP Terbilang - Mengubah Angka Menjadi Huruf Terbilang.
# https://github.com/nggit/php-terbilang
# Copyright (c) 2021 nggit.

namespace Nggit\PHPTerbilang;

use Exception;

class Terbilang
{
    /**
     * @var string[]
     */
    protected $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
    
    /**
     * @var array
     */
    protected $suffixes = [
        ['belas', 'puluh'], ['', 'ratus'], ['', 'ribu', 'juta', 'miliar'],
        ['', 'triliun', 'septiliun', 'undesiliun', 'kuindesiliun', 'novemdesiliun', 'trevigintiliun', 'septenvigintiliun', 'untrigintiliun'],
    ];
    
    /**
     * @var string
     */
    public $separator;
    
    /**
     * @var string[]
     */
    protected $separators = [',', '.'];

    /**
     * @var array
     */
    protected $result = [];
    
    /**
     * Terbilang constructor.
     * @param  string $num
     * @param  string $sep
     * @throws Exception
     */
    public function __construct($num = '', $sep = ',')
    {
        $this->separator = $sep;

        if ($num != '') {
            $this->parse($num, $sep);
        }
    }
    
    /**
     * @param  array $result
     * @return string
     */
    public function getResult($result = [])
    {
        return $result ? strtr(
            rtrim(implode(' ', array_filter($result, 'strlen')), ' ,'),
            ['satu ratus' => 'seratus', 'satu ribu' => 'seribu', ';' => '']
        ) : implode(' ', $this->result);
    }
    
    /**
     * @param  string $num
     * @return array|string|string[]
     */
    public function filter_num($num = '')
    {
        for ($n = 0; $n < strlen($num); $n++) {
            if (ord($num[$n]) < 48 || ord($num[$n]) > 57) {
                $num[$n] = ' ';
            }
        }
        
        return str_replace(' ', '', $num);
    }
    
    /**
     * @param  string $num
     * @return $this
     */
    public function spell($num = '')
    {
        $this->result = [];

        for ($n = 0; $n < strlen($num); $n++) {
            if (ord($num[$n]) >= 48 && ord($num[$n]) <= 57) {
                $this->result[] = $num[$n] == '0' ? 'nol' : $this->words[(int) $num[$n]];
            }
        }
        
        return $this;
    }
    
    /**
     * @param  string $num
     * @param  int    $level
     * @return $this
     * @throws Exception
     */
    protected function read($num, $level = 12)
    {
        if ($level == 12) {
            $num = $this->filter_num($num);

            if (strpos($num, '0') === 0) {
                return $this->spell($num);
            }

            if (strlen($num) > 108) {
                throw new Exception('Angka yang anda masukkan terlalu besar');
            }
        }

        $i = (int) ((strlen($num) - 1) / $level);
        $part = substr($num, 0, strlen($num) - $i * $level);

        if ($level == 12) {
            $this->read($part, 3);
            $this->result[] = $this->suffixes[3][$i] . ','; // triliun, ...
        } elseif ($level == 3) {
            $this->read($part, 2);
            $this->result[] = $this->suffixes[2][$i]; // ribu, juta, miliar
        } elseif ($level == 2) {
            if ((int) $part < count($this->words)) {
                $this->result[] = trim($this->words[(int) $part] . ' ' . $this->suffixes[1][$i]); // ratus
            } else {
                if ((int) $part[0] == 1) {
                    $this->result[] = $this->words[(int) $part[1]] . ' ' . $this->suffixes[0][0]; // belas
                } else {
                    $this->result[] = trim(
                        $this->words[(int) $part[0]] . ' ' .
                        $this->suffixes[0][1] . ' ' .
                        $this->words[(int) $part[1]]
                    ) . ';'; // puluh
                }
            }
        }

        $num = ltrim(substr($num, strlen($num) - $i * $level), '0');

        if ($num == '') {
            return $this;
        }

        return $this->read($num, $level);
    }
    
    /**
     * @param  string $num
     * @param  string $sep
     * @return $this
     * @throws Exception
     */
    public function parse($num = '', $sep = '')
    {
        if ($sep == '') {
            $sep = $this->separator;
        }

        if (! in_array($sep, $this->separators)) {
            throw new Exception('Harap gunakan koma atau titik sebagai pemisah');
        }

        if ($num == '') {
            return $this;
        }
        
        $this->result = [];
        $result = [];
        $num = trim((string) $num, ' ,.');

        if (strpos($num, '-') === 0 && trim($num, ',-.0') != '') {
            $result[] = 'minus';
        }
        
        if (($sep_pos = strrpos($num, $sep))) {
            $result[] = $this->getResult($this->read(substr($num, 0, $sep_pos))->result);
            $result[] = 'koma';
            $result[] = $this->spell(substr($num, $sep_pos))->getResult();
        } else {
            $sep_alt = $this->separators[array_search($sep, $this->separators) ^ 1];
            $sep_alt_pos = strpos($num, $sep_alt);
    
            if ($sep_alt_pos && strpos($num, '0') === 0 || substr_count($num, $sep_alt) == 1 && strlen(substr($num, $sep_alt_pos)) != 4) {
                $result[] = $this->getResult($this->read(substr($num, 0, $sep_alt_pos))->result);
                $result[] = 'koma';
                $result[] = $this->spell(substr($num, $sep_alt_pos))->getResult();
            } else {
                $result[] = $this->getResult($this->read($num)->result);
            }
        }
    
        $this->result = $result;
        return $this;
    }
}
