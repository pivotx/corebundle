<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Twig;

/**
 * Twig Iterator Variable
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class HtmlIteratorVariable implements \ArrayAccess
{
    protected $_iterator = null;
    protected $_value = null;

    public function __construct(HtmlIterator $iterator, $value)
    {
        $this->_iterator = $iterator;
        $this->_value    = $value;
    }

    public function __get($name)
    {
        if ($name == 'value') {
            return $this->_value;
        }
        return $this->_iterator->__get($name);
    }

    public function offsetGet($offset)
    {
        if ($offset == 'value') {
            return $this->_value;
        }
        return $this->_iterator->__get($offset);
    }

    public function offsetExists($offset)
    {
        return true;
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}
