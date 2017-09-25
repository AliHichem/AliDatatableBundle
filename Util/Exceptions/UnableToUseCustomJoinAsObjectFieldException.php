<?php
/**
 * Created by PhpStorm.
 * User: Maarten
 * Date: 25-9-2017
 * Time: 12:16
 */

namespace Ali\DatatableBundle\Util\Exceptions;


class UnableToUseCustomJoinAsObjectFieldException extends \Exception
{

    /**
     * UnableToUseCustomJoinAsObjectFieldException constructor.
     */
    public function __construct($alias, $field)
    {
        $message = sprintf('FQDN joins cannot be used as normal selects, please use a DQLDatatableField for field "%s.%s"',$alias, $field);
        parent::__construct($message);
    }
}