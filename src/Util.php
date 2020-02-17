<?php

namespace OrpheusNET\Logchecker;

class Util {
    public static function commandExists($cmd) {
        $where = substr(strtolower(PHP_OS), 0, 3) === 'win' ? 'where' : 'command -v';

        exec("{$where} {$cmd} 2>/dev/null", $output, $return_var);
        return $return_var === 0;
    }

    public static function strposArray($haystack, $needles) {
        foreach ($needles as $needle) {
            if (($pos = strpos($haystack, $needle)) !== false) {
                return $pos;
            }
        }
        return false;
    }
}
