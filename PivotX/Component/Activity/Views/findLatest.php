<?php

namespace PivotX\Component\Activity\Views;

use \PivotX\Component\Views\AbstractView;

class findLatest extends AbstractView
{
    protected $repository;

    public function __construct($repository,$name)
    {
        $this->repository = $repository;
        $this->name       = $name;
        $this->group      = 'PivotX/Core';

        $this->range_limit = 7;
    }

    protected function buildQuery()
    {
        $qb = $this->repository->createQueryBuilder('al');

        if (isset($this->arguments['level'])) {
            $qb->andWhere('al.level = :level');

            $int_level = \PivotX\Component\Activity\Service::LEVEL_SITE;
            switch ($this->arguments['level']) {
                default:
                    $int_level = intval($this->arguments['level']);
                    break;
            }

            $qb->setParameters(array('level' => $int_level));
        }

        return $qb;
    }

    public function getResult()
    {
        $query = $this
            ->buildQuery()
            ->orderBy('al.date_logged', 'DESC')
            ->setFirstResult($this->range_offset)
            ->setMaxResults($this->range_limit)
            ->getQuery();

        $data = $query->getResult();
        
        return $data;
    }

    public function getLength()
    {
        $query = $this
            ->buildQuery()
            ->select('count(al.id)')
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}


