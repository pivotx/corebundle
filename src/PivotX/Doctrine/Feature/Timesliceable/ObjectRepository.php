<?php

namespace PivotX\Doctrine\Feature\Timesliceable;


class ObjectRepository implements \PivotX\Doctrine\Entity\EntityRepository
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
        var_dump($config);
        foreach($config['fields'] as $field_definition) {
            if (count($field_definition) > 1) {
                list($start_field, $end_field) = array_values($field_definition);
                $cc_start_field = \Doctrine\Common\Util\Inflector::classify($start_field);
                $cc_end_field = \Doctrine\Common\Util\Inflector::classify($end_field);

                echo 'dates = '.$start_field.'-'.$end_field."\n";
            }
            else {
                list($field) = array_values($field_definition);
                $cc_field = \Doctrine\Common\Util\Inflector::classify($field);

                echo 'date = '.$field."\n";

                $methods['findBy'.$cc_field.'OnYear']  = array('generateFindByYear',  $field);
/*
                $methods['findBy'.$cc_field.'OnMonth'] = array('generateFindByMonth', $field);
                $methods['findBy'.$cc_field.'OnWeek']  = array('generateFindByWeek',  $field);

                $methods['findBy'.$cc_field.'OnRelativeYear']  = array('generateFindByRelativeYear',  $field);
                $methods['findBy'.$cc_field.'OnRelativeMonth'] = array('generateFindByRelativeMonth', $field);
                $methods['findBy'.$cc_field.'OnRelativeWeek']  = array('generateFindByRelativeWeek',  $field);
*/

                /*

                findByDateCreatedOnYear( $year, $no_years=1)
                findByDateCreatedOnMonth($year, $month,      $no_of_months=1)
                findByDateCreatedOnWeek( $year, $week,       $no_of_weeks=1)

                findByDateCreatedOnRelativeYear( $year=0,           $no_of_years=1)     -1=previous, +1=next year, etc
                findByDateCreatedOnRelativeMonth($year=0, $month=0, $no_of_months=1)    -1=previous, +1=next month, etc
                findByDateCreatedOnRelativeWeek( $year=0, $week=0,  $no_of_weeks=1)     -1=previous, +1=next week, etc

                $first_day_of_week 

                findByDateCreatedOnThisYear( $no_of_years=1)     -1=previous, +1=next week, etc
                findByDateCreatedOnThisMonth($no_of_months=1)    -1=previous, +1=next week, etc
                findByDateCreatedOnThisWeek( $no_of_weeks=1)     -1=previous, +1=next week, etc

                findByDateCreatedOnNextYear( $no_of_years=1)     -1=previous, +1=next week, etc
                findByDateCreatedOnNextMonth($no_of_months=1)    -1=previous, +1=next week, etc
                findByDateCreatedOnNextWeek( $no_of_weeks=1)     -1=previous, +1=next week, etc



                {{ loadView 'Entry/findByDateEventOnRelativeMonth' as agenda with { year: +1, month: 1 } }}

                {{ loadView 'Entry/findByDateEventOnThisWeek' as agenda }}

                {{ loadView 'Entry/findByDateEventOnNextWeek' as agenda with { 'first_day': '4' } }}

                 */
                    
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

    /**
     * Get feature views
     */
    public function getViewsForEntity($config)
    {
        $class = $this->metaclassdata->name;
        $_p    = explode('\\', $class);
        $singular_entity = end($_p);
        $plural_entity   = $singular_entity.'s';

        $views = array();
        foreach($config['fields'] as $field_definition) {
            if (count($field_definition) > 1) {
                list($start_field, $end_field) = array_values($field_definition);
                $cc_start_field = \Doctrine\Common\Util\Inflector::classify($start_field);
                $cc_end_field = \Doctrine\Common\Util\Inflector::classify($end_field);
            }
            else {
                list($field) = array_values($field_definition);
                $cc_field = \Doctrine\Common\Util\Inflector::classify($field);

                $views[] = array(
                    'findBy'.$cc_field.'OnYear',
                    'array(\'year\'=>null, \'no_of_years\'=>1)',
                    'Find "'.$plural_entity.'" based on year'
                );
            }
        }

        $code = '';
        foreach($views as $view) {
            $name = $view[0];
            $args = $view[1];
            $desc = $view[2];

            $code .= "\t\t".'$view = new \\PivotX\\Doctrine\\Repository\\Views\\findTemplate($this, \''.$name.'\', '.$args.', $prefix.\'/'.$name.'\', \''.$desc.'\', \'PivotX/Core\', array($prefix, \'returnMore\'));'."\n";
            $code .= "\t\t".'$service->registerView($view);'."\n";
        }

        return $code;
    }

    public function generateFindByYear($classname, $config, $field)
    {
        $cc_field = \Doctrine\Common\Util\Inflector::classify($field);
        $method   = 'findBy'.$cc_field.'OnYear';

        return <<<THEEND
    /**
     * Find $cc_field in a specific year
     * 
%comment%
     */
    public function $method(\$year = null, \$no_of_years = 1, \$_criteria = null, \$order_by = null, \$limit = null, \$offset = null)
    {
        if (is_null(\$year)) {
            \$year = date('Y');
        }

        // @todo create this:
        // \$criteria = \$this->decodeCriteria(\$_criteria, \$order_by, \$limit, \$offset);

        \$criteria = new \Doctrine\Common\Collections\Criteria;
        \$builder  = new \Doctrine\Common\Collections\ExpressionBuilder;

        if (is_int(\$year)) {
            \$criteria->where(\$builder->gte('date_created', new \DateTime(\$year.'-01-01')));
            \$criteria->andWhere(\$builder->lte('date_created', new \DateTime(\$year.'-12-31')));
        }

        return \$this->matching(\$criteria);
    }
THEEND;
    }
}

