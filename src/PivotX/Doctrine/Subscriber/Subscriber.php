<?php
namespace PivotX\Doctrine\Subscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\Common\EventSubscriber;

/**
 * Generic PivotX Doctrine subscriber
 *
 * @todo make a fast exit possible (for unmanaged entities)
 *
 * By no means is this meant to be fast.
 * It is meant to not generated conflicts when generating code
 * or fixing the entity definitions.
 */
class Subscriber implements EventSubscriber
{
    /**
     * @todo one easy upgrade could be to remember the methods
     *       for a particular entity/event
     */
    private function getPrefixMethods($prefix, $entity)
    {
        $methods = get_class_methods($entity);
        $len     = strlen($prefix);
        $results = array();
        foreach($methods as $method) {
            if (substr($method, 0, $len) == $prefix) {
                $results[] = $method;
            }
        }
        
        return $results;
    }

    /**
     * @todo one easy upgrade could be to remember the methods
     *       for a particular entity/event
     */
    private function callWithPrefix($prefix, $entity)
    {
        $methods = $this->getPrefixMethods($prefix, $entity);
        foreach($methods as $method) {
            call_user_func(array($entity, $method));
        }
    }

    /**
     * prePersist
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        $this->callWithPrefix('prePersist_', $entity);
    }

    /**
     * preUpdate
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        /*
        ob_start();
        //var_dump($entityManager);
        echo get_class($entityManager);
        echo "\n";
        $out = ob_get_clean();
        file_put_contents('/tmp/listener.txt', $out, FILE_APPEND);
        //*/

        $this->callWithPrefix('preUpdate_', $entity);
    }

    /**
     * onFlush
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        $out = "In onFlush\n";
        file_put_contents('/tmp/listener.txt', $out, FILE_APPEND);

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            $changeset = $uow->getEntityChangeSet($entity);

            $out = "Entity update with id ".$entity->getId()."\n";
            file_put_contents('/tmp/listener.txt', $out, FILE_APPEND);

            $methods = $this->getPrefixMethods('onPxPreUpdate_', $entity);
            foreach($methods as $method) {
                $new_entity = call_user_func_array(array($entity, $method), array($changeset));

                if (!is_null($new_entity)) {
                    $em->persist($new_entity);
                    $classMetadata = $em->getClassMetadata(get_class($new_entity));
                    $uow->computeChangeSet($classMetadata, $new_entity);
                }
            }
        }
    }

    public function preFlush(PreFlushEventArgs $eventArgs)
    {
    }

    /**
     * Return the events we can handle
     */
    public function getSubscribedEvents()
    {
        return array('prePersist', 'preUpdate', 'onFlush');
        return array('prePersist', 'preUpdate', 'onFlush', 'preFlush');
    }
}
