<?php


namespace Akeneo\Tool\Component\Connector\Exception;

/**
 * Class BusinessArrayConversionException
 * is used when an exception has to be thrown for action in the UI
 * that is the reason for the internationalisation parameters.
 * @package Akeneo\Tool\Component\Connector\Exception
 */
class BusinessArrayConversionException extends ArrayConversionException
{
    public function __construct($message, private readonly string $messageKey, private readonly array  $messageParameters, \Throwable $previous = null, $code = 0)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getMessageKey()
    {
        return $this->messageKey;
    }

    public function getMessageParameters(): array
    {
        return $this->messageParameters;
    }
}
