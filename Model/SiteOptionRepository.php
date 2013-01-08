<?php
namespace PivotX\CoreBundle\Model;
use PivotX\Doctrine\Annotation as PivotX;

class SiteOptionRepository extends \PivotX\Doctrine\Repository\AutoEntityRepository
{

    /**
     * Add generated views
     * 
     * @PivotX\UpdateDate     2013-01-08 16:30:25
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function addGeneratedViews(\PivotX\Component\Views\Service $service, $prefix)
    {

    }
}
