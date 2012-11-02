<?php
namespace PivotX\CoreBundle\Model;

class TranslationTextRepository extends \PivotX\Doctrine\Repository\AutoEntityRepository
{
    public function addDefaultViews(\PivotX\Component\Views\Service $service, $prefix)
    {
        $findAll = new \PivotX\Component\Translations\Views\crudFindAll($this, 'Crud/'.$prefix.'/findAll');
        $service->registerView($findAll);

        return parent::addDefaultViews($service, $prefix);
    }
}
