<?php

namespace PivotX\Doctrine\Feature\Timesliceable;


//class ObjectRepository implements \PivotX\Doctrine\Entity\EntityRepository
class ObjectRepository extends \PivotX\Doctrine\Entity\AbstractEntityRepository
{
    private $fields = null;
    private $metaclassdata = null;

    public function __construct(array $fields, $metaclassdata)
    {
        $this->fields        = $fields;
        $this->metaclassdata = $metaclassdata;
    }

    /**
     * Get feature methods independent of field configuration
     */
    public function getPropertyMethodsForEntity($config)
    {
        $methods = array();

        //$methods['find'] = 'generateAddGeneratedViews';
        //echo "Yo\n"; var_dump($config); echo "\n";
        if (!is_array($config) || !isset($config['fields'])) {
            return array();
        }

        foreach($config['fields'] as $field_definition) {
            if (count($field_definition) > 2) {
                list($name, $start_field, $end_field) = array_values($field_definition);
                $cc_start_field = \Doctrine\Common\Util\Inflector::classify($start_field);
                $cc_end_field = \Doctrine\Common\Util\Inflector::classify($end_field);

                //echo 'dates = '.$start_field.'-'.$end_field."\n";

                $methods['findBy'.$name.'BetweenDates'] = array('generateFindByTwoBetweenDates',  $start_field, $end_field, $name);

                // @todo need more version of the two dates!
            }
            else {
                list($name, $field) = array_values($field_definition);

                //echo 'date = '.$field."\n";

                $methods['decodeCriteriaFor'.$name] = array('generateDecodeCriteriaFor',  $field, $name);

                $methods['findLatestBy'.$name] = array('generateFindLatestBy',  $field, $name);

                $methods['findBy'.$name.'BetweenDates'] = array('generateFindByBetweenDates',  $field, $name);

                $methods['findBy'.$name.'OnYear']  = array('generateFindByYear',  $field, $name);
                $methods['findBy'.$name.'OnMonth'] = array('generateFindByMonth', $field, $name);
                // @todo week skipped for now
                //$methods['findBy'.$name.'OnWeek']  = array('generateFindByWeek',  $field);

                $methods['findBy'.$name.'OnRelativeYear']  = array('generateFindByRelativeYear',  $field, $name);
                $methods['findBy'.$name.'OnRelativeMonth'] = array('generateFindByRelativeMonth',  $field, $name);

                // @todo week skipped for now
                //$methods['findBy'.$name.'OnRelativeWeek']  = array('generateFindByRelativeWeek',  $field);


            }
        }

        return $methods;
    }

    /**
     * Get feature methods dependent on field configuration
     */
    public function getPropertyMethodsForField($field, $config)
    {
        return array();
    }

    private function getViewsForFields($name, $singular, $plural)
    {
        $views = array();

        $views[] = $this->buildView(
            'findBy'.$name.'BetweenDates',
            'array(\'date_first\'=>null, \'date_last\'=>null)',
            'Find "'.$plural.'" by "'.$name.'" based on 2 dates',
            array(
                'date_first' => array('First date to search for, this date is inclusive. When unspecified defaults to <strong>no</strong> start date.', '\'2012-07-01\''),
                'date_last' => array('Last date to search for, this date is exclusive. When unspecified defaults to <strong>no</strong> end date.', '\'2012-07-01\''),
            )
        );

        return $views;
    }

