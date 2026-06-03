<?php

namespace App\Support;

use Illuminate\Filesystem\Filesystem;

class WindowsSafeFilesystem extends Filesystem
{
    public function replace($path, $content, $mode = null)
    {
        clearstatcache(true, $path);

        $directory = dirname($path);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($path, $content, LOCK_EX);

        if ($mode !== null) {
            @chmod($path, $mode);
        }
    }
}
