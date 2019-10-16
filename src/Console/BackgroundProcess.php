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

    protected function getOS()
    {
        return strtoupper(PHP_OS);
    }

    public function run(string $sOutputFile = '/dev/null', bool $bAppend = false)
    {
        if ($this->command === null) {
            return null;
        }

        if ($sOS = $this->getOS()) {
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
