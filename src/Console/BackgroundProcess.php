<?php
declare(strict_types=1);

namespace Camoo\Sms\Console;

use Camoo\Sms\Exception\BackgroundProcessException;

class BackgroundProcess
{
    /** @var null|string $command */
    private $command = null;

    public function __construct($command = null)
    {
        $this->command  = $command;
    }

    protected function getOS()
    {
        return strtoupper(PHP_OS);
    }

    protected function getCommand()
    {
        if (null !== $this->command) {
            return escapeshellcmd($this->command);
        }
        return null;
    }

    public function run($sOutputFile = '/dev/null', $bAppend = false)
    {
        if ($this->getCommand() === null) {
            throw new BackgroundProcessException('Command is missing');
        }

        $sOS = $this->getOS();

        if (empty($sOS)) {
            throw new BackgroundProcessException('Operating System cannot be determined');
        }

        if (substr($sOS, 0, 3) === 'WIN') {
            shell_exec(sprintf('%s &', $this->getCommand(), $sOutputFile));
            return 0;
        } elseif ($sOS === 'LINUX' || $sOS === 'FREEBSD' || $sOS === 'DARWIN') {
            return (int) shell_exec(sprintf('%s %s %s 2>&1 & echo $!', $this->getCommand(), ($bAppend) ? '>>' : '>', $sOutputFile));
        }

        throw new BackgroundProcessException('Operating System not Supported');
    }
}
