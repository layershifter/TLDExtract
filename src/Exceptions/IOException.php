<?php
/**
 * TLDExtract: Domain parser library.
 *
 * @link      https://github.com/layershifter/TLDExtract
 *
 * @copyright Copyright (c) 2016, Alexander Fedyashov
 * @license   https://raw.githubusercontent.com/layershifter/TLDExtract/master/LICENSE MIT License
 */

namespace LayerShifter\TLDExtract\Exceptions;

/**
 * Exception for filesystem errors.
 */
class IOException extends Exception
{
    /**
     * @var null|string Filename
     */
    private $filename;

    /**
     * Constructor of exception.
     *
     * @param string      $message  Message for exception
     * @param int         $code     Error code
     * @param \Exception  $previous Parent exception
     * @param null|string $filename Filename
     */
    public function __construct($message, $code = 0, \Exception $previous = null, $filename = null)
    {
        $this->filename = $filename;
        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets filename that caused error.
     *
     * @return null|string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}
