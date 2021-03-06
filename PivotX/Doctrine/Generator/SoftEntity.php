<?php
namespace PivotX\Doctrine\Generator;


use Symfony\Component\Yaml\Yaml;


/**
 * This is an entity-generator for a JSON defined entity.
 *
 * This class generates a new YAML file.
 */
class SoftEntity
{
    private $entity;
    private $kernel;

    public function __construct($entity, $kernel)
    {
        $this->entity   = $entity;
        $this->kernel   = $kernel;
    }


    private function getYamlFilename()
    {
        $parts    = explode('\\', $this->entity->getBundle());
        $basename = end($parts);

        $path = false;
        try {
            $path = $this->kernel->locateResource('@'.$basename.'/Resources/config/');
        }
        catch (InvalidArgumentException $e) {
            // don't show anything
        }

        if ($path === false) {
            // cannot find config directory
            return null;
        }

        if (!is_dir($path.'doctrine/')) {
            @mkdir($path.'doctrine', 0755);
            @chmod($path, 0755);
        }
        if (!is_dir($path.'doctrine/')) {
            // cannot create directory doctrine/
            return null;
        }

        return $path.'doctrine/'.$this->entity->getName().'.orm.yml';
    }

    private function getEntityPhpFilename()
    {
        $parts    = explode('\\', $this->entity->getBundle());
        $basename = end($parts);

        $path = false;
        try {
            $path = $this->kernel->locateResource('@'.$basename.'/');
        }
        catch (InvalidArgumentException $e) {
            // don't show anything
        }

        if ($path === false) {
            // cannot find config directory
            return null;
        }

        if (!is_dir($path.'Entity/')) {
            @mkdir($path.'Entity', 0755);
            @chmod($path, 0755);
        }
        if (!is_dir($path.'Entity/')) {
            // cannot create directory doctrine/
            return null;
        }

        return $path.'Entity/'.$this->entity->getName().'.php';
    }

    private function getRepositoryPhpFilename()
    {
        $parts    = explode('\\', $this->entity->getBundle());
        $basename = end($parts);

        $path = false;
        try {
            $path = $this->kernel->locateResource('@'.$basename.'/');
        }
        catch (InvalidArgumentException $e) {
            // don't show anything
        }

        if ($path === false) {
            // cannot find config directory
            return null;
        }

        if (!is_dir($path.'Model/')) {
            @mkdir($path.'Model', 0755);
            @chmod($path, 0755);
        }
        if (!is_dir($path.'Entity/')) {
            // cannot create directory doctrine/
            return null;
        }

        return $path.'Model/'.$this->entity->getName().'Repository.php';
    }

    private function getBundleNamespace()
    {
        $parts = explode('\\', $this->entity->getBundle());
        array_pop($parts);
        return implode('\\', $parts);
    }

    private function getEntityClass()
    {
        return $this->getBundleNamespace().'\\Entity\\'.$this->entity->getName();
    }

    private function getRepositoryClass()
    {
        return $this->getBundleNamespace().'\\Model\\'.$this->entity->getName().'Repository';
    }

    /**
     * Get the feature generator class
     * 
     * @todo should do a proper lookup
     */
    public function getFeatureSoftClass($feature)
    {
        $classpath = ucfirst($feature);
        if (substr($feature, 0, 7) == 'pivotx_') {
            $classpath = 'PivotX\\'.ucfirst(substr($feature, 7));
        }

        $class = '\\PivotX\\Doctrine\\Feature\\'.$classpath.'\\SoftProperty';
        if (class_exists($class)) {
            return $class;
        }

        return null;
    }

    /**
     * Internal call the build the YAML array
     */
    private function getYamlArray()
    {
        $suggestions = new Suggestions();

        $yaml_fields   = array();
        $yaml_features = array();
        $yaml_relation = array();

        $field_definitions = $this->entity->getFields();
        foreach($field_definitions as $field_definition) {
            $id = $field_definition->getName();

            $field = array(
                'type' => 'integer',
                'length' => null,
                'precision' => 0,
                'scale' => 0,
                'nullable' => false,
                'unique' => false
            );

            $orm = $suggestions->getOrmFieldFromType($field_definition->getPivotXType(), $field_definition);
            if (is_array($orm)) {
                $field = array_merge($field, $orm);
            }

            if (isset($orm['auto_entity'])) {
                list($name,$_value) = each($orm['auto_entity']);
                $soft_class = $this->getFeatureSoftClass($name);

                if (!is_null($soft_class)) {
                    // @todo here
                    $soft = new $soft_class($this->entity);

                    $field = $soft->modifyOrmField($field, $field_definition);
                }
            }

            $relation = $suggestions->getRelationFromDefinition($field_definition);

            if (!is_null($relation)) {
                $id   = $relation['id'];
                $type = $relation['type'];
                unset($relation['id']);
                unset($relation['type']);

                if (!isset($yaml_relation[$type])) {
                    $yraml_relation[$type] = array();
                }
                $yaml_relation[$type][$id] = $relation;
            }
            else {
                $yaml_fields[$id] = $field;
            }
        }

        $features = $this->entity->getFeatures();
        foreach($features as $definition) {
            $id = $definition['type'];

            $feature = $definition['orm']['auto_entity'][$id];

            $yaml_features[$id] = $feature;
        }

        $yaml_entity = array(
            'type' => 'entity',
            'table' => strtolower($this->entity->getName()),
            'repositoryClass' => $this->getRepositoryClass(),
            'fields' => $yaml_fields,
            'auto_entity' => $yaml_features
        );

        $yaml_entity = array_merge($yaml_entity, $yaml_relation);

        $entity_class = $this->getEntityClass();
        $yaml = array(
            $entity_class => $yaml_entity
        );

        return $yaml;
    }

