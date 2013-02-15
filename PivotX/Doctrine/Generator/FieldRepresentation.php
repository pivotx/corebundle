<?php
namespace PivotX\Doctrine\Generator;


/**
 * Internal field representation
 */
class FieldRepresentation
{
    // name of field
    private $name;

    // regular field or relational field (normal, onetoone, manytoone, onetomany, manytomany)
    private $fieldgroup;

    // various doctrine settings
    private $type = false;
    private $nullable = false;
    private $unique = false;
    private $targetEntity = false;

    // various PivotX settings
    private $pivotx_type = '';
    private $state = 'normal';
    private $in_crud = true;
    private $description = '';
    private $arguments = '';
    private $settings = array();

    // live-variables (not saved)
    private $show_buttons = false;

    public function __construct($name)
    {
        $this->name       = $name;
        $this->fieldgroup = 'normal';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setState($state = 'normal')
    {
        $this->state = $state;
    }

    public function isInCrud()
    {
        return $this->in_crud;
    }

    public function setInCrud($in_crud)
    {
        $this->in_crud = $in_crud;
    }

    public function getPivotXType()
    {
        return $this->pivotx_type;
    }

    public function setPivotXType($type)
    {
        $this->pivotx_type = $type;
    }

    public function getTargetEntity()
    {
        return $this->targetEntity;
    }

    public function setTargetEntity($te)
    {
        $this->targetEntity = $te;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function hasArguments()
    {
        return ($this->arguments != '');
    }

    public function setArguments($args)
    {
        $this->arguments = $args;
    }

    public function getShowButtons()
    {
        return $this->show_buttons;
    }

    public function setShowButtons($show_buttons)
    {
        $this->show_buttons = $show_buttons;
    }

    /**
     * Import entity information from the metadata-configuration
     */
    public function importMetaDataConfig($metadataconfig)
    {
        $this->type     = $metadataconfig['type'];
        $this->unique   = $metadataconfig['unique'];
        $this->nullable = $metadataconfig['nullable'];

        if (isset($metadataconfig['id']) && ($metadataconfig['id'])) {
            $this->pivotx_type = 'entity.id';
        }
        else {
            switch ($this->type) {
                case 'integer':
                    $this->pivotx_type = 'html.integer';
                    break;
                case 'float':
                    $this->pivotx_type = 'html.float';
                    break;
                case 'string':
                    $this->pivotx_type = 'html.text';
                    break;
                case 'text':
                    $this->pivotx_type = 'html.textarea';
                    break;
                case 'date':
                    $this->pivotx_type = 'html.date';
                    break;
                case 'time':
                    $this->pivotx_type = 'html.time';
                    break;
                case 'datetime':
                    $this->pivotx_type = 'html.datetime';
                    break;
                case 'boolean':
                    $this->pivotx_type = 'html.boolean';
                    break;
            }
        }
    }

    /**
     * Import entity information from the metadata YAML file
     */
    public function importYamlArray($config)
    {
    }

    /**
     * Import additional information from the PivotX configuration
     */
    public function importPivotConfig($config)
    {
        if (isset($config['pivotx_type'])) {
            $this->pivotx_type = $config['pivotx_type'];
        }
        if (isset($config['state'])) {
            $this->state = $config['state'];
        }
        if (isset($config['in_crud'])) {
            $this->in_crud = $config['in_crud'];
        }
    }

    /**
     * Export as a YAML array
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
            'pivotx_type' => $this->pivotx_type,
            'state' => $this->state,
            'in_crud' => $this->in_crud,
        );

        return $config;
    }
}
