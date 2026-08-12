<?php

// ponytail: pail butuh ext-pcntl (tidak ada di PHP Windows), ini tail polling lintas-OS.
$file = __DIR__.'/storage/logs/laravel.log';
$pos = is_file($file) ? max(0, filesize($file) - 4096) : 0;

while (true) {
    clearstatcache(true, $file);
    $size = is_file($file) ? filesize($file) : 0;

    if ($size < $pos) {
        $pos = 0; // log dirotasi / dibersihkan
    }

    if ($size > $pos && ($fh = @fopen($file, 'r'))) {
        fseek($fh, $pos);
        while (($line = fgets($fh)) !== false) {
            echo $line;
        }
        $pos = ftell($fh);
        fclose($fh);
    }

    usleep(500000);
}
