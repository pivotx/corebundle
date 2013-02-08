<?php
namespace PivotX\Doctrine\Generator;


use Symfony\Component\Yaml\Yaml;
use PivotX\CoreBundle\Entity\TranslationText;


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
                'description' => 'Multi-line text field, contents is plain text.',
                'orm' => array(
                    'type' => 'text',
                    'nullable' => true,
                )
            ),
            'html.integer' => array(
                'type_description' => 'integer',
                'description' => 'Integer number field.',
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
            'html.ref' => array(
                'type_description' => 'reference',
                'description' => 'A link to an internal reference',
                'orm' => array(
                    'type' => 'string',
                    'length' => 200,
                    'nullable' => true,
                )
            ),
            'html.link' => array(
                'type_description' => 'link',
                'description' => 'A link to a website',
                'orm' => array(
                    'type' => 'string',
                    'length' => 200,
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

            'text.html' => array(
                'type_description' => 'textarea',
                'description' => 'Multi-line text field, contents is html.',
                'orm' => array(
                    'type' => 'text',
                    'nullable' => true,
                    'auto_entity' => array(
                        'pivotx_type' => array(
                            'type' => 'textarea',
                            'class' => 'wysiwyg-normal'
                        )
                    )
                )
            ),
            'text.markdown' => array(
                'type_description' => 'textarea',
                'description' => 'Multi-line text field, contents is markdown.',
                'orm' => array(
                    'type' => 'text',
                    'nullable' => true,
                    'auto_entity' => array(
                        'pivotx_type' => array(
                            'type' => 'textarea',
                            'class' => 'markdown'
                        )
                    )
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
            'feature.structurable.order' => array(
                'type_description' => 'integer',
                'description' => 'Structurable order field',
                'orm' => array(
                    'type' => 'integer',
                    'nullable' => false,
                    'auto_entity' => array(
                        'structurable' => array(
                            'kind' => 'order'
                        )
                    )
                )
            ),
            'feature.structurable.parent' => array(
                'type_description' => 'integer',
                'description' => 'Structurable parent field',
                'orm' => array(
                    'type' => 'integer', // @todo foreign key
                    'nullable' => true,
                    'auto_entity' => array(
                        'structurable' => array(
                            'kind' => 'parent'
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
            'feature.publishable.publish' => array(
                'type_description' => 'datetime',
                'description' => 'Publication date',
                'orm' => array(
                    'type' => 'datetime',
                    'auto_entity' => array(
                        'publishable' => array(
                            'type' => 'publish_date'
                        )
                    )
                )
            ),
            'feature.publishable.depublish' => array(
                'type_description' => 'datetime',
                'description' => 'Depublication date',
                'orm' => array(
                    'type' => 'datetime',
                    'nullable' => true,
                    'auto_entity' => array(
                        'publishable' => array(
                            'type' => 'depublish_date'
                        )
                    )
                )
            ),
            'feature.publishable.state' => array(
                'type_description' => 'choices',
                'description' => 'Publish state (publish, hold, publish_on)',
                'orm' => array(
                    'type' => 'string',
                    'length' => 32,
                    'auto_entity' => array(
                        'publishable' => array(
                            'type' => 'publish_state'
                        )
                    )
                )
            ),

            'relation.genericresource.single' => array(
                'type_description' => 'single-resource',
                'description' => 'A single resource field.',
                'relation' => array(
                    'type' => 'manyToOne',
                    'targetEntity' => 'PivotX\CoreBundle\Entity\GenericResource',
                    'auto_entity' => array(
                        'pivotx_backendresource' => array ( null )
                    )
                )
            ),
            'relation.genericresource.multiple' => array(
                'type_description' => 'multiple-resource',
                'description' => 'A multiple resource field.',
                'relation' => array(
                    'type' => 'manyToMany',
                    'targetEntity' => 'PivotX\CoreBundle\Entity\GenericResource',
                )
            ),
            'relation.any.many-to-one' => array(
                'type_description' => 'many-to-one',
                'description' => 'A many-to-one relation field.',
                'needs' => 'relation',
                'relation' => array(
                    'type' => 'manyToOne',
                )
            ),
            'relation.any.many-to-many' => array(
                'type_description' => 'many-to-many',
                'description' => 'A many-to-many relation field.',
                'needs' => 'relation',
                'relation' => array(
                    'type' => 'manyToMany',
                )
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


        // @todo default entities should not be hardcoded
        $this->entities = array(

            /**
             * Entity 'no-fields'
             */
            'no-fields' => array(
                'description' => 'Id field.',
                'fields' => array(
                    array( 'name' => 'id', 'type' => 'entity.id' )
                )
            ),

            /**
             * Entity 'minimal-fields'
             */
            'minimal-fields' => array(
                'description' => 'Id field and date creation/modification fields.',
                'fields' => array(
                    array( 'name' => 'id',           'type' => 'entity.id' ),
                    array( 'name' => 'date_created', 'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified','type' => 'feature.timestampable.update' )
                )
            ),

            /**
             * Entity 'minimal-content'
             */
            'minimal-content' => array(
                'description' => 'Minimal content entity: minimal-fields, publish date, slug, title and body.',
                'fields' => array(
                    array( 'name' => 'id',               'type' => 'entity.id' ),
                    array( 'name' => 'date_created',     'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified',    'type' => 'feature.timestampable.update' ),
                    array( 'name' => 'date_publication', 'type' => 'feature.publishable.publish' ),
                    array( 'name' => 'title',            'type' => 'html.text' ),
                    array( 'name' => 'slug',             'type' => 'feature.sluggable.slug',      'arguments' => '%title%' ),
                    array( 'name' => 'body',             'type' => 'html.textarea' ),
                )
            ),

            /**
             * Entity 'hierarchal-content'
             */
            'hierarchal-content' => array(
                'description' => 'Hierarchal content entity: minimal-content, self-link and menu title.',
                'fields' => array(
                    array( 'name' => 'id',               'type' => 'entity.id' ),
                    array( 'name' => 'date_created',     'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified',    'type' => 'feature.timestampable.update' ),
                    array( 'name' => 'date_publication', 'type' => 'feature.publishable.publish' ),
                    array( 'name' => 'parent_id',        'type' => 'relation.any.many-to-one',    'relation' => 'self.id' ),
                    array( 'name' => 'order_number',     'type' => 'feature.structurable.order' ),
                    array( 'name' => 'title',            'type' => 'html.text' ),
                    array( 'name' => 'menu_title',       'type' => 'html.text' ),
                    array( 'name' => 'slug',             'type' => 'feature.sluggable.slug',      'arguments' => '%title%' ),
                    array( 'name' => 'body',             'type' => 'html.textarea' ),
                )
            ),

            /**
             * Entity 'minimal-newsitem'
             */
            'minimal-newsitem' => array(
                'description' => 'Minimal newsitem entity: minimal-fields, publish features, slug, title, body and user.',
                'fields' => array(
                    array( 'name' => 'id',                 'type' => 'entity.id' ),
                    array( 'name' => 'date_created',       'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified',      'type' => 'feature.timestampable.update' ),
                    array( 'name' => 'publish_state',      'type' => 'feature.publishable.state' ),
                    array( 'name' => 'date_publication',   'type' => 'feature.publishable.publish' ),
                    array( 'name' => 'date_depublication', 'type' => 'feature.publishable.depublish' ),
                    array( 'name' => 'user',               'type' => 'relation.any.many-to-one',    'relation' => 'PivotX\CoreBundle\Entity\User' ),
                    array( 'name' => 'title',              'type' => 'html.text' ),
                    array( 'name' => 'slug',               'type' => 'feature.sluggable.slug',      'arguments' => '%title%' ),
                    array( 'name' => 'body',               'type' => 'html.textarea' ),
                )
            ),

            /**
             * Entity 'regular-newsitem'
             */
            'regular-newsitem' => array(
                'description' => 'Regular newsitem entity: minimal-newsitem, image and introduction.',
                'fields' => array(
                    array( 'name' => 'id',                 'type' => 'entity.id' ),
                    array( 'name' => 'date_created',       'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified',      'type' => 'feature.timestampable.update' ),
                    array( 'name' => 'publish_state',      'type' => 'feature.publishable.state' ),
                    array( 'name' => 'date_publication',   'type' => 'feature.publishable.publish' ),
                    array( 'name' => 'date_depublication', 'type' => 'feature.publishable.depublish' ),
                    array( 'name' => 'user',               'type' => 'relation.any.many-to-one',    'relation' => 'PivotX\CoreBundle\Entity\User' ),
                    array( 'name' => 'title',              'type' => 'html.text' ),
                    array( 'name' => 'slug',               'type' => 'feature.sluggable.slug',          'arguments' => '%title%' ),
                    array( 'name' => 'image',              'type' => 'relation.genericresource.single' ),
                    array( 'name' => 'introduction',       'type' => 'html.textarea' ),
                    array( 'name' => 'body',               'type' => 'html.textarea' ),
                )
            ),

            /**
             * Entity 'linked-entity'
             */
            'linked-entity' => array(
                'description' => 'Linked entity: minimal-fields, publish date, title and link.',
                'fields' => array(
                    array( 'name' => 'id',               'type' => 'entity.id' ),
                    array( 'name' => 'date_created',     'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified',    'type' => 'feature.timestampable.update' ),
                    array( 'name' => 'date_publication', 'type' => 'feature.publishable.publish' ),
                    array( 'name' => 'title',            'type' => 'html.text' ),
                    array( 'name' => 'ref',              'type' => 'html.ref' ),
                )
            ),

            /**
             * Entity 'regular-event'
             */
            'regular-event' => array(
                'description' => 'Regular event entity: minimal-content, publish features, event dates.',
                'fields' => array(
                    array( 'name' => 'id',                 'type' => 'entity.id' ),
                    array( 'name' => 'date_created',       'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified',      'type' => 'feature.timestampable.update' ),
                    array( 'name' => 'publish_state',      'type' => 'feature.publishable.state' ),
                    array( 'name' => 'date_publication',   'type' => 'feature.publishable.publish' ),
                    array( 'name' => 'date_depublication', 'type' => 'feature.publishable.depublish' ),
                    array( 'name' => 'date_event_start',   'type' => 'html.datetime' ),
                    array( 'name' => 'date_event_end',     'type' => 'html.datetime' ),
                    array( 'name' => 'title',              'type' => 'html.text' ),
                    array( 'name' => 'slug',               'type' => 'feature.sluggable.slug',          'arguments' => '%title%' ),
                    array( 'name' => 'image',              'type' => 'relation.genericresource.single' ),
                    array( 'name' => 'introduction',       'type' => 'html.textarea' ),
                    array( 'name' => 'body',               'type' => 'html.textarea' ),
                ),
                'features' => array(
                    array(
                        'type' => 'timesliceable',
                        'orm' => array(
                            'auto_entity' => array(
                                'fields' => array(
                                    array( 'name' => 'Events',    'start_field' => 'date_event_start', 'end_field' => 'date_event_end' )
                                )
                            )
                        )
                    )
                )
            ),

            /**
             * Entity 'minimal-taxonomy'
             *
             * Ex. unordered categories, tags
             */
            'minimal-taxonomy' => array(
                'description' => 'Minimal taxonomy: minimal-fields, publish date, slug, title.',
                'fields' => array(
                    array( 'name' => 'id',               'type' => 'entity.id' ),
                    array( 'name' => 'date_created',     'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified',    'type' => 'feature.timestampable.update' ),
                    array( 'name' => 'date_publication', 'type' => 'feature.publishable.publish' ),
                    array( 'name' => 'title',            'type' => 'html.text' ),
                    array( 'name' => 'slug',             'type' => 'feature.sluggable.slug',      'arguments' => '%title%' ),
                )
            ),

            /**
             * Entity 'ordered-taxonomy'
             *
             * Ex. ordered categories
             */
            'ordered-taxonomy' => array(
                'description' => 'Ordered taxonomy: minimal-taxonomy and order number.',
                'fields' => array(
                    array( 'name' => 'id',               'type' => 'entity.id' ),
                    array( 'name' => 'date_created',     'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified',    'type' => 'feature.timestampable.update' ),
                    array( 'name' => 'date_publication', 'type' => 'feature.publishable.publish' ),
                    array( 'name' => 'order_number',     'type' => 'feature.structurable.order' ),
                    array( 'name' => 'title',            'type' => 'html.text' ),
                    array( 'name' => 'slug',             'type' => 'feature.sluggable.slug',      'arguments' => '%title%' ),
                )
            ),

            /**
             * Entity 'hierarchal-taxonomy'
             *
             * Ex. hierarchal and ordered categories
             */
            'hierarchal-taxonomy' => array(
                'description' => 'Hierarchal taxonomy: ordered-taxonomy and parent taxonomy.',
                'fields' => array(
                    array( 'name' => 'id',               'type' => 'entity.id' ),
                    array( 'name' => 'date_created',     'type' => 'feature.timestampable.create' ),
                    array( 'name' => 'date_modified',    'type' => 'feature.timestampable.update' ),
                    array( 'name' => 'date_publication', 'type' => 'feature.publishable.publish' ),
                    array( 'name' => 'parent_id',        'type' => 'relation.any.many-to-one',    'relation' => 'self.id' ),
                    array( 'name' => 'order_number',     'type' => 'feature.structurable.order' ),
                    array( 'name' => 'title',            'type' => 'html.text' ),
                    array( 'name' => 'slug',             'type' => 'feature.sluggable.slug',      'arguments' => '%title%' ),
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

    public function getEntities()
    {
        $entities = array();
        foreach($this->entities as $key => $entity) {
            $entities[$key] = $entity['description'];
        }
        return $entities;
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

    public function getRelationFromDefinition($definition)
    {
        $type = $definition['type'];

        if (isset($this->types[$type])) {
            if (isset($this->types[$type]['relation'])) {
                $relation = $this->types[$type]['relation'];
                $relation['id'] = $definition['name'];
                if (!isset($relation['targetEntity'])) {
                    $relation['targetEntity'] = $definition['relation'];
                }
                return $relation;
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
            // @todo should not be here
            $_fields = explode(',', trim($arguments));
            $fields  = array();
            foreach($_fields as $field) {
                $name = null;
                if (preg_match('|(.+)[(](.+)[)]|', $field, $match)) {
                    $field = $match[1];
                    $name  = $match[2];
                }
                else {
                    $_name = preg_replace('|[^a-zA-Z_]|', '', $field);
                    $name = \Doctrine\Common\Util\Inflector::classify($_name);
                }

                if (strpos($field, '-') > 0) {
                    list($f1, $f2) = explode('-', trim($field));
                    $fields[] = array('name'=>$name, 'start_field'=>trim($f1), 'end_field'=>trim($f2));
                }
                else {
                    $fields[] = array('name'=>$name, 'field'=>trim($field));
                }
            }
            $feature['orm']['auto_entity']['timesliceable']['fields'] = $fields;
        }

        return $feature;
    }

    public function getEntity($type)
    {
        if (!isset($this->entities[$type])) {
            return null;
        }

        $entity = $this->entities[$type];

        unset($entity['description']);

        return $entity;
    }

    public function setTranslationsForNewEntity($translations, $site, $entity_name)
    {
        $entity_name = strtolower($entity_name);

        // @todo wrong..
        $fname = dirname(dirname(dirname(__FILE__))).'/CoreBundle/Resources/suggestions/translations.doctrine-preset.yaml';

        $suggestions = Yaml::parse($fname);

        $sites = $suggestions['sites'];

        $name = $entity_name;
        if (!isset($sites[$name])) {
            $name = 'item';
        }

        foreach($sites[$name] as $k => $v) {
            $translations->setTexts($entity_name, 'common.'.$k, $site, null, $v, TranslationText::STATE_SUGGESTED);
        }
    }

    public function buildEntity($type, $name, $bundle)
    {
        $entity = $this->getEntity($type);
        if (is_null($entity)) {
            return null;
        }

        $entity['name']   = $name;
        $entity['bundle'] = $bundle;

        return $entity;
    }
}
