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
    private $config;
    private $kernel;

    public function __construct($config, $kernel)
    {
        $this->config   = $config;
        $this->kernel   = $kernel;
    }


    private function getYamlFilename()
    {
        $parts    = explode('\\', $this->config['bundle']);
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

        return $path.'doctrine/'.$this->config['name'].'.orm.yml';
    }

    private function getEntityPhpFilename()
    {
        $parts    = explode('\\', $this->config['bundle']);
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

        return $path.'Entity/'.$this->config['name'].'.php';
    }

    private function getRepositoryPhpFilename()
    {
        $parts    = explode('\\', $this->config['bundle']);
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

        return $path.'Model/'.$this->config['name'].'Repository.php';
    }

    private function getBundleNamespace()
    {
        $parts = explode('\\', $this->config['bundle']);
        array_pop($parts);
        return implode('\\', $parts);
    }

    private function getEntityClass()
    {
        return $this->getBundleNamespace().'\\Entity\\'.$this->config['name'];
    }

    private function getRepositoryClass()
    {
        return $this->getBundleNamespace().'\\Model\\'.$this->config['name'].'Repository';
    }

    /**
     * Internal call the build the YAML array
     */
    private function getYamlArray()
    {
        $suggestions = new Suggestions();

        $yaml_fields = array();

        foreach($this->config['fields'] as $definition) {
            $id = $definition['name'];

            $field = array(
                'type' => 'integer',
                'length' => null,
                'precision' => 0,
                'scale' => 0,
                'nullable' => false,
                'unique' => false
            );

            $orm = $suggestions->getOrmFieldFromType($definition['type']);
            if (is_array($orm)) {
                $field = array_merge($field, $orm);
            }

            $yaml_fields[$id] = $field;
        }

        $yaml_entity = array(
            'type' => 'entity',
            'table' => strtolower($this->config['name']),
            'repositoryClass' => $this->getRepositoryClass(),
            'fields' => $yaml_fields
        );

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
        $classname = $this->config['name'];

        $properties = '';
        $methods    = '';
        foreach($this->config['fields'] as $definition) {
            $name   = $definition['name'];
            $ucname = str_replace(" ", "", ucwords(strtr($name, "_-", "  ")));
            $type   = 'integer';

            $orm = $suggestions->getOrmFieldFromType($definition['type']);
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
        $classname = $this->config['name'].'Repository';

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
     * Write the actual Yaml configuration
     */
    public function writeYaml()
    {
        $filename = $this->getYamlFilename();
        $yaml     = $this->getYamlArray();

        $content = Yaml::dump($yaml, 6, 2);

        file_put_contents($filename, $content);
        chmod($filename, 0644);

        return true;
    }

    /**
     * Write the entity php
     */
    public function writeEntityPhp()
    {
        $filename = $this->getEntityPhpFilename();
        $code     = $this->getEntityPhp();

        file_put_contents($filename, $code);
        chmod($filename, 0644);

        return true;
    }

    /**
     * Write the repository php
     */
    public function writeRepositoryPhp()
    {
        $filename = $this->getRepositoryPhpFilename();
        $code     = $this->getRepositoryPhp();

        file_put_contents($filename, $code);
        chmod($filename, 0644);

        return true;
    }
}
