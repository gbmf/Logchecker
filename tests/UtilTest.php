<?php

namespace OrpheusNET\Logchecker;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
    public function commandExistsDataProvider()
    {
        return [
            ['cd', true],
            ['totallyfakecommandthatdefinitelydoesnotexist', false]
        ];
    }

    /**
     * @dataProvider commandExistsDataProvider
     */
    public function testCommandExists($command, $exists)
    {
        $this->assertSame($exists, Util::commandExists($command));
    }

    public function decodeLogDataProvider()
    {
        $logPath = implode(DIRECTORY_SEPARATOR, [__DIR__, 'logs', 'eac', 'originals']);
        $return = [];
        foreach (new FilesystemIterator($logPath) as $file) {
            $result = explode('_', $file->getFilename());
            $language = $result[0];
            $logName = $result[1];
            if (!file_exists(implode(DIRECTORY_SEPARATOR, [$logPath, '..', 'utf8', $language]))) {
                continue;
            }
            $return[] = [$file->getPathname(), $language, $logName];
        }
        return $return;
    }

    /**
     * @dataProvider decodeLogDataProvider
     */
    public function testDecodeLog($logPath, $language, $logName)
    {
        $testLog = implode(DIRECTORY_SEPARATOR, [__DIR__, 'logs', 'eac', 'utf8', $language, $logName]);
        $this->assertStringEqualsFile($testLog, Util::decodeEncoding(file_get_contents($logPath), $logPath));
    }
}
