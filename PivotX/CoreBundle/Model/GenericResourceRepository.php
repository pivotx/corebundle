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
     * Default build/decode criteria for Modified
     *
     * @param mixed $_criteria    additional criteria
     * @param mixed $order_by     specified order 
     * @param integer $limit      limit the number of results
     * @param integer $offset     first result to return
     * @return object             Criteria object
     * @PivotX\UpdateDate     2013-01-24 14:09:13
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function decodeCriteriaForModified($_criteria = null, $order_by = null, $limit = null, $offset = null)
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

        if (is_array($order_by)) {
            $criteria->orderBy($order_by);
        }
        else {
            $criteria->orderBy(array('date_modified' => \Doctrine\Common\Collections\Criteria::ASC));
        }

        if (!is_null($limit)) {
            $criteria->setMaxResults($limit);
        }
        if (!is_null($offset)) {
            $criteria->setFirstResult($offset);
        }

        return $criteria;
    }

    /**
     * Find lastest by Modified 
     * @PivotX\UpdateDate     2013-01-24 14:09:13
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function findLatestByModified($_criteria = null, $order_by = null, $limit = null, $offset = null)
    {
        $order_by = array(
            'date_modified' => \Doctrine\Common\Collections\Criteria::DESC
        );

        $criteria = $this->decodeCriteriaForModified($_criteria, $order_by, $limit, $offset);

        $builder  = new \Doctrine\Common\Collections\ExpressionBuilder;

        return $this->matching($criteria);
    }

    /**
     * Find by Modified between 2 dates (the former is inclusive, the latter exclusive)
     * @PivotX\UpdateDate     2013-01-24 14:09:13
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function findByModifiedBetweenDates($date_first = null, $date_last = null, $_criteria = null, $order_by = null, $limit = null, $offset = null)
    {
        if (!is_null($date_first)) {
            if (is_numeric($date_first)) {
                $date_first = new \DateTime;
                $date_first->setTimestamp($date_first);
            }
            else if (is_string($date_first)) {
                $date_first = new \DateTime($date_first);
            }
        }
        if (!is_null($date_last)) {
            if (is_numeric($date_last)) {
                $date_last = new \DateTime;
                $date_last->setTimestamp($date_last);
            }
            else if (is_string($date_last)) {
                $date_last = new \DateTime($date_last);
            }
        }

        $criteria = $this->decodeCriteriaForModified($_criteria, $order_by, $limit, $offset);

        $builder  = new \Doctrine\Common\Collections\ExpressionBuilder;

        if (!is_null($date_first)) {
            $criteria->andWhere($builder->gte('date_modified', $date_first));
        }

        if (!is_null($date_last)) {
            $criteria->andWhere($builder->lt('date_modified', $date_last));
        }

        return $this->matching($criteria);
    }

    /**
     * Find by Modified in a specific year
     * 
     * @PivotX\UpdateDate     2013-01-24 14:09:13
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function findByModifiedOnYear($year = null, $no_of_years = 1, $criteria = null, $order_by = null, $limit = null, $offset = null)
    {
        if (is_null($year)) {
            $year = date('Y');
        }

        $last_year = $year + $no_of_years;

        return $this->findByModifiedBetweenDates(sprintf('%04-01-01', $year), sprintf('%04d-01-01', $last_year), $criteria, $order_by, $limit, $offset);
    }

    /**
     * Find by Modified in a specific year/month
     * 
     * @PivotX\UpdateDate     2013-01-24 14:09:13
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function findByModifiedOnMonth($year = null, $month = null, $no_of_months = 1, $criteria = null, $order_by = null, $limit = null, $offset = null)
    {
        if (is_null($year)) {
            $year = date('Y');
        }
        if (is_null($month)) {
            $month = date('m');
        }

        $first_datetime = new \DateTime(sprintf('%04d-%02d-01',$year, $month));
        $last_datetime = clone $first_datetime;
        $interval      = new \DateInterval('P'.$no_of_months.'M');
        $last_datetime->add($interval);

        return $this->findByModifiedBetweenDates($first_datetime, $last_datetime, $criteria, $order_by, $limit, $offset);
    }

    /**
     * Find by Modified in a specific year relative to the current year
     * 
     * @PivotX\UpdateDate     2013-01-24 14:09:13
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function findByModifiedOnRelativeYear($relative_year = null, $no_of_years = 1, $criteria = null, $order_by = null, $limit = null, $offset = null)
    {
        $year = date('Y');
        if (!is_null($relative_year)) {
            $year += $relative_year;
        }

        return $this->findByModifiedOnYear($year, $no_of_years, $criteria, $order_by, $limit, $offset);
    }

    /**
     * Find by Modified in a specific year/month relative to the current year/month
     * 
     * @PivotX\UpdateDate     2013-01-24 14:09:13
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function findByModifiedOnRelativeMonth($relative_year = null, $relative_month = null, $no_of_months = 1, $criteria = null, $order_by = null, $limit = null, $offset = null)
    {
        $year  = date('Y');
        $month = date('m');
        if (!is_null($relative_year)) {
            $year += $relative_year;
        }
        if (!is_null($relative_month)) {
            $month += $relative_month;

            $year  += floor($month / 12);
            $month  = $month % 12 + 1;
        }

        return $this->findByModifiedOnMonth($year, $month, $no_of_months, $criteria, $order_by, $limit, $offset);
    }

    /**
     * Find all records as used by the Crud
     * 
     * @PivotX\UpdateDate     2013-01-24 14:09:13
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function findCrudAll($_criteria = null, $order_by = null, $limit = null, $offset = null)
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


        if (is_array($order_by)) {
            $criteria->orderBy($order_by);
        }
        else {
			$criteria->orderBy(array (   'date_modified' => \Doctrine\Common\Collections\Criteria::DESC ));
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
     * @PivotX\UpdateDate     2013-01-24 14:09:13
     * @PivotX\AutoUpdateCode code will be updated by PivotX
     * @author                PivotX Generator
     */
    public function addGeneratedViews(\PivotX\Component\Views\Service $service, $prefix)
    {
		$view = new \PivotX\Doctrine\Repository\Views\findTemplate($this, 'findLatestByModified', array(), $prefix.'/findLatestByModified', 'Find latest "genericresources" by "Modified" ', 'PivotX/Core', array($prefix, 'returnMore'));
		$view->setLongDescription("<h4>Description</h4><p>Find latest \"genericresources\" by \"Modified\" .</p>");
		$service->registerView($view);
		$view = new \PivotX\Doctrine\Repository\Views\findTemplate($this, 'findByModifiedBetweenDates', array('date_first'=>null, 'date_last'=>null), $prefix.'/findByModifiedBetweenDates', 'Find "genericresources" by "Modified" based on 2 dates', 'PivotX/Core', array($prefix, 'returnMore'));
		$view->setLongDescription("<h4>Description</h4><p>Find \"genericresources\" by \"Modified\" based on 2 dates.</p><h4>Available arguments</h4><dl><dt>date_first</dt><dd>First date to search for, this date is inclusive. When unspecified defaults to <strong>no</strong> start date.</dd><dd>Arguments example: <code>{ 'date_first': '2012-07-01' }</code></dd><dt>date_last</dt><dd>Last date to search for, this date is exclusive. When unspecified defaults to <strong>no</strong> end date.</dd><dd>Arguments example: <code>{ 'date_last': '2012-07-01' }</code></dd></dl>");
		$service->registerView($view);
		$view = new \PivotX\Doctrine\Repository\Views\findTemplate($this, 'findByModifiedOnYear', array('year'=>null, 'no_of_years'=>1), $prefix.'/findByModifiedOnYear', 'Find "genericresources" by "Modified" based on year', 'PivotX/Core', array($prefix, 'returnMore'));
		$view->setLongDescription("<h4>Description</h4><p>Find \"genericresources\" by \"Modified\" based on year.</p><h4>Available arguments</h4><dl><dt>year</dt><dd>Year to find. When unspecified defaults to <em>current year</em>.</dd><dd>Arguments example: <code>{ 'year': 2012 }</code></dd><dt>no_of_years</dt><dd>Number of years to return. When unspecified defaults to <em>1</em>.</dd><dd>Arguments example: <code>{ 'no_of_years': 1 }</code></dd></dl>");
		$service->registerView($view);
		$view = new \PivotX\Doctrine\Repository\Views\findTemplate($this, 'findByModifiedOnMonth', array('year'=>null, 'month'=>null, 'no_of_months'=>1), $prefix.'/findByModifiedOnMonth', 'Find "genericresources" by "Modified" based on year/month', 'PivotX/Core', array($prefix, 'returnMore'));
		$view->setLongDescription("<h4>Description</h4><p>Find \"genericresources\" by \"Modified\" based on year/month.</p><h4>Available arguments</h4><dl><dt>year</dt><dd>Year to find. When unspecified defaults to <em>current year</em>.</dd><dd>Arguments example: <code>{ 'year': 2012 }</code></dd><dt>month</dt><dd>Month to find. When unspecified defaults to <em>current month</em>.</dd><dd>Arguments example: <code>{ 'month': 1 }</code></dd><dt>no_of_months</dt><dd>Number of months to return. When unspecified defaults to <em>1</em>.</dd><dd>Arguments example: <code>{ 'no_of_months': 1 }</code></dd></dl>");
		$service->registerView($view);
		$view = new \PivotX\Doctrine\Repository\Views\findTemplate($this, 'findByModifiedOnRelativeYear', array('year'=>null, 'no_of_years'=>1), $prefix.'/findByModifiedOnRelativeYear', 'Find "genericresources" by "Modified" based on year relative to the current year', 'PivotX/Core', array($prefix, 'returnMore'));
		$view->setLongDescription("<h4>Description</h4><p>Find \"genericresources\" by \"Modified\" based on year relative to the current year.</p><h4>Available arguments</h4><dl><dt>year</dt><dd>Year to add or substract to the current year. When unspecified defaults to <em>+1</em> (next year).</dd><dd>Arguments example: <code>{ 'year': +1 }</code></dd><dt>no_of_years</dt><dd>Number of years to return. When unspecified defaults to <em>1</em>.</dd><dd>Arguments example: <code>{ 'no_of_years': 1 }</code></dd></dl>");
		$service->registerView($view);
		$view = new \PivotX\Doctrine\Repository\Views\findTemplate($this, 'findByModifiedOnRelativeMonth', array('year'=>null, 'month'=>null, 'no_of_months'=>1), $prefix.'/findByModifiedOnRelativeMonth', 'Find "genericresources" by "Modified" based on year/month relative to the current year/month', 'PivotX/Core', array($prefix, 'returnMore'));
		$view->setLongDescription("<h4>Description</h4><p>Find \"genericresources\" by \"Modified\" based on year/month relative to the current year/month.</p><h4>Available arguments</h4><dl><dt>year</dt><dd>Year to add or substract to the current year. When unspecified defaults to <em>+1</em> (next year).</dd><dd>Arguments example: <code>{ 'year': +1 }</code></dd><dt>month</dt><dd>Month to add or substract to the current month. When unspecified defaults to <em>+1</em> (next month).</dd><dd>Arguments example: <code>{ 'month': +1 }</code></dd><dt>no_of_months</dt><dd>Number of months to return. When unspecified defaults to <em>1</em>.</dd><dd>Arguments example: <code>{ 'no_of_months': 1 }</code></dd></dl>");
		$service->registerView($view);
		$view = new \PivotX\Doctrine\Repository\Views\findTemplate($this, 'findCrudAll', array(), $prefix.'/findCrudAll', 'Find all records for the Crud table', 'PivotX/Core', array($prefix, 'returnMore'));
		$view->setLongDescription("<h4>Description</h4><p>Find all records for the Crud table.</p>");
		$service->registerView($view);

    }
}
