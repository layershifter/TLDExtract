<?php
/**
 * IOException.php
 *
 * @author Alexander Fedyashov <af@e42.guru>
 */

namespace LayerShifter\TLDExtract\Exceptions;


class IOException extends \Exception
{
    private $filename;

    public function __construct($message, $code = 0, \Exception $previous = null, $filename = null)
    {
        $this->filename = $filename;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }
}