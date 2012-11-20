<?php

namespace PivotX\Doctrine\Feature\Loggable;


/**
 * @todo there is still a problem because 2(!) loggable entries get stored
 *       while we only need to first one
 */
class ObjectProperty implements \PivotX\Doctrine\Entity\EntityProperty
{
    private $fields = null;
    private $metaclassdata = null;

    public function __construct(array $fields, $metaclassdata)
    {
        $this->fields        = $fields;
        $this->metaclassdata = $metaclassdata;
    }

    public function getPropertyMethodsForEntity($config)
    {
        $methods = array();

        $methods['setActivityService']     = 'generateSetActivityService';
        $methods['onPxPreUpdate_Loggable'] = 'generateOnPxPreUpdateLoggable';

        return $methods;
    }

    public function getPropertyMethodsForField($field, $config)
    {
        return array();
    }

    public function generateSetActivityService($classname, $config)
    {
        return <<<THEEND
    /**
     * Set the activityservice
     * 
%comment%
     */
    public static function setActivityService(\$service)
    {
        if (property_exists('$classname', 'activity_service')) {
            self::\$activity_service = \$service;
        }
    }
THEEND;
    }

    public function generateOnPxPreUpdateLoggable($classname, $config)
    {
        $fields_to_ignore = array();
        if (isset($config['remove-fields'])) {
            $fields_to_ignore = $config['remove-fields'];
        }

        $fields_to_store = array();
        foreach($this->metaclassdata->fieldMappings as $field => $config) {
            if (isset($config['id']) && ($config['id'] == true)) {
                // ignore ids
                continue;
            }
            if (in_array($field, $fields_to_ignore)) {
                continue;
            }

            $fields_to_store[] = $field;
        }
        $fields_array_code = 'array( "' . implode('","', $fields_to_store) . '" )';

        $parts      = explode('\\', $classname);
        $base_class = end($parts);

        return <<<THEEND
    /**
     * Store a version
     * 
%comment%
     */
    public function onPxPreUpdate_Loggable(\$changeset)
    {
        \$fields = $fields_array_code;

        \$data    = array();
        \$changes = false;
        foreach(\$fields as \$field) {
            \$data[\$field] = \$this->\$field;

            if (isset(\$changeset[\$field])) {
                \$data[\$field] = \$changeset[\$field][0];
                \$changes      = true;
            }
        }

        // @todo not the nicest way, but works for now
        if (isset(\$this->loggable_already_logged)) {
            \$changes = false;
        }
        \$this->loggable_already_logged = true;

        if (\$changes && (property_exists('$classname', 'activity_service')) && (!is_null(self::\$activity_service))) {
            \$log = self::\$activity_service->createLoggableMessage('$base_class', \$this->getId(), \$data);

            return \$log;
        }

        return null;
    }
THEEND;
    }
}

