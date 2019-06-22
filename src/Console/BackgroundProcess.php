<?php
declare(strict_types=1);
namespace Camoo\Sms\Console;

class BackgroundProcess
{
    private $command = null;
    public function __construct($command = null)
    {
        $this->command  = $command;
    }

    public function run(string $sOutputFile = '/dev/null', bool $bAppend = false) : ?int
    {
        if ($this->command === null) {
            return null;
        }

        if ($sOS = strtoupper(PHP_OS)) {
            if (substr($sOS, 0, 3) === 'WIN') {
                shell_exec(sprintf('%s &', $this->command, $sOutputFile));
                return time();
            } elseif ($sOS === 'LINUX' || $sOS === 'FREEBSD' || $sOS === 'DARWIN') {
                return (int) shell_exec(sprintf('%s %s %s 2>&1 & echo $!', $this->command, ($bAppend) ? '>>' : '>', $sOutputFile));
            }
        }
        return null;
    }
}
