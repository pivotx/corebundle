<?php

/*
 * This file is part of the PivotX package.
 */

namespace PivotX\Doctrine\Generator;

use Doctrine\Bundle\DoctrineBundle\Registry;
use PivotX\Doctrine\Configuration\YamlConfiguration;
use Symfony\Component\Yaml\Yaml;


/**
 * This is our cache warmer for pre-defined entities.
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 */
class Entities
{
    private $doctrine;
    private $translation_service;

    /**
     * Constructor.
     *
     * @param RegistryInterface $registry A RegistryInterface instance
     */
    public function __construct(Registry $doctrine, $translation_service)
    {
        //echo 'PivotX Doctrine Warmer'."\n";

        $this->doctrine = $doctrine;
        $this->translation_service = $translation_service;
    }

    /**
     * Update a single entity
     */
    public function updateEntityCode($metaclassdata, $feature_configuration, $filename)
    {
        $entity = new Entity($metaclassdata, $feature_configuration);

        $source_original = file_get_contents($filename);
        $source_updated  = $source_original;

        $source_updated = $entity->getUpdatedCode($source_original);

        if ($source_original != $source_updated) {
            $backup_filename = str_replace('.php', '.php~', $filename);

            $ok = true;
            if ($ok && file_exists($backup_filename)) {
                if (!@unlink($backup_filename)) {
                    $ok = false;
                }
            }
            if ($ok) {
                if (!rename($filename, $backup_filename)) {
                    $ok = false;
                }
            }
            if ($ok) {
                file_put_contents($filename, $source_updated);
            }

            return true;
        }

        return false;
    }

    /**
     * Get the original ORM YAML file
     * 
     * @todo make this actually work
     */
    protected function getOrmYamlFilename($base_class)
    {
        return 'src/PivotX/CoreBundle/Resources/config/doctrine/'.$base_class.'.orm.yml';
    }

    /**
     * Get the entity filename
     * 
     * @todo make this actually work
     */
    protected function getEntityFilename($base_class)
    {
        return 'src/PivotX/CoreBundle/Entity/'.$base_class.'.php';
    }

    /**
     * Verify all feature configurations for all entities
     *
     * @return boolean    true if some code changed
     */
    public function updateAllCode()
    {
        $changed_code = false;

        foreach ($this->doctrine->getEntityManagers() as $em) {
            $classes = $em->getMetadataFactory()->getAllMetadata();
            foreach($classes as $class) {
                //echo "Class: ".$class->name."\n";
                //var_dump($class);

                $_p = explode('\\',$class->name);
                $base_class = $_p[count($_p)-1];
                //echo 'Base-class: '.$base_class."\n";

                $orm_filename    = $this->getOrmYamlFilename($base_class);
                $entity_filename = $this->getEntityFilename($base_class);

                //echo 'orm: '.$orm_filename."\n";
                //echo 'php: '.$entity_filename."\n";

                if (file_exists($orm_filename) && (file_exists($entity_filename))) {
                    $feature_configuration = new YamlConfiguration($orm_filename);

                    if ($this->updateEntityCode($class, $feature_configuration, $entity_filename)) {
                        $changed_code = true;
                    }
                }
            }
        }

        return $changed_code;
    }

    /**
     * Convert camelcased stuff to lowercase
     */
    private function deCamelCase($text)
    {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $text));
    }

    /**
     */
    private function convertTermsToLanguageTexts($terms, $languages)
    {
        $texts = array();
        foreach($languages as $language) {
            if (isset($terms[$language])) {
                $texts[$language] = $terms[$language];
            }
        }
        return $texts;
    }

    /**
     * Add a translation
     */
    private function addEntityTranslation($translations, $languages, $entity, $key)
    {
        $groups = array();
        $groups[] = $entity;
        $groups[] = 'any';

        foreach($groups as $group) {
            if (isset($translations[$group])) {
                if (isset($translations[$group][$key])) {
                    $terms = $translations[$group][$key];
                    if (is_scalar($terms)) {
                        $terms = $translations[$group][$terms];
                    }

                    $texts = $this->convertTermsToLanguageTexts($terms, $languages);
                    //$this->translation_service->suggestTexts($entity, $key, 'pivotx-backend', 'utf-8', $texts);

                    $this->translation_service->suggestTexts($entity, 'crud-form.'.$key, 'pivotx-backend', 'utf-8', $texts);
                    $this->translation_service->suggestTexts($entity, 'crud-heading.'.$key, 'pivotx-backend', 'utf-8', $texts);

                    return;
                }
            }
        }

        echo "No translations found for \"$key\".\n";
    }

    /**
     */
    private function getTranslationSuggestionsFilename($name)
    {
        return 'src/PivotX/CoreBundle/Resources/suggestions/'.$name.'.yaml';
    }

    /**
     * Add all missing suggested translations
     *
     * @return boolean    true if some translations have been added
     */
    public function updateAllTranslations()
    {
        $added_translations = true;
        $languages          = array('nld', 'eng');

        $filename = $this->getTranslationSuggestionsFilename('doctrine-preset');
        $translations = \Symfony\Component\Yaml\Yaml::parse($filename);
        foreach ($this->doctrine->getEntityManagers() as $em) {
            $classes = $em->getMetadataFactory()->getAllMetadata();
            foreach($classes as $class) {
                //echo "Class: ".$class->name."\n";

                $_p = explode('\\',$class->name);
                $base_class = $_p[count($_p)-1];

                //$entity = $this->deCamelCase($base_class);
                $entity = mb_strtolower($base_class);

                if (isset($translations[$entity]) && isset($translations[$entity]['entity.title'])) {
                    $terms = $translations[$entity]['entity.title'];
                    $texts = $this->convertTermsToLanguageTexts($terms, $languages);
                    $this->translation_service->suggestTexts($entity, 'entity.title', 'pivotx-backend', 'utf-8', $texts);
                }

                foreach($class->fieldMappings as $key => $config) {
                    $this->addEntityTranslation($translations, $languages, $entity, $key);
                }
            }
        }

        $filename = $this->GetTranslationSuggestionsFileName('backend');
        $translations = \Symfony\Component\Yaml\Yaml::parse($filename);
        foreach($translations as $group => $values) {
            foreach($values as $key => $terms) {
                $texts = $this->convertTermsToLanguageTexts($terms, $languages);
                $this->translation_service->suggestTexts($group, $key, 'pivotx-backend', 'utf-8', $texts);
            }
        }

        return $added_translations;
    }
}
