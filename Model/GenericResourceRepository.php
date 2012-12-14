<?php

namespace PivotX\CoreBundle\Model;
use PivotX\Doctrine\Annotation as PivotX;

class GenericResourceRepository extends \PivotX\Doctrine\Repository\AutoEntityRepository
{
    public function addDefaultViews(\PivotX\Component\Views\Service $service, $prefix)
    {
        return parent::addDefaultViews($service, $prefix);
    }

    /**
     * Add generated views
     * 
     * @PivotX\UpdateDate     2012-12-14 16:05:55
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function addGeneratedViews(\PivotX\Component\Views\Service $service, $prefix)
    {

    }
}