    private function getViewsForField($name, $singular, $plural)
    {
        $views = array();

        $views[] = $this->buildView(
            'findLatestBy'.$name,
            'array()',
            'Find latest "'.$plural.'" by "'.$name.'" ',
            array(
            )
        );

        $views[] = $this->buildView(
            'findBy'.$name.'BetweenDates',
            'array(\'date_first\'=>null, \'date_last\'=>null)',
            'Find "'.$plural.'" by "'.$name.'" based on 2 dates',
            array(
                'date_first' => array('First date to search for, this date is inclusive. When unspecified defaults to <strong>no</strong> start date.', '\'2012-07-01\''),
                'date_last' => array('Last date to search for, this date is exclusive. When unspecified defaults to <strong>no</strong> end date.', '\'2012-07-01\''),
            )
        );

        $views[] = $this->buildView(
            'findBy'.$name.'OnYear',
            'array(\'year\'=>null, \'no_of_years\'=>1)',
            'Find "'.$plural.'" by "'.$name.'" based on year',
            array(
                'year' => array('Year to find. When unspecified defaults to <em>current year</em>.', '2012'),
                'no_of_years' => array('Number of years to return. When unspecified defaults to <em>1</em>.', '1'),
            )
        );

        $views[] = $this->buildView(
            'findBy'.$name.'OnMonth',
            'array(\'year\'=>null, \'month\'=>null, \'no_of_months\'=>1)',
            'Find "'.$plural.'" by "'.$name.'" based on year/month',
            array(
                'year' => array('Year to find. When unspecified defaults to <em>current year</em>.', '2012'),
                'month' => array('Month to find. When unspecified defaults to <em>current month</em>.', '1'),
                'no_of_months' => array('Number of months to return. When unspecified defaults to <em>1</em>.', '1'),
            )
        );

        $views[] = $this->buildView(
            'findBy'.$name.'OnRelativeYear',
            'array(\'year\'=>null, \'no_of_years\'=>1)',
            'Find "'.$plural.'" by "'.$name.'" based on year relative to the current year',
            array(
                'year' => array('Year to add or substract to the current year. When unspecified defaults to <em>+1</em> (next year).', '+1'),
                'no_of_years' => array('Number of years to return. When unspecified defaults to <em>1</em>.', '1'),
            )
        );

        $views[] = $this->buildView(
            'findBy'.$name.'OnRelativeMonth',
            'array(\'year\'=>null, \'month\'=>null, \'no_of_months\'=>1)',
            'Find "'.$plural.'" by "'.$name.'" based on year/month relative to the current year/month',
            array(
                'year' => array('Year to add or substract to the current year. When unspecified defaults to <em>+1</em> (next year).', '+1'),
                'month' => array('Month to add or substract to the current month. When unspecified defaults to <em>+1</em> (next month).', '+1'),
                'no_of_months' => array('Number of months to return. When unspecified defaults to <em>1</em>.', '1'),
            )
        );

        return $views;
    }

    /**
     * Get feature views
     */
    public function getViewsForEntity($config)
    {
        if (!is_array($config) || !isset($config['fields'])) {
            return '';
        }

        $class = $this->metaclassdata->name;
        $_p    = explode('\\', $class);
        $singular_entity = strtolower(end($_p));
        $plural_entity   = \PivotX\Component\Translations\Inflector::pluralize($singular_entity);

        $views = array();
        foreach($config['fields'] as $field_definition) {
            if (count($field_definition) > 2) {
                list($name, $start_field, $end_field) = array_values($field_definition);
                $cc_start_field = \Doctrine\Common\Util\Inflector::classify($start_field);
                $cc_end_field = \Doctrine\Common\Util\Inflector::classify($end_field);

                $views = array_merge($views, $this->getViewsForFields($name, $singular_entity, $plural_entity));
            }
            else {
                list($name, $field) = array_values($field_definition);

                $views = array_merge($views, $this->getViewsForField($name, $singular_entity, $plural_entity));
            }
        }

        return $this->generateViewsCode($views);
    }

