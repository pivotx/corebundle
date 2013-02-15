<?php
namespace PivotX\Doctrine\Generator;


/**
 * Internal entities representation
 */
class EntitiesRepresentation
{
    private $entities = array();

    public function __construct()
    {
    }

    private function _findEntity($name, $create_if_notfound = false)
    {
        for($i=0; $i < count($this->entities); $i++) {
            if ($this->entities[$i]->getName() == $name) {
                return $this->entities[$i];
            }
        }

        if ($create_if_notfound) {
            $entity           = new EntityRepresentation($name);
            $this->entities[] = $entity;
            return $entity;
        }

        return null;
    }

    private function findOrCreateEntity($name)
    {
        return $this->_findEntity($name, true);
    }

    private function sortEntities()
    {
        usort($this->entities, function($a,$b){
            $am = array_search($a->getManaged(), array('full', 'augmented', 'ignore'));
            $bm = array_search($b->getManaged(), array('full', 'augmented', 'ignore'));

            if ($am < $bm) {
                return -1;
            }
            if ($am > $bm) {
                return +1;
            }

            return strcasecmp($a->getName(), $b->getName());
        });
    }

    public function importDoctrineConfiguration($doctrine, $kernel)
    {
        foreach($doctrine->getEntityManagers() as $em) {
            $classes = $em->getMetadataFactory()->getAllMetadata();
            foreach($classes as $class) {
                $_p = explode('\\',$class->name);
                $base_class = end($_p);

                if ($class->rootEntityName == $class->name) {
                    $entity = $this->findOrCreateEntity($base_class);
                    $entity->importMetaDataConfig($class);
                    $entity->importYamlConfig($class, $kernel);

                    // @todo remove this
                    if ($base_class == 'Entry') {
                        //echo '<pre>'; var_dump($class); echo '</pre>';
                    }
                }
            }
        }

        $this->sortEntities();
    }

    public function importPivotConfiguration($siteoptions)
    {
        $config_entities = $siteoptions->findSiteOptions('all', 'config.entities');

        foreach($config_entities as $config_entity) {
            $entity = $this->findOrCreateEntity($config_entity->getName(), true);

            $config = $siteoptions->getValue('config.entities.'.$entity->getInternalName(), null, 'all');
            if (!is_null($config)) {
                $entity->importPivotConfig($config);
            }
            else {
                $entity->setManaged('full');
            }
        }

        $this->sortEntities();
    }

    public function getEntities()
    {
        return $this->entities;
    }
}
