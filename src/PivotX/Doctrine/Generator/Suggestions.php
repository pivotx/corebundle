<?php
namespace PivotX\Doctrine\Generator;


/**
 * Suggestions for new entity types and entity features
 *
 * This is too hardcoded at the moment.
 */
class Suggestions
{
    private $types;
    private $features;

    public function __construct()
    {
        // @todo types should not be hardcoded
        $this->types = array(
            'entity.id' => array(
                'type_description' => 'record identifier',
                'description' => 'Identity field for the record',
                'unique' => true,
                'orm' => array(
                    'id' => true,
                    'generator' => array(
                        'strategy' => 'IDENTITY'
                    )
                )
            ),

            'html.text' => array(
                'type_description' => 'text',
                'description' => 'Single line text field',
                'orm' => array(
                    'type' => 'string',
                    'length' => 200,
                    'nullable' => true,
                )
            ),
            'html.textarea' => array(
                'type_description' => 'textarea',
                'description' => 'Multi-line text field',
                'orm' => array(
                    'type' => 'text',
                    'nullable' => true,
                )
            ),
            'html.decimal' => array(
                'type_description' => 'decimal',
                'description' => 'Decimal number field.',
                'orm' => array(
                    'type' => 'integer',
                )
            ),
            'html.float' => array(
                'type_description' => 'float',
                'description' => 'Floatingpoint number field.',
                'orm' => array(
                    'type' => 'float',
                    'nullable' => true,
                )
            ),
            'html.datetime' => array(
                'type_description' => 'datetime',
                'description' => 'Date and time field.',
                'orm' => array(
                    'type' => 'datetime',
                    'nullable' => true,
                )
            ),
            'html.date' => array(
                'type_description' => 'date',
                'description' => 'Date field.',
                'orm' => array(
                    'type' => 'date',
                    'nullable' => true,
                )
            ),
            'html.time' => array(
                'type_description' => 'time',
                'description' => 'Time field.',
                'orm' => array(
                    'type' => 'time',
                    'nullable' => true,
                )
            ),
            'html.boolean' => array(
                'type_description' => 'decimal',
                'description' => 'Boolean field.',
                'orm' => array(
                    'type' => 'boolean',
                )
            ),

            'html.username' => array(
                'type_description' => 'text',
                'description' => 'Unique user name field.',
                'orm' => array(
                    'type' => 'string',
                    'length' => 100,
                    'unique' => true,
                    'nullable' => true
                )
            ),
            'html.email' => array(
                'type_description' => 'text',
                'description' => 'Email field.',
                'orm' => array(
                    'type' => 'string',
                    'length' => 200,
                    'nullable' => true
                )
            ),

            'feature.timestampable.create' => array(
                'type_description' => 'datetime',
                'description' => 'Creation date/time for the record',
                'orm' => array(
                    'type' => 'datetime',
                    'auto_entity' => array(
                        'timestampable' => array(
                            'on' => 'create'
                        )
                    )
                )
            ),
            'feature.timestampable.update' => array(
                'type_description' => 'datetime',
                'description' => 'Last update date/time for the record',
                'orm' => array(
                    'type' => 'datetime',
                    'auto_entity' => array(
                        'timestampable' => array(
                            'on' => 'update'
                        )
                    )
                )
            ),
            'feature.sluggable.slug' => array(
                'type_description' => 'slug',
                'description' => 'Record slug',
                'needs' => 'arguments',
                'orm' => array(
                    'type' => 'string',
                    'length' => 64,
                    'nullable' => true,
                    'auto_entity' => array(
                        'sluggable' => array(
                            'format' => '%title%'
                        )
                    )
                )
            ),
            'feature.publishable.state' => array(
                'type_description' => 'choices',
                'description' => 'Publish state (publish, hold, publish_on)',
            ),

            'relation.genericresource.single' => array(
                'type_description' => 'single-resource',
                'description' => 'A single resource field.'
            ),
            'relation.genericresource.multiple' => array(
                'type_description' => 'multiple-resource',
                'description' => 'A multiple resource field.'
            ),
            'relation.any.to-one' => array(
                'type_description' => 'many-to-one',
                'description' => 'A many-to-one relation field.',
                'needs' => 'relation'
            ),
            'relation.any.to-many' => array(
                'type_description' => 'many-to-many',
                'description' => 'A many-to-many relation field.',
                'needs' => 'relation'
            ),
        );

        // @todo features should not be hardcoded
        $this->features = array(
            'timesliceable' => array(
                'type' => 'timesliceable',
                'description' => 'Select entities based on a date or date range',
                'orm' => array(
                    'auto_entity' => array(
                        'timesliceable' => array(
                            'fields' => ''
                        )
                    )
                )
            ),
        );
    }

    public function getFieldTypes()
    {
        $types = array_keys($this->types);
        array_shift($types);
        return $types;
    }

    public function getFeatures()
    {
        $features = array_keys($this->features);
        return $features;
    }

    public function getOrmFieldFromType($type)
    {
        if (isset($this->types[$type])) {
            if (isset($this->types[$type]['orm'])) {
                return $this->types[$type]['orm'];
            }
        }

        return null;
    }

    public function getTwigFieldFromType($type)
    {
        $field = array(
            'type_description' => false,
            'nullable' => false,
            'unique' => false,
            'editor' => false
        );

        if (isset($this->types[$type])) {
            foreach($this->types[$type] as $k => $v) {
                $field[$k] = $v;
            }
        }

        return $field;
    }

    public function getFeature($name, $arguments)
    {
        $feature = $this->features[$name];

        if ($name == 'timesliceable') {
            $_fields = explode(',', trim($arguments));
            $fields  = array();
            foreach($_fields as $field) {
                if (strpos($field, '-') > 0) {
                    list($f1, $f2) = explode('-', trim($field));
                    $fields[] = array('start_field'=>trim($f1), 'end_field'=>trim($f2));
                }
                else {
                    $fields[] = array('field'=>trim($field));
                }
            }
            $feature['orm']['auto_entity']['timesliceable']['fields'] = $fields;
        }

        return $feature;
    }
}