    /**
     * Internal call to write an initial entity PHP
     */
    private function getEntityPhp()
    {
        $suggestions = new Suggestions();

        $namespace = $this->getBundleNamespace();
        $classname = $this->entity->getName();

        $properties = '';
        $methods    = '';
        $field_definitions = $this->entity->getFields();
        foreach($field_definitions as $field_definition) {

            $name   = $field_definition->getName();
            $ucname = str_replace(" ", "", ucwords(strtr($name, "_-", "  ")));
            $type   = 'integer';

            $orm = $suggestions->getOrmFieldFromType($field_definition->getPivotXType(), $field_definition);
            if (is_array($orm) && isset($orm['type'])) {
                $type = $orm['type'];
            }

            $properties .= <<<THEPROPERTY
    /**
     * @var $type \$$name
     */
    private \$$name;


THEPROPERTY;

            $methods .= <<<THEMETHODS
    /**
     * Set $name
     *
     * @param $type \$$name
     */
    function set$ucname(\$$name)
    {
        \$this->$name = \$$name;
    }

    /**
     * Get $name
     *
     * @return $type
     */
    function get$ucname()
    {
        return \$this->$name;
    }


THEMETHODS;
        }


        $properties = rtrim($properties);
        $methods    = rtrim($methods);

        $code = <<<THEEND
<?php
namespace $namespace\\Entity;

class $classname
{
$properties

$methods
}
THEEND;

        return $code;
    }

    /**
     * Internal call to write an initial repository PHP
     */
    private function getRepositoryPhp()
    {
        $suggestions = new Suggestions();

        $namespace = $this->getBundleNamespace();
        $classname = $this->entity->getName().'Repository';

        $code = <<<THEEND
<?php
namespace $namespace\\Model;

class $classname extends \PivotX\Doctrine\Repository\AutoEntityRepository
{
}

THEEND;

        return $code;
    }

    /**
     * Remove the actual Yaml configuration
     *
     * @return boolean return true if the configuration changed
     */
    public function deleteYaml()
    {
        $filename = $this->getYamlFilename();

        if (file_exists($filename) && unlink($filename)) {
            return true;
        }

        return false;
    }

    /**
     * Write the actual Yaml configuration
     *
     * @return boolean return true if the configuration changed
     */
    public function writeYaml()
    {
        $filename = $this->getYamlFilename();
        $yaml     = $this->getYamlArray();

        $content = Yaml::dump($yaml, 6, 2);

        $old_content = false;
        if (file_exists($filename)) {
            $old_content = file_get_contents($filename);
        }

        if (($old_content === false) || ($content != $old_content)) {
            file_put_contents($filename, $content);
            chmod($filename, 0644);

            return true;
        }

        return false;
    }

    /**
     * Write the entity php
     */
    public function writeEntityPhp($overwrite = false)
    {
        $filename = $this->getEntityPhpFilename();
        $code     = $this->getEntityPhp();

        if ((!file_exists($filename)) || $overwrite) {
            file_put_contents($filename, $code);
            chmod($filename, 0644);
        }

        return true;
    }

    /**
     * Write the repository php
     */
    public function writeRepositoryPhp($overwrite = false)
    {
        $filename = $this->getRepositoryPhpFilename();
        $code     = $this->getRepositoryPhp();

        if ((!file_exists($filename)) || $overwrite) {
            file_put_contents($filename, $code);
            chmod($filename, 0644);
        }

        return true;
    }

    /**
     * Mark changes
     */
    public function markChanges()
    {
        $fields = $this->entity->getFields();
        foreach($fields as &$field) {
            $field->setState('normal');
        }

        return true;
    }

    /**
     * Return the updated configuration
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
