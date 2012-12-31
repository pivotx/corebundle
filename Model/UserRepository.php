<?php

namespace PivotX\CoreBundle\Model;
use PivotX\Doctrine\Annotation as PivotX;

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

    /**
     * Add generated views
     * 
     * @PivotX\UpdateDate     2012-12-28 17:22:24
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function addGeneratedViews(\PivotX\Component\Views\Service $service, $prefix)
    {

    }
}
