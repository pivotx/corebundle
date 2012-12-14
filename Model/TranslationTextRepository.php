<?php
namespace PivotX\CoreBundle\Model;
use PivotX\Doctrine\Annotation as PivotX;

class TranslationTextRepository extends \PivotX\Doctrine\Repository\AutoEntityRepository
{
    public function addDefaultViews(\PivotX\Component\Views\Service $service, $prefix)
    {
        $findAll = new \PivotX\Component\Translations\Views\crudFindAll($this, 'Crud/'.$prefix.'/findAll');
        $service->registerView($findAll);

        return parent::addDefaultViews($service, $prefix);
    }

    /**
     * Find the loggabled records
     *
     * @param integer $id   id of the record the search loggabled records for
     * @return array        array of the following associative array
     *                      - id      loggabled id
     *                      - date    logged date
     *                      - data    associative array of stored fields
     *                      - source  actual ActivityLog record
     */
    public function findLoggabled($id)
    {
        $em = $this->getEntityManager();

        $repository = $em->getRepository('PivotX\CoreBundle\Entity\ActivityLog');

        $tag = 'entity_TranslationText_'.$id;
        $activitylogs = $repository->findBy(array('primary_tag'=>$tag), array('date_logged' => 'desc'));

        $results = array();
        foreach($activitylogs as $activitylog) {
            $context = $activitylog->getTechnicalContext();

            $results[] = array(
                'id'     => $activitylog->getId(),
                'date'   => $activitylog->getDateLogged(),
                'data'   => $context['entity'],
                'source' => $activitylog
            );
        }

        return $results;
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
