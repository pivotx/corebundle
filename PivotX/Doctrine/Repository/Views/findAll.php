<?php

namespace PivotX\Doctrine\Repository\Views;

use \PivotX\Component\Views\AbstractView;

class findAll extends AbstractView
{
    protected $repository;

    public function __construct($repository,$name)
    {
        list($primary, $other) = explode('/', $name, 2);
        $tags = array ( $primary, 'returnAll' );

        $plural = \PivotX\Component\Translations\Inflector::pluralize(strtolower($tags[0]));

        parent::__construct($name, 'PivotX/Core', 'Find all "'.$plural.'"', $tags);

        $this->long_description = 'Find all "'.$plural.'"';

        $this->repository = $repository;
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

    public function getResult()
    {
        list($criteria, $order_by) = $this->splitArguments();

        $data = $this->repository->findBy($criteria, $order_by, $this->range_limit, $this->range_offset);

        return $data;
    }

    public function getLength()
    {
        list($criteria, $order_by) = $this->splitArguments();

        $data = $this->repository->findBy($criteria);

        return count($data);
    }
}