    public function generateFindByTwoBetweenDates($classname, $config, $start_field, $end_field, $name)
    {
        $method   = 'findBy'.$name.'BetweenDates';

        return <<<THEEND
    /**
     * Find by $name between 2 dates (the former is inclusive, the latter exclusive)
     * 
%comment%
     */
    public function $method(\$date_first = null, \$date_last = null, \$_criteria = null, \$order_by = null, \$limit = null, \$offset = null)
    {
        if (!is_null(\$date_first)) {
            if (is_numeric(\$date_first)) {
                \$date_first = new \DateTime;
                \$date_first->setTimestamp(\$date_first);
            }
            else if (is_string(\$date_first)) {
                \$date_first = new \DateTime(\$date_first);
            }
        }
        if (!is_null(\$date_last)) {
            if (is_numeric(\$date_last)) {
                \$date_last = new \DateTime;
                \$date_last->setTimestamp(\$date_last);
            }
            else if (is_string(\$date_last)) {
                \$date_last = new \DateTime(\$date_last);
            }
        }

        \$criteria = \$this->decodeCriteriaFor$name(\$_criteria, \$order_by, \$limit, \$offset);

        \$builder  = new \Doctrine\Common\Collections\ExpressionBuilder;

        \$first_expression = null;
        if (!is_null(\$date_first)) {
            \$first_expression = \$builder->andX(
                \$builder->gte('$start_field', \$date_first),
                \$builder->lt('$end_field', \$date_first)
            );
        }

        \$last_expression = null;
        if (!is_null(\$date_last)) {
            \$last_expression = \$builder->andX(
                \$builder->gte('$start_field', \$date_last),
                \$builder->lt('$end_field', \$date_last)
            );
        }

        if (is_null(\$first_expression)) {
            \$criteria->andWhere(\$last_expression);
        }
        else if (is_null(\$last_expression)) {
            \$criteria->andWhere(\$first_expression);
        }
        else {
            \$criteria->andWhere(\$builder->orX(
                \$first_expression,
                \$last_expression
            ));
        }

        return \$this->matching(\$criteria);
    }
THEEND;
    }

    public function generateDecodeCriteriaFor($classname, $config, $field, $name)
    {
        $method   = 'decodeCriteriaFor'.$name.'';

        return <<<THEEND
    /**
     * Default build/decode criteria for $name
     *
     * @param mixed \$_criteria    additional criteria
     * @param mixed \$order_by     specified order 
     * @param integer \$limit      limit the number of results
     * @param integer \$offset     first result to return
     * @return object             Criteria object
%comment%
     */
    public function $method(\$_criteria = null, \$order_by = null, \$limit = null, \$offset = null)
    {
        if (is_null(\$_criteria)) {
            \$criteria = new \Doctrine\Common\Collections\Criteria;
        }
        else if (\$_criteria instanceof \Doctrine\Common\Collections\Criteria) {
            \$criteria = \$_criteria;
        }
        else {
            \$criteria = new \Doctrine\Common\Collections\Criteria;

            // @todo do something with \$_criteria
        }

        if (is_array(\$order_by)) {
            \$criteria->orderBy(\$order_by);
        }
        else {
            \$criteria->orderBy(array('$field' => \Doctrine\Common\Collections\Criteria::ASC));
        }

        if (!is_null(\$limit)) {
            \$criteria->setMaxResults(\$limit);
        }
        if (!is_null(\$offset)) {
            \$criteria->setFirstResult(\$offset);
        }

        return \$criteria;
    }
THEEND;
    }

    public function generateFindLatestBy($classname, $config, $field, $name)
    {
        $method   = 'findLatestBy'.$name;

        return <<<THEEND
    /**
     * Find lastest by $name 
%comment%
     */
    public function $method(\$_criteria = null, \$order_by = null, \$limit = null, \$offset = null)
    {
        \$order_by = array(
            '$field' => \Doctrine\Common\Collections\Criteria::DESC
        );

        \$criteria = \$this->decodeCriteriaFor$name(\$_criteria, \$order_by, \$limit, \$offset);

        \$builder  = new \Doctrine\Common\Collections\ExpressionBuilder;

        return \$this->matching(\$criteria);
    }
THEEND;
    }

    public function generateFindByBetweenDates($classname, $config, $field, $name)
    {
        $method   = 'findBy'.$name.'BetweenDates';

        return <<<THEEND
    /**
     * Find by $name between 2 dates (the former is inclusive, the latter exclusive)
%comment%
     */
    public function $method(\$date_first = null, \$date_last = null, \$_criteria = null, \$order_by = null, \$limit = null, \$offset = null)
    {
        if (!is_null(\$date_first)) {
            if (is_numeric(\$date_first)) {
                \$date_first = new \DateTime;
                \$date_first->setTimestamp(\$date_first);
            }
            else if (is_string(\$date_first)) {
                \$date_first = new \DateTime(\$date_first);
            }
        }
        if (!is_null(\$date_last)) {
            if (is_numeric(\$date_last)) {
                \$date_last = new \DateTime;
                \$date_last->setTimestamp(\$date_last);
            }
            else if (is_string(\$date_last)) {
                \$date_last = new \DateTime(\$date_last);
            }
        }

        \$criteria = \$this->decodeCriteriaFor$name(\$_criteria, \$order_by, \$limit, \$offset);

        \$builder  = new \Doctrine\Common\Collections\ExpressionBuilder;

        if (!is_null(\$date_first)) {
            \$criteria->andWhere(\$builder->gte('$field', \$date_first));
        }

        if (!is_null(\$date_last)) {
            \$criteria->andWhere(\$builder->lt('$field', \$date_last));
        }

        return \$this->matching(\$criteria);
    }
THEEND;
    }

