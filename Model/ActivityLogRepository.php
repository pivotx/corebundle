<?php

namespace PivotX\CoreBundle\Model;

class ActivityLogRepository extends \PivotX\Doctrine\Repository\AutoEntityRepository
{
    public function addDefaultViews(\PivotX\Component\Views\Service $service, $prefix)
    {
        $findAll = new \PivotX\Component\Activity\Views\findLatest($this, $prefix.'/findLatest');
        $service->registerView($findAll);

        return parent::addDefaultViews($service, $prefix);
    }

    /**
     * Add generated views
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-12-06, 17:47:18
     */
    public function addGeneratedViews(\PivotX\Component\Views\Service $service, $prefix)
    {

    }
}
