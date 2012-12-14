<?php

namespace PivotX\Doctrine\Feature\Publishable;


class ObjectProperty implements \PivotX\Doctrine\Entity\EntityProperty
{
    private $fields = null;
    private $metaclassdata = null;

    private $field_publish_date = 'date_publication';
    private $field_depublish_date = 'date_depublication';
    private $field_publish_state = 'publish_state';

    public function __construct(array $fields, $metaclassdata)
    {
        $this->fields        = $fields;
        $this->metaclassdata = $metaclassdata;
    }

    public function getPropertyMethodsForEntity($config)
    {
        $methods = array();

        // @todo not called atm

        $have_state = false;
        foreach($this->fields as $lfield) {
            if ($lfield[1]['type'] == 'publish_state') {
                $have_state = true;
            }
        }
        if ($have_state) {
            $methods['isPublished'] = 'generateIsPublishedWithState';
        }
        else {
            $methods['isPublished'] = 'generateIsPublishedWithoutState';
        }

        return $methods;
    }

    public function getPropertyMethodsForField($field, $config)
    {
        $methods = array();

        //$methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfiguration';

        foreach($this->fields as $lfield) {
            if ($lfield[0] == $field) {
                switch ($lfield[1]['type']) {
                    case 'publish_date':
                        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfigurationPublishDate';
                        break;
                    case 'depublish_date':
                        break;
                    case 'publish_state':
                        break;
                }
            }
        }

        return $methods;
    }

    public function generateIsPublishedWithoutState($classname, $config)
    {
        return <<<THEEND
    /**
     * Returns true if entity is published
     */
    public function isPublished()
    {
        return true;
    }

THEEND;
    }

    public function generateIsPublishedWithState($classname, $config)
    {
        // @todo search for the proper field
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
}

