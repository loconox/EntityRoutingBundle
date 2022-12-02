<?php

namespace Loconox\EntityRoutingBundle\Exception;

class SlugServiceNotFoundException extends \RuntimeException
{
    public function __construct($name)
    {
        if (is_object($name)) {
            parent::__construct(sprintf('No service found for the entity %s', get_class($name)));
        }
        else {
            parent::__construct(sprintf('No service found for %s', $name));
        }
    }
}