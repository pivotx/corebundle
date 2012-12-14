<?php

namespace PivotX\Doctrine\Repository\Views;

use \PivotX\Component\Views\AbstractView;

class findOneBy extends AbstractView
{
    protected $repository;

    public function __construct($repository,$name)
    {
        list($primary, $other) = explode('/', $name, 2);
        $tags = array ( $primary, 'returnOne' );

        parent::__construct($name, 'PivotX/Core', 'Find one specific "'.strtolower($tags[0]).'"', $tags);

        $this->long_description = 'Find one specific "'.strtolower($tags[0]).'"';

        $this->repository = $repository;
    }

    public function getResult()
    {
        $data = $this->repository->findOneBy($this->arguments, null, $this->range_limit, $this->range_offset);

        return $data;
    }

    public function getLength()
    {
        return 1;
    }
}


