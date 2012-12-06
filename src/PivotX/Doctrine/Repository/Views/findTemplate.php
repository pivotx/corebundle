<?php

namespace PivotX\Doctrine\Repository\Views;

use \PivotX\Component\Views\AbstractView;

class findTemplate extends AbstractView
{
    protected $repository;
    protected $method;
    protected $method_arguments;

    public function __construct($repository, $method, $arguments, $name, $description, $group, $tags)
    {
        parent::__construct($name, $group, $description, $tags);

        $this->repository       = $repository;
        $this->method           = $method;
        $this->method_arguments = $arguments;
    }

    private function splitArguments()
    {
        $criteria = array();
        $order_by = null;
        foreach($this->arguments as $k => $v) {
            if (in_array($k, array('orderBy'))) {
                $order_by = $v;
            }
            else {
                $criteria[$k] = $v;
            }
        }
        return array($criteria, $order_by);
    }

    private function executeQuery($limit = null, $offset = null)
    {
        list($criteria, $order_by) = $this->splitArguments();

        $array = array();

        foreach($this->method_arguments as $k => $v) {
            $array[] = $v;
        }

        $array[] = $criteria;
        $array[] = $order_by;
        $array[] = $limit;
        $array[] = $offset;

        return call_user_func_array(array($this->repository, $this->method), $array);
    }

    public function getResult()
    {
        return $this->executeQuery($this->range_limit, $this->range_offset);
    }

    public function getLength()
    {
        $data = $this->executeQuery();

        return count($data);
    }
}


