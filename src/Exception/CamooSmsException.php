<?php
declare(strict_types=1);

namespace Camoo\Sms\Exception;

use RuntimeException;

/**
 * Class CamooSmsException
 *
 */
class CamooSmsException extends RuntimeException
{

    /**
     * Json encodes the message and calls the parent constructor.
     *
     * @param null           $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = null, int $code = 0, Exception $previous = null)
    {
        parent::__construct(json_encode($message), $code, $previous);
    }
}
