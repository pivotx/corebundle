<?php

namespace PivotX\Doctrine\Entity;

interface EntityRepository
{

    /**
     * Get feature methods independent of field configuration
     */
    public function getPropertyMethodsForEntity($config);

    /**
     * Get feature methods dependent on field configuration
     */
    public function getPropertyMethodsForField($field, $config);

    /**
     * Get feature views
     *
     * We may assume that addGeneratedViews is called with
     * with the 'pivotx.views' service and the view-prefix
     * string.
     *
     * @param  array   feature configuration
     * @return string  code to add to addGeneratedViews
     */
    public function getViewsForEntity($config);
}
