<?php
namespace PivotX\Doctrine\Generator;


/**
 * Internal entity representation
 */
class EntityRepresentation
{
    // name of entity
    private $name;

    // class
    private $entity_class = false;

    // roles
    private $roles = array(
        'create' => 'ROLE_EDITOR',
        'read'   => 'ROLE_USER',
        'update' => 'ROLE_EDITOR',
        'delete' => 'ROLE_EDITOR',
    );

    // field definitions
    private $fields = array();

    // manage level for this entity (full, augmented, ignore)
    // full      - we manage the fields and write out the Doctrine configuration
    // augmented - user manages the Doctrine configuration we only augment the configuration
    // ignore    - ignore this entity is in its entirety
    private $managed = 'ignore';

    // state of entity
    // new       - new entity, not yet created
    // normal    - existing entity
    // deleted   - existing entity, but will be deleted
    private $state = 'normal';

    // various doctrine configurations
    private $bundle = false;

    // features
    private $features = array();

    public function __construct($name)
    {
        $this->name    = $name;
        $this->managed = 'ignore';
    }

    public function getInternalName()
    {
        return strtolower($this->name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRole($name)
    {
        if (isset($this->roles[$name])) {
            return $this->roles[$name];
        }
        return ROLE_SUPER_ADMIN;
    }

    public function getEntityClass()
    {
        return $this->entity_class;
    }

    public function getManaged()
    {
        return $this->managed;
    }

    public function setManaged($managed)
    {
        $this->managed = $managed;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state)
    {
        $this->state = $state;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
    }

    public function _findField($name, $create_if_notfound = false)
    {
        for($i=0; $i < count($this->fields); $i++) {
            if ($this->fields[$i]->getName() == $name) {
                return $this->fields[$i];
            }
        }

        if ($create_if_notfound) {
            $field          = new FieldRepresentation($name);
            $this->fields[] = $field;
            return $field; 
        }

        return null;
    }

    public function findOrCreateField($name)
    {
        return $this->_findField($name, true);
    }

    private function importYamlArray($config)
    {
        foreach($config['fields'] as $fieldname => $fieldconfig) {
            $field = $this->findOrCreateField($fieldname);
            $field->importYamlArray($fieldconfig);
        }
    }

    /**
     * Import original yaml configuration if found
     */
    public function importYamlConfig($metadataconfig, $kernel)
    {
        $parts = explode('\\',$metadataconfig->name);
        $base_class = end($parts);

        $path = false;
        try {
            $bundlename = $parts[0] . '' . $parts[1];

            $path = $kernel->locateResource('@'.$bundlename.'/Resources/config/doctrine/'.$base_class.'.orm.yml');
        }
        catch (\InvalidArgumentException $e) {
        }

        if ($path !== false) {
            $yaml = \Symfony\Component\Yaml\Yaml::parse($path);

            list($_class, $config) = each($yaml);

            $this->importYamlArray($config);
        }

        return $path;
    }

    /**
     * Import entity information from the metadata-configuration
     */
    public function importMetaDataConfig($metadataconfig)
    {
        $this->entity_class = $metadataconfig->name;

        foreach($metadataconfig->fieldMappings as $key => $config) {
            $field = $this->findOrCreateField($key);
            $field->importMetaDataConfig($config);

            // @todo remove this
            if ($key == 'date_created') {
                //echo '<pre>'; var_dump($config); echo '</pre>';
            }
        }
    }

    /**
     * Import additional information from the PivotX configuration
     */
    public function importPivotConfig($config)
    {
        if (isset($config['name'])) {
            $this->name = $config['name'];
        }
        if (isset($config['managed'])) {
            $this->managed = $config['managed'];
        }
        if (isset($config['state'])) {
            $this->state = $config['state'];
        }
        if (isset($config['bundle'])) {
            $this->bundle = $config['bundle'];
        }
        if (isset($config['fields'])) {
            $field_order = array();
            foreach($config['fields'] as $name => $fieldconfig) {
                $field_order[] = $name;
                $field = $this->findOrCreateField($name);
                $field->importPivotConfig($fieldconfig);
            }

            $this->reorderFields($field_order);
        }
    }

    /**
     * Export as a YAML array
     *
     * When we manage the entity
     */
    public function exportAsYamlArray()
    {
        $yamla = array();

        return $yamla;
    }

    /**
     * Export additional information from the PivotX configuration
     */
    public function exportPivotConfig()
    {
        $config = array(
            'name' => $this->name,
            'managed' => $this->managed,
            'state' => $this->state,
            'bundle' => $this->bundle,
            'fields' => array(),
            'features' => array()
        );

        foreach($this->fields as $field) {
            $config['fields'][$field->getName()] = $field->exportPivotConfig();
        }

        foreach($this->features as $feature) {
            $config['features'][$feature->getName()] = $feature->exportPivotConfig();
        }

        return $config;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function reorderFields($field_order)
    {
        $old_fields  = $this->fields;
        $new_fields  = array();
        foreach($field_order as $field) {
            $idx = false;
            for($i=0; $i < count($old_fields); $i++) {
                if ($old_fields[$i]->getName() == $field) {
                    $idx = $i;
                    break;
                }
            }
            if ($idx !== false) {
                $new_fields[] = $old_fields[$idx];
                array_splice($old_fields, $idx, 1);
            }
        }

        $this->fields = array_merge($new_fields, $old_fields);
    }

    public function addField($field)
    {
        $this->fields[] = $field;
    }

    public function deleteField($name)
    {
        for($i=0; $i < count($this->fields); $i++) {
            if ($this->fields[$i]->getName() == $name) {
                array_splice($this->fields, $i, 1);
                return true;
            }
        }
        return false;
    }

    public function getFeatures()
    {
        return $this->features;
    }
}
