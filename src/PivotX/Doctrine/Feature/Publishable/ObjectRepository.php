<?php

namespace PivotX\Doctrine\Feature\Publishable;


//class ObjectRepository implements \PivotX\Doctrine\Entity\EntityRepository
class ObjectRepository extends \PivotX\Doctrine\Feature\Timesliceable\ObjectRepository
{
    public function __construct(array $fields, $metaclassdata)
    {
        parent::__construct($fields, $metaclassdata);
    }

    /**
     * Get feature methods independent of field configuration
     */
    public function getPropertyMethodsForEntity($config)
    {
        $timesliceable_config = array(
            'fields' => array(
                array(
                    'name' => 'Publication',
                    'field' => 'date_publication'
                )
            )
        );

        return parent::getPropertyMethodsForEntity($timesliceable_config);
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
        // @todo somehow this gets called twice

        $timesliceable_config = array(
            'fields' => array(
                array(
                    'name' => 'Publication',
                    'field' => 'date_publication'
                )
            )
        );

        return parent::getViewsForEntity($timesliceable_config);
    }
}
