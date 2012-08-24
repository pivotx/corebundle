<?php

namespace PivotX\Doctrine\Repository\Views;

use \PivotX\Component\Views\AbstractView;

class findBy extends AbstractView
{
    protected $repository;

    public function __construct($repository,$name)
    {
        list($primary, $other) = explode('/', $name, 2);
        $tags = array ( $primary, 'returnMore' );

        parent::__construct($name, 'PivotX/Core', 'Find filtered "'.$tags[0].'"', $tags);

        $this->repository = $repository;
    }

    public function getResult()
    {
        $data = $this->repository->findBy($this->arguments, null, $this->range_limit, $this->range_offset);

        return $data;
    }

    public function getLength()
    {
        $data = $this->repository->findBy(array());

        return count($data);
    }
}


