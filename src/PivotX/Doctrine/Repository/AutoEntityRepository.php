<?php
/**
 * AutoEntityRepository
 *
 * This class is a basic Doctrine repository with only an added
 * call to add views.
 */

namespace PivotX\Doctrine\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

class AutoEntityRepository extends \Doctrine\ORM\EntityRepository
{
    public function addDefaultViews(\PivotX\Component\Views\Service $service, $prefix)
    {
        $findAll = new Views\findAll($this, $prefix.'/findAll');
        $service->registerView($findAll);

        $find = new Views\find($this, $prefix.'/find');
        $service->registerView($find);

        $findOneBy = new Views\findOneBy($this, $prefix.'/findOneBy');
        $service->registerView($findOneBy);

        $findBy = new Views\findBy($this, $prefix.'/findBy');
        $service->registerView($findBy);

        if (method_exists($this, 'addGeneratedViews')) {
            $this->addGeneratedViews($service, $prefix);
        }
    }
}

