<?php

namespace Ali\DatatableBundle\Util\Exceptions;


class UnableToUseCustomJoinAsObjectFieldException extends \Exception
{

    /**
     * UnableToUseCustomJoinAsObjectFieldException constructor.
     */
    public function __construct($alias, $field)
    {
        $message = sprintf('Non-doctrine joins cannot be used as normal selects, please use a DQLDatatableField for field "%s.%s"',$alias, $field);
        parent::__construct($message);
    }
}