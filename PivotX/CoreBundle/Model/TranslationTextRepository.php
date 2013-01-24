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
     * Find all records as used by the Crud
     * 
     * @PivotX\UpdateDate     2013-01-24 14:20:27
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function findCrudAll($site = null, $_criteria = null, $order_by = null, $limit = null, $offset = null)
    {
        if (is_null($_criteria)) {
            $criteria = new \Doctrine\Common\Collections\Criteria;
        }
        else if ($_criteria instanceof \Doctrine\Common\Collections\Criteria) {
            $criteria = $_criteria;
        }
        else {
            $criteria = new \Doctrine\Common\Collections\Criteria;

            // @todo do something with $_criteria
        }

        $builder  = new \Doctrine\Common\Collections\ExpressionBuilder;

		if (!is_null($site)) {
			$criteria->andWhere($builder->eq('sitename', $site));
		}

        if (is_array($order_by)) {
            $criteria->orderBy($order_by);
        }
        else {
			$criteria->orderBy(array (   'sitename' => \Doctrine\Common\Collections\Criteria::ASC,   'groupname' => \Doctrine\Common\Collections\Criteria::ASC,   'name' => \Doctrine\Common\Collections\Criteria::ASC ));
        }

        if (!is_null($limit)) {
            $criteria->setMaxResults($limit);
        }
        if (!is_null($offset)) {
            $criteria->setFirstResult($offset);
        }

        // CurrentSite only
        /*
        if (is_null($first_expression)) {
            $criteria->andWhere($last_expression);
        }
        */

        return $this->matching($criteria);
    }

    /**
     * Add generated views
     * 
     * @PivotX\UpdateDate     2013-01-24 14:20:27
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function addGeneratedViews(\PivotX\Component\Views\Service $service, $prefix)
    {
		$view = new \PivotX\Doctrine\Repository\Views\findTemplate($this, 'findCrudAll', array('site' => null), $prefix.'/findCrudAll', 'Find all records for the Crud table', 'PivotX/Core', array($prefix, 'returnMore'));
		$view->setLongDescription("<h4>Description</h4><p>Find all records for the Crud table.</p><h4>Available arguments</h4><dl><dt>site</dt><dd>Site to restrict results by. When unspecified defaults to <strong>no restriction</strong>.</dd></dl>");
		$service->registerView($view);

    }
}
