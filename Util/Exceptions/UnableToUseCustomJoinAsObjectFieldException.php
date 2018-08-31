<?php

namespace Ali\DatatableBundle\Util\Exceptions;

/**
 * Class CustomJoinFieldException
 *
 * @author Maarten Sprakel <maarten@extendas.com>
 */
class CustomJoinFieldException extends \Exception
{
    /**
     * CustomJoinFieldException constructor.
     *
     * @param string $prop
     */
    public function __construct($prop)
    {
        $message = sprintf("setFields() contains a field that is joined with a custom join: %s. To solve this use, ex: \$translator->trans('thead.field') => new DQLDatatableField('q.field', 'alias')", $prop);
        parent::__construct($message);
    }
}