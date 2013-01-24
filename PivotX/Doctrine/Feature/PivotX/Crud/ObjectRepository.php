<?php

namespace PivotX\Doctrine\Feature\PivotX\Crud;


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

        $methods['findCrudAll'] = 'generateFindCrudAll';

        return $methods;
    }

    /**
     * Get feature methods dependent on field configuration
     */
    public function getPropertyMethodsForField($field, $config)
    {
        return array();
    }

    private function getViews($config)
    {
        $views = array();

        if (isset($config['site_only']) && ($config['site_only'] == true)) {
            $views[] = $this->buildView(
                'findCrudAll',
                'array(\'site\' => null)',
                'Find all records for the Crud table',
                array(
                    'site' => 'Site to restrict results by. When unspecified defaults to <strong>no restriction</strong>.'
                )
            );
        }
        else {
            $views[] = $this->buildView(
                'findCrudAll',
                'array()',
                'Find all records for the Crud table',
                array(
                )
            );
        }

        return $views;
    }

    /**
     * Get feature views
     */
    public function getViewsForEntity($config)
    {
        if (!is_array($config) || !isset($config['fields'])) {
            //return '';
        }

        $class = $this->metaclassdata->name;
        $_p    = explode('\\', $class);
        $singular_entity = strtolower(end($_p));
        $plural_entity   = \PivotX\Component\Translations\Inflector::pluralize($singular_entity);

        $views = array();
        $views = array_merge($views, $this->getViews($config));

        return $this->generateViewsCode($views);
    }

    public function generateFindCrudAll($classname, $config)
    {
        $method   = 'findCrudAll';

        $default_order_by = '';
        if (isset($config['order_by'])) {
            $orderBy = array();
            foreach($config['order_by'] as $field => $dir) {
                $dir = strtoupper($dir);

                $orderBy[$field] = '\Doctrine\Common\Collections\Criteria::'.$dir;
            }

            $codeOrderBy = var_export($orderBy, true);
            $codeOrderBy = str_replace("\n", ' ', $codeOrderBy);
            $codeOrderBy = str_replace(' => \'', ' => ', $codeOrderBy);
            $codeOrderBy = str_replace('\', ', ', ', $codeOrderBy);
            $codeOrderBy = str_replace(', )', ' )', $codeOrderBy);
            $codeOrderBy = str_replace('\\\\', '\\', $codeOrderBy);

            $default_order_by = "\t\t\t".'$criteria->orderBy('.trim($codeOrderBy).');';
        }

        $add_args   = '';
        $and_wheres = '';
        if (isset($config['site_only']) && ($config['site_only'] == true)) {
            $add_args    = '$site = null, ';
            $and_wheres .= "\t\tif (!is_null(\$site)) {\n";
            $and_wheres .= "\t\t\t" . '$criteria->andWhere($builder->eq(\'sitename\', $site));' . "\n";
            $and_wheres .= "\t\t}\n";
        }

        return <<<THEEND
    /**
     * Find all records as used by the Crud
     * 
%comment%
     */
    public function $method($add_args\$_criteria = null, \$order_by = null, \$limit = null, \$offset = null)
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

        \$builder  = new \Doctrine\Common\Collections\ExpressionBuilder;

$and_wheres
        if (is_array(\$order_by)) {
            \$criteria->orderBy(\$order_by);
        }
        else {
$default_order_by
        }

        if (!is_null(\$limit)) {
            \$criteria->setMaxResults(\$limit);
        }
        if (!is_null(\$offset)) {
            \$criteria->setFirstResult(\$offset);
        }

        // CurrentSite only
        /*
        if (is_null(\$first_expression)) {
            \$criteria->andWhere(\$last_expression);
        }
        */

        return \$this->matching(\$criteria);
    }
THEEND;
    }
}

