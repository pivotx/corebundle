<?php

namespace PivotX\CoreBundle\Model;

class UserRepository extends \PivotX\Doctrine\Repository\AutoEntityRepository
{
    public function addDefaultViews(\PivotX\Component\Views\Service $service, $prefix)
    {
        /*
        $findAll = new \PivotX\Component\Activity\Views\findLatest($this, $prefix.'/findLatest');
        $service->registerView($findAll);
         */

        return parent::addDefaultViews($service, $prefix);
    }
}
