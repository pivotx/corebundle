<?php
namespace PivotX\Doctrine\Generator;


class Suggestions
{
    private $types;

    public function __construct()
    {
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
                    'length' => 200
                )
            ),
            'html.textarea' => array(
                'type_description' => 'textarea',
                'description' => 'Multi-line text field',
                'orm' => array(
                    'type' => 'text',
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
                )
            ),
            'html.datetime' => array(
                'type_description' => 'datetime',
                'description' => 'Date and time field.',
                'orm' => array(
                    'type' => 'datetime',
                )
            ),
            'html.date' => array(
                'type_description' => 'date',
                'description' => 'Date field.',
                'orm' => array(
                    'type' => 'date',
                )
            ),
            'html.time' => array(
                'type_description' => 'time',
                'description' => 'Time field.',
                'orm' => array(
                    'type' => 'time',
                )
            ),
            'html.boolean' => array(
                'type_description' => 'decimal',
                'description' => 'Boolean field.',
                'orm' => array(
                    'type' => 'boolean',
                )
            ),

            'pivotx.timestampable.create' => array(
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
            'pivotx.timestampable.update' => array(
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
            'pivotx.sluggable.slug' => array(
                'type_description' => 'slug',
                'description' => 'Record slug',
                'needs' => 'arguments',
                'orm' => array(
                    'type' => 'string',
                    'length' => 64
                )
            ),
            'pivotx.publishable.state' => array(
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
    }


    public function getFieldTypes()
    {
        $types = array_keys($this->types);
        array_shift($types);
        return $types;
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
}
