<?php

namespace PivotX\Component\Translations\Views;

use \PivotX\Component\Views\AbstractView;

class crudFindAll extends AbstractView
{
    private $repository;

    public function __construct($repository, $name)
    {
        $this->repository        = $repository;
        $this->name              = $name;
        $this->group             = 'PivotX/Backend';
        $this->description       = 'Back-end view for texts';

        $this->long_description = <<<THEEND
This view represents the back-end view for texts.
THEEND;

        $this->arguments    = array();
        $this->range_limit  = null;
        $this->range_offset = null;
    }

    protected function buildQuery()
    {
        $qb = $this->repository->createQueryBuilder('tt');

        if (isset($this->arguments['siteandgroup']) && (strpos($this->arguments['siteandgroup'], '/') !== false)) {
            list($sitename,$groupname) = explode('/', $this->arguments['siteandgroup'], 2);

            $qb->andWhere('tt.sitename = :sitename')->setParameter('sitename', $sitename);
            $qb->andWhere('tt.groupname = :groupname')->setParameter('groupname', $groupname);
        }
        if ((isset($this->arguments['name']) && ($this->arguments['name'] != ''))) {
            $qb->andWhere('tt.name like :name')->setParameter('name', '%'.$this->arguments['name'].'%');
        }

        return $qb;
    }

    public function getResult()
    {
        $builder = $this
            ->buildQuery()
            ;

        $order = false;
        if (isset($this->arguments['order'])) {
            $order = $this->arguments['order'];
        }
        switch ($order) {
            case 'id':
                $builder = $builder->orderBy('tt.id', 'ASC');
                break;
            case 'state':
                $builder = $builder->orderBy('tt.state', 'ASC');
                break;

            default:
                $builder = $builder->orderBy('tt.sitename, tt.groupname, tt.name', 'ASC');
                break;
        }

        $builder = $builder
            ->setFirstResult($this->range_offset)
            ->setMaxResults($this->range_limit)
            ;

        $query = $builder
            ->getQuery()
            ;

        $data = $query->getResult();

        return $data;
    }

    public function getLength()
    {
        $query = $this
            ->buildQuery()
            ->select('count(tt.id)')
            ->getQuery();

        return $query->getSingleScalarResult();
    }
}

