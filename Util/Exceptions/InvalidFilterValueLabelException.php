<?php

namespace Ali\DatatableBundle\Util\Exceptions;


class InvalidFilterValueLabelException extends \Exception
{

    /**
     * InvalidFilterValueLabelException constructor.
     */
    public function __construct($label)
    {
        $message = sprintf('Invalid label for filter value, string expected but got type "%s" of class "%s"', gettype($label), get_class($label));
        parent::__construct($message);
    }
}