<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Twig;

/**
 * Twig Iterator
 *
 * @todo this is just the oops iterator, should be improved/stripped/etc/etc
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class HtmlIterator implements \Iterator, \Countable
{
    protected $data = false;
    protected $length = 0;
    protected $pointer = 0;

    protected $it_args = array();

    protected $active_values = array();
    protected $groupingby_active = false;

    /**
     * Our constructor takes an array or an Iterator
     */
    public function __construct($data, $args = false)
    {
        if (is_array($args) && isset($args['groupingby'])) {
            $this->_setup_groupingby($data, $args['groupingby']);
        }
        else {
            $this->_setup_normal($data);
        }
        if (is_array($args)) {
            $this->it_args = $args;
        }
    }

    /**
     */
    protected function getPropertyValueFromData($data, $key)
    {
        $have  = false;
        $value = null;
        if ((is_array($data)) && (isset($data[$key]))) {
            $have  = true;
            $value = $data[$key];
        }
        else if (is_object($data)) {
            $lcKey = ucfirst($key);
            if (($data instanceof ArrayAccess) && isset($data[$key])) {
                $have  = true;
                $value = $data[$key];
            }
            else if (method_exists($data,'get'.$lcKey)) {
                $method= 'get' . $lcKey;
                $have  = true;
                $value = $data->$method();
            }
            else if (method_exists($data,'__call')) {
                $have  = true;
                $value = $data->$key();
            }
        }

        return array($have, $value);
    }

    /**
     * Setup an array or an Iterator
     */
    protected function _setup_normal($data)
    {
        if ($data instanceof \Iterator) {
            $this->data    = array();
            $this->length  = 0;
            $this->pointer = 0;

            foreach($data as $rec) {
                $this->data[] = $rec;
                $this->length++;
            }
        }
        else if (is_array($data)) {
            $this->data    = $data;
            $this->length  = count($data);
            $this->pointer = 0;
        }
        else {
            $this->data    = array ( $data );
            $this->length  = 1;
            $this->pointer = 0;
        }
    }

    /**
     * Set up a numeric grouping by
     */
    protected function _setup_groupingby_numeric($data, $groupingby)
    {
        echo 'setup grouping<br/>'."\n";
        if ($groupingby < 1) {
            $groupingby = count($data);
        }

        $source_length = count($data);
        $no_of_groups  = ceil($source_length / $groupingby);

        $outdata   = array();
        $outdata[] = array('start'=>true, 'end'=>false, 'group_start'=>false, 'group_end'=>false, 'item'=>false, 'value'=>false);

        $counter = 0;
        foreach($data as $rec) {
            if (($counter % $groupingby) == 0) {
                $outdata[] = array('start'=>false, 'end'=>false, 'group_start'=>true, 'group_end'=>false, 'item'=>false, 'value'=>false);
            }
            $outdata[] = array('start'=>false, 'end'=>false, 'group_start'=>false, 'group_end'=>false, 'item'=>true, 'value'=>$rec);
            if (($counter % $groupingby) == ($groupingby-1)) {
                $outdata[] = array('start'=>false, 'end'=>false, 'group_start'=>false, 'group_end'=>true, 'item'=>false, 'value'=>false);
            }

            $counter++;
        }

        if ((count($outdata) > 0) && ($outdata[count($outdata)-1]['group_end'] != true)) {
            $outdata[] = array('start'=>false, 'end'=>false, 'group_start'=>false, 'group_end'=>true, 'item'=>false, 'value'=>false);
        }

        $outdata[] = array('start'=>false, 'end'=>true, 'group_start'=>false, 'group_end'=>false, 'item'=>false, 'value'=>false);

        return $outdata;
    }

    /**
     * Set up a fields grouping by
     */
    protected function _setup_groupingby_fields($data, $groupingby)
    {
        if (strpos($groupingby, ',')) {
            $fields = explode(',', $groupingby);
        }
        else {
            $fields = array($groupingby);
        }

        $source_length = count($data);

        $outdata   = array();
        $outdata[] = array('start'=>true, 'end'=>false, 'group_start'=>false, 'group_end'=>false, 'item'=>false, 'value'=>false);

        $have_previous_key  = false;
        $previous_group_key = null;

        $counter = 0;
        foreach($data as $rec) {
            $group_values = array();
            foreach($fields as $field) {
                list($have, $value) = $this->getPropertyValueFromData($rec, $field);

                $group_values[] = $value;
            }
            $group_key = serialize($group_values);

            if ($group_key != $previous_group_key) {
                if ($have_previous_key) {
                    $outdata[] = array('start'=>false, 'end'=>false, 'group_start'=>false, 'group_end'=>true, 'item'=>false, 'value'=>false);
                }
                $have_previous_key  = true;
                $previous_group_key = $group_key;
                $outdata[] = array('start'=>false, 'end'=>false, 'group_start'=>true, 'group_end'=>false, 'item'=>false, 'value'=>false);
            }
            $outdata[] = array('start'=>false, 'end'=>false, 'group_start'=>false, 'group_end'=>false, 'item'=>true, 'value'=>$rec);

            $counter++;
        }

        if ($have_previous_key) {
            $outdata[] = array('start'=>false, 'end'=>false, 'group_start'=>false, 'group_end'=>true, 'item'=>false, 'value'=>false);
        }

        $outdata[] = array('start'=>false, 'end'=>true, 'group_start'=>false, 'group_end'=>false, 'item'=>false, 'value'=>false);

        return $outdata;
    }

    /**
     * Enhance information in a grouping by result
     * 
     * Add item_first/item_last for the items inside a group.
     */
    protected function _enhance_groupingby($data)
    {
        $len = count($data);
        $len1 = $len - 1;
        for($i=0; $i < $len; $i++) {
            $data[$i]['item_first'] = ($i > 0)     ? ($data[$i-1]['group_start']) : false;
            $data[$i]['item_last']  = ($i < $len1) ? ($data[$i+1]['group_end'])   : false;
        }

        $data[0]['value'] = $data[2]['value'];

        $last_item_index = 0;
        for($idx=1; $idx < $len; $idx++) {
            if ($data[$idx]['group_start']) {
                $last_item_index = $idx + 1;
            }

            if ($data[$idx]['value'] !== false) {
                $last_item_index = $idx;
            }
            else {
                $data[$idx]['value'] = $data[$last_item_index]['value'];
            }
        }

        return $data;
    }

    /**
     * * Setup an array or an Iterator
     */
    protected function _setup_groupingby($data, $groupingby)
    {
        $this->groupingby_active = true;

        if (count($data) > 0) {
            if (is_numeric($groupingby)) {
                $outdata = $this->_setup_groupingby_numeric($data, $groupingby);
            }
            else {
                $outdata = $this->_setup_groupingby_fields($data, $groupingby);
            }

            $outdata = $this->_enhance_groupingby($outdata);
        }
        else {
            // @todo uhm..
            $outdata = $this->data;
        }

        $this->data    = $outdata;
        $this->length  = count($this->data);
        $this->pointer = 0;

        //echo '<pre>'; var_dump($this->data); echo '</pre>';
    }

    /**
     * Set the active record
     * 
     * This method implements a fluent interface.
     */
    public function setActive($key, $value)
    {
        $this->it_args['active_key']     = $key;
        $this->it_args['active_value']   = $value;

        return $this;
    }

    /**
     */
    protected function isCurrentActive()
    {
        if (!isset($this->it_args['active_key'])) {
            return false;
        }

        $key    = $this->it_args['active_key'];
        $values = $this->it_args['active_value'];
        if (!is_array($values)) {
            $values = array($values);
        }

        list($have, $value) = $this->getPropertyValueFromData($this->data[$this->pointer], $key);

        if (!$have) {
            return false;
        }

        if (in_array($value,$values)) {
            return true;
        }
        return false;
    }

    /**
     * Handy stuff!
     */
    public function __get($name) {
        switch ($name) {
            case 'index';
                return $this->pointer;


            case 'first':
                return ($this->pointer == 0);

            case 'last':
                return ($this->pointer+1 >= $this->length);


            case 'odd':
                return ($this->pointer % 2) == 0;

            case 'even':
                return ($this->pointer % 2) == 1;

            case 'length':
            case 'size':
            case 'count':
                return $this->length;

            case 'number':
                if (!isset($this->it_args['offset'])) {
                    return $this->pointer + 1;
                }
                return $this->it_args['offset'] + $this->pointer + 1;

            case 'active':
                return $this->isCurrentActive();

            case 'checked':
                if ($this->isCurrentActive()) {
                    return new \Twig_Markup(' checked="checked"', 'UTF-8');
                }
                return '';

            case 'selected':
                if ($this->isCurrentActive()) {
                    return new \Twig_Markup(' selected="selected"', 'UTF-8');
                }
                return '';

            case 'classes':
                $test    = array ( 'first','last','odd','even','active' );
                $classes = array();
                foreach($test as $t) {
                    if ($this->__get($t)) {
                        $classes[] = $t;
                    }
                }
                return implode(' ',$classes);

            default:
                if (isset($this->it_args[$name])) {
                    return $this->it_args[$name];
                }
                if ($this->groupingby_active) {
                    return $this->data[$this->pointer][$name];
                }
                break;
        }
    }


    /**
     * Iterator stuff
     */

    public function key()
    {
        return $this->pointer;
    }

    public function current()
    {
        if ($this->groupingby_active) {
            return new HtmlIteratorVariable($this, $this->data[$this->pointer]['value']);
        }
        // @todo should we create a htmliteratorvariable, right??
        return new HtmlIteratorVariable($this, $this->data[$this->pointer]);
    }

    public function next()
    {
        if ($this->pointer > $this->length) {
            return false;
        }
        ++$this->pointer;
    }

    public function valid()
    {
        return (($this->pointer >= 0) && ($this->pointer < $this->length));
    }

    public function rewind()
    {
        $this->pointer = 0;
    }

    public function count()
    {
        return $this->length;
    }
}
