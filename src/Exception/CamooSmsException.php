<?php
namespace Camoo\Sms\Exception;

/**
 * Class CamooSmsException
 *
 */
class CamooSmsException extends \Exception
{

    /**
     * Json encodes the message and calls the parent constructor.
     *
     * @param null           $message
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct(json_encode($message), $code, $previous);
    }
}
