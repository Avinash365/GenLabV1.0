<?php
namespace App\Services;

use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DocxHtmlConverter
{
    public function convert(string $absolutePath): ?string
    {
        // Try mammoth via node first
        $html = $this->tryMammoth($absolutePath);
        if($html){
            $body = $this->extractBody($html);
            // If body extremely small (e.g., only paragraph tags or empty), fallback
            if(strlen(strip_tags($body)) < 10){
                $html = null; // force fallback
            } else {
                return $body;
            }
        }
        // Fallback to PHPWord
        $html = $this->tryPhpWord($absolutePath);
        return $html ? $this->extractBody($html) : null;
    }

    protected function tryMammoth(string $absolutePath): ?string
    {
        $script = base_path('node-scripts/convert-docx.js');
        if(!file_exists($script)) return null;
        $cmd = 'node '.escapeshellarg($script).' '.escapeshellarg($absolutePath);
        $descriptor = [1 => ['pipe','w'], 2 => ['pipe','w']];
        $proc = proc_open($cmd, $descriptor, $pipes, base_path());
        if(!is_resource($proc)) return null;
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        foreach($pipes as $p){ fclose($p); }
        $exit = proc_close($proc);
        if($exit === 0 && trim($stdout) !== '') return $stdout;
        return null;
    }

    protected function tryPhpWord(string $absolutePath): ?string
    {
        try {
            $phpWord = PhpWordIOFactory::load($absolutePath);
            $tmp = storage_path('app/tmp/'.Str::uuid().'.html');
            if(!is_dir(dirname($tmp))) @mkdir(dirname($tmp),0755,true);
            PhpWordIOFactory::createWriter($phpWord, 'HTML')->save($tmp);
            return @file_get_contents($tmp) ?: null;
        } catch(\Throwable $e){
            return null;
        }
    }

    protected function extractBody(string $raw): string
    {
        if(preg_match('/<body[^>]*>(.*)<\/body>/is', $raw, $m)){
            return trim($m[1]);
        }
        return trim($raw);
    }
}
