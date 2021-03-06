<?php

namespace PivotX\CoreBundle\Model;
use PivotX\Doctrine\Annotation as PivotX;

class ActivityLogRepository extends \PivotX\Doctrine\Repository\AutoEntityRepository
{
    public function addDefaultViews(\PivotX\Component\Views\Service $service, $prefix)
    {
        $repository = $this;

        $view = new \PivotX\Component\Views\ViewProxy($prefix.'/findLatest', function() use ($repository,$prefix) {
            return new \PivotX\Component\Activity\Views\findLatest($repository, $prefix.'/findLatest');
        });
        $service->registerView($view);

        return parent::addDefaultViews($service, $prefix);
    }

    /**
     * Find all records as used by the Crud
     * 
     * @PivotX\UpdateDate     2013-02-08 14:20:45
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

		if ((!is_null($site)) && ($site != '')) {
			$criteria->andWhere($builder->eq('sitename', $site));
		}

        if (is_array($order_by)) {
            $criteria->orderBy($order_by);
        }
        else {
			$criteria->orderBy(array (   'date_logged' => \Doctrine\Common\Collections\Criteria::DESC,   'id' => \Doctrine\Common\Collections\Criteria::DESC ));
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
     * @PivotX\UpdateDate     2013-02-08 14:20:45
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function addGeneratedViews(\PivotX\Component\Views\Service $service, $prefix)
    {
		$repository = $this;

        $view = new \PivotX\Component\Views\ViewProxy($prefix.'/findCrudAll', function() use ($prefix, $repository) {
            $pview = new \PivotX\Doctrine\Repository\Views\findTemplate($repository, 'findCrudAll', array('site' => null), $prefix.'/findCrudAll', 'Find all records for the Crud table', 'PivotX/Core', array($prefix, 'returnMore'));
            $pview->setLongDescription("<h4>Description</h4><p>Find all records for the Crud table.</p><h4>Available arguments</h4><dl><dt>site</dt><dd>Site to restrict results by. When unspecified defaults to <strong>no restriction</strong>.</dd></dl>");
            return $pview;
        });
        $service->registerView($view);

    }
}
