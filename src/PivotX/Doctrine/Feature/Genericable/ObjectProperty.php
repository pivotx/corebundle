<?php

namespace PivotX\Doctrine\Feature\Genericable;


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

        $methods['getGenericTitle'] = 'generateGenericTitle';

        return $methods;
    }

    public function getPropertyMethodsForField($field, $config)
    {
        $methods = array();

        return $methods;
    }

    public function generateGenericTitle($classname, $config)
    {
        $title_field = false;

        // @todo read actual configuration

        if ($title_field === false) {
            foreach($this->metaclassdata->fieldMappings as $name => $data) {
                if (in_array($name, array('title', 'name', 'email'))) {
                    $title_field = $name;
                    break;
                }
            }
        }

        if ($title_field === false) {
            // @todo
            $title_field = 'id';
        }

        return <<<THEEND
    /**
     * Returns the generic title for this object
     *
%comment%
     */
    public function getGenericTitle()
    {
        return \$this->$title_field;
    }

THEEND;
    }

    public function generateIsPublishedWithState($classname, $config)
    {
        $state_field = false;
        foreach($this->fields as $lfield) {
            if ($lfield[1]['type'] == 'publish_state') {
                $state_field = $lfield[0];
            }
        }

        if ($state_field === false) {
            return '';
        }

        return <<<THEEND
    /**
     * Returns true if entity is published
     *
%comment%
     */
    public function isPublished()
    {
        switch (\$this->$state_field) {
            case 'published':
                return true;
                break;
            case 'timed-depublish':
                return true;
                break;
            case 'depublished':
                return false;
                break;
            case 'timed-publish':
                return false;
                break;
        }
        return false;
    }

THEEND;
    }

    public function generateGetCrudChoices()
    {
        // @todo upgrade this
        $statefield = $this->field_publish_state;
        return <<<THEEND
    /**
     * Return all the CRUD choices
     *
%comment%
     *
     * @return array Array of choices
     */
    public function getCrudChoices_$statefield()
    {
        return array(
            'published',
            'depublished'

            // these two are not options you should be able to select here
            // 'timed-publish',
            // 'timed-depublish'
        );
    }
THEEND;
    }

    public function generateGetCrudConfigurationPublishDate($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * Return the CRUD field configuration
     * 
%comment%
     */
    public function getCrudConfiguration_$field()
    {
        return array(
            'name' => '$field',
            'type' => 'datetime'
        );
    }
THEEND;
    }

    public function generateGetCrudConfigurationPublishState($classname, $field, $config)
    {
        return <<<THEEND
    /**
     * Return the CRUD field configuration
     * 
%comment%
     */
    public function getCrudConfiguration_$field()
    {
        return array(
            'name' => '$field',
            'type' => 'choice',
            'choices' => array(
                'published' => 'Published',
                'timed-depublish' => 'Published (scheduled for depublication)',
                'depublished' => 'Not published',
                'timed-publish' => 'Not published (scheduled for publication)'
            )
        );
    }
THEEND;
    }
}

