<?php namespace Gzero\Base\Services;

class RepositoryValidationException extends RepositoryException {

    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @param string     $message  [optional] The Exception message to throw.
     * @param int        $code     [optional] The Exception code.
     * @param \Exception $previous [optional] The previous exception used for the exception chaining. Since 5.3.0
     *
     * @since 5.1.0
     */
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, ($code) ? $code : 400, $previous);
    }
}