    public function generateFindByYear($classname, $config, $field, $name)
    {
        $method       = 'findBy'.$name.'OnYear';
        $parentMethod = 'findBy'.$name.'BetweenDates';

        return <<<THEEND
    /**
     * Find by $name in a specific year
     * 
%comment%
     */
    public function $method(\$year = null, \$no_of_years = 1, \$criteria = null, \$order_by = null, \$limit = null, \$offset = null)
    {
        if (is_null(\$year)) {
            \$year = date('Y');
        }

        \$last_year = \$year + \$no_of_years;

        return \$this->$parentMethod(sprintf('%04-01-01', \$year), sprintf('%04d-01-01', \$last_year), \$criteria, \$order_by, \$limit, \$offset);
    }
THEEND;
    }

    public function generateFindByMonth($classname, $config, $field, $name)
    {
        $method       = 'findBy'.$name.'OnMonth';
        $parentMethod = 'findBy'.$name.'BetweenDates';

        return <<<THEEND
    /**
     * Find by $name in a specific year/month
     * 
%comment%
     */
    public function $method(\$year = null, \$month = null, \$no_of_months = 1, \$criteria = null, \$order_by = null, \$limit = null, \$offset = null)
    {
        if (is_null(\$year)) {
            \$year = date('Y');
        }
        if (is_null(\$month)) {
            \$month = date('m');
        }

        \$first_datetime = new \DateTime(sprintf('%04d-%02d-01',\$year, \$month));
        \$last_datetime = clone \$first_datetime;
        \$interval      = new \DateInterval('P'.\$no_of_months.'M');
        \$last_datetime->add(\$interval);

        return \$this->$parentMethod(\$first_datetime, \$last_datetime, \$criteria, \$order_by, \$limit, \$offset);
    }
THEEND;
    }

    public function generateFindByRelativeYear($classname, $config, $field, $name)
    {
        $method       = 'findBy'.$name.'OnRelativeYear';
        $simpleMethod = 'findBy'.$name.'OnYear';

        return <<<THEEND
    /**
     * Find by $name in a specific year relative to the current year
     * 
%comment%
     */
    public function $method(\$relative_year = null, \$no_of_years = 1, \$criteria = null, \$order_by = null, \$limit = null, \$offset = null)
    {
        \$year = date('Y');
        if (!is_null(\$relative_year)) {
            \$year += \$relative_year;
        }

        return \$this->$simpleMethod(\$year, \$no_of_years, \$criteria, \$order_by, \$limit, \$offset);
    }
THEEND;
    }

    public function generateFindByRelativeMonth($classname, $config, $field, $name)
    {
        $method       = 'findBy'.$name.'OnRelativeMonth';
        $simpleMethod = 'findBy'.$name.'OnMonth';

        return <<<THEEND
    /**
     * Find by $name in a specific year/month relative to the current year/month
     * 
%comment%
     */
    public function $method(\$relative_year = null, \$relative_month = null, \$no_of_months = 1, \$criteria = null, \$order_by = null, \$limit = null, \$offset = null)
    {
        \$year  = date('Y');
        \$month = date('m');
        if (!is_null(\$relative_year)) {
            \$year += \$relative_year;
        }
        if (!is_null(\$relative_month)) {
            \$month += \$relative_month;

            \$year  += floor(\$month / 12);
            \$month  = \$month % 12 + 1;
        }

        return \$this->$simpleMethod(\$year, \$month, \$no_of_months, \$criteria, \$order_by, \$limit, \$offset);
    }
THEEND;
    }
}

