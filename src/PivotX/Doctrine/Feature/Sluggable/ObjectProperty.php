<?php

namespace PivotX\Doctrine\Feature\Sluggable;


class ObjectProperty implements \PivotX\Doctrine\Entity\EntityProperty
{
    private $fields = null;
    private $metaclassdata = null;

    private $field_slug = 'slug';
    private $field_slug_format = '%title%';

    public function __construct(array $fields, $metaclassdata)
    {
        $this->fields        = $fields;
        $this->metaclassdata = $metaclassdata;
    }

    /**
     * Get feature methods independent of field configuration
     */
    public function getPropertyMethodsForEntity($config)
    {
        return array();
    }

    /**
     * Get feature methods dependent on field configuration
     */
    public function getPropertyMethodsForField($field, $config)
    {
        $methods = array();

        $methods['getCrudConfiguration_'.$field] = 'generateGetCrudConfiguration';
        $methods['getSlugSuggestion'] = 'generateGetSlugSuggestion';
        $methods['normalizeSlug'] = 'generateNormalizeSlug';

        return $methods;
    }

    private function getFormatString($field)
    {
        $format_field = $this->field_slug_format;

        foreach($this->fields as $fld) {
            if ($fld[0] == $field) {
                $format_field = $fld[1]['format'];
            }
        }

        return $format_field;
     }

    public function generateGetCrudConfiguration($classname, $field, $config)
    {
        // note: we could decode this now but maybe the source
        //       for the format string will be 'live' as well some day
        $format_field = $this->getFormatString($field);

        return <<<THEEND
    /**
     * Return the CRUD field configuration
     * 
     * @PivotX\Internal       internal use only
%comment%
     */
    public function getCrudConfiguration_$field()
    {
        preg_match_all('/%([^%]+?)%/', "$format_field", \$matches);
        \$sources = implode(' ', \$matches[1]);

        return array(
            'name' => '$field',
            'type' => 'backend_unique',
            'arguments' => array(
                'sources' => \$sources
            )
        );
    }
THEEND;
    }

    public function generateNormalizeSlug()
    {
        $field = $this->field_slug;

        return <<<THEEND
    /**
     * Normalize the slug
     *
%comment%
     */
    public function normalizeSlug(\$value)
    {
        return \PivotX\Doctrine\Feature\Sluggable\Helpers::normalizeSlug(\$value);
    }

THEEND;
    }

    public function generateGetSlugSuggestion()
    {
        $field        = $this->field_slug;
        $format_field = $this->getFormatString($field);

        return <<<THEEND
    /**
     * Generate a slug using the format argument
     *
     * @PivotX\Internal       internal use only
%comment%
     */
    public function getSlugSuggestion(\$number = 0)
    {
        \$data = get_object_vars(\$this);

        \$suggestion = preg_replace_callback(
            '/(%([^%]+?)%)/',
            function(\$match) use (\$data){
                \$field = \$match[2];
                if (isset(\$data[\$field])) {
                    return \$data[\$field];
                }
                return '';
            },
            "$format_field"
        );

        if (\$number > 0) {
            \$suggestion .= '-' . \$number;
        }

        \$suggestion = \$this->normalizeSlug(\$suggestion);

        return \$suggestion;
    }

THEEND;
    }
}

