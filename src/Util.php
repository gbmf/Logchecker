<?php

namespace OrpheusNET\Logchecker;

class Util
{
    public static function commandExists(string $cmd)
    {
        $where = substr(strtolower(PHP_OS), 0, 3) === 'win' ? 'where' : 'command -v';

        exec("{$where} {$cmd} 2>/dev/null", $output, $return_var);
        return $return_var === 0;
    }

    public static function decodeEncoding(string $log, string $logPath): string
    {
        try {
            $chardet = new Chardet();
        } catch (\RuntimeException $exc) {
            $chardet = null;
        }
        /** @var Chardet $chardet */

        // Whipper uses UTF-8 so we don't need to bother checking, especially as it's
        // possible a log may be falsely detected as a different encoding by chardet
        if (strpos($log, "Log created by: whipper") !== false) {
            return $log;
        }
        // To parse the log, we want to deal with the log in UTF-8. EAC by default should
        // always output to UTF-16 and XLD to UTF-8, but sometimes people view the log and
        // re-encode them to something else (like Windows-1251), and we need to use chardet
        // to detect this so we can then convert it to UTF-8.
        if (ord($log[0]) . ord($log[1]) == 0xFF . 0xFE) {
            $log = mb_convert_encoding(substr($log, 2), 'UTF-8', 'UTF-16LE');
        } elseif (ord($log[0]) . ord($log[1]) == 0xFE . 0xFF) {
            $log = mb_convert_encoding(substr($log, 2), 'UTF-8', 'UTF-16BE');
        } elseif (ord($log[0]) == 0xEF && ord($log[1]) == 0xBB && ord($log[2]) == 0xBF) {
            $log = substr($log, 3);
        } elseif ($chardet !== null) {
            $results = $chardet->analyze($logPath);
            if ($results['charset'] !== 'utf-8' && $results['confidence'] > 0.7) {
                // $log = mb_convert_encoding($log, 'UTF-8', $results['charset']);
                $log = iconv($results['charset'], 'UTF-8', $log);
            }
        }
        return $log;
    }
}
