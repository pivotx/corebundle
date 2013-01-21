<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Activity;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Symfony\Component\HttpFoundation\Session\Session;


/**
 * PivotX Activity Service
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Service
{
    private $doctrine_registry;
    private $entity_manager = null;
    private $session = null;

    private $sitename;
    private $user;
    private $technical_context;

    private $last_log = null;

    const LEVEL_SITE = 100;
    const LEVEL_EDITORIAL = 200;
    const LEVEL_ADMINISTRATIVE = 500;
    const LEVEL_SECURITY = 800;
    const LEVEL_TECHNICAL = 900;

    // not important means we delete it after a mininum time (say 7 days)
    const IMPORTANCE_NOT = 100;

    // average important means we delete it after a reasonable time (say 31 days)
    const IMPORTANCE_AVERAGE = 500;

    // very important means we keep it even longer (say 365 days)
    const IMPORTANCE_VERY = 900;

    // most important means we don't delete it ever (automatically anyway) (say forever)
    const IMPORTANCE_MOST = 999;

    /**
     * Constructor
     */
    public function __construct(Registry $doctrine_registry, Session $session = null)
    {
        $this->doctrine_registry = $doctrine_registry;
        $this->session           = $session;

        $this->sitename          = '';
        $this->user              = null;
        $this->technical_context = array();
    }

    /**
     * Set the routing context for following messages
     *
     * This method implements a fluent interface.
     */
    public function setRoutingContext($sitename, $routing_context = null)
    {
        $this->sitename = $sitename;

        if (!is_null($routing_context)) {
            $this->technical_context['routing'] = $routing_context;
        }

        return $this;
    }

    /**
     * Actual log the activity
     *
     * This method implements a fluent interface.
     */
    public function log()
    {
        if (!is_null($this->last_log)) {
            if (is_null($this->entity_manager)) {
                $this->entity_manager = $this->doctrine_registry->getEntityManager();
            }

            $this->entity_manager->persist($this->last_log);
            $this->entity_manager->flush();
        }

        return $this;
    }

    /**
     * Discard the activity
     *
     * This method implements a fluent interface.
     */
    public function discard()
    {
        $this->last_log = null;

        return $this;
    }

    /**
     * Create a new log entity
     */
    private function createLogEntity($level, $language, $message, $arguments = null)
    {
        $session = $this->session;
        if (!is_null($session) && ($session->has('security.logged') && ($session->get('security.logged') === true))) {
            $user_id = $session->get('security.user_id');

            if (is_null($this->user) || ($this->user->getId() != $user_id)) {
                $this->user = $this->doctrine_registry->getRepository('PivotX\CoreBundle\Entity\User')->find($user_id);
            }
        }


        $log = new \PivotX\CoreBundle\Entity\ActivityLog;

        $log->setDateLogged(new \DateTime());
        $log->setLevel($level);
        $log->setFriendlyLanguage($language);
        $log->setFriendlyMessage($message);
        $log->setFriendlyArguments($arguments);

        $log->setSitename($this->sitename);
        if (!is_null($this->user)) {
            $log->setUser($this->user);
        }
        $log->setImportance(self::IMPORTANCE_AVERAGE);

        $log->setTechnicalContext($this->technical_context);

        return $log;
    }

    /**
     * Create a new message
     *
     * This method implements a fluent interface.
     */
    public function anyMessage($level, $language, $message, $arguments = null)
    {
        $log = $this->createLogEntity($level, $language, $message, $arguments);

        if (!is_null($this->last_log)) {
            $this->log();
            $this->discard();
        }

        $this->last_log = $log;

        return $this;
    }

    /**
     * Store a site-level message
     *
     * This method implements a fluent interface.
     * 
     * @see sharedMessage
     */
    public function siteMessage($language, $message, $arguments = null)
    {
        return $this->anyMessage(self::LEVEL_SITE, $language, $message, $arguments);
    }

    /**
     * Store a editorial-level message
     *
     * This method implements a fluent interface.
     * 
     * @see sharedMessage
     */
    public function editorialMessage($language, $message, $arguments = null)
    {
        return $this->anyMessage(self::LEVEL_EDITORIAL, $language, $message, $arguments);
    }

    /**
     * Store an administrative-level message
     *
     * This method implements a fluent interface.
     * 
     * @see sharedMessage
     */
    public function administrativeMessage($language, $message, $arguments = null)
    {
        return $this->anyMessage(self::LEVEL_ADMINISTRATIVE, $language, $message, $arguments);
    }

    /**
     * Store a security-level message
     *
     * This method implements a fluent interface.
     * 
     * @see sharedMessage
     */
    public function securityMessage($language, $message, $arguments = null)
    {
        return $this->anyMessage(self::LEVEL_SECURITY, $language, $message, $arguments);
    }

    /**
     * Store a technical-level message
     *
     * This method implements a fluent interface.
     * 
     * @see sharedMessage
     */
    public function technicalMessage($language, $message, $arguments = null)
    {
        return $this->anyMessage(self::LEVEL_TECHNICAL, $language, $message, $arguments);
    }

    /**
     * Creates a loggable message
     *
     * Important!
     */
    public function createLoggableMessage($classname, $id, $entity = null)
    {
        $language  = null;
        $message   = 'Stored a version of :classname with id :id';
        $arguments = array( 'classname' => $classname, 'id' => $id );

        $context = array( 'entity' => $entity );

        $log = $this->createLogEntity(self::LEVEL_TECHNICAL, $language, $message, $arguments);
        $log->addTechnicalContext($context);
        $log->setImportance(self::IMPORTANCE_MOST);
        $log->setPrimaryTag('entity_'.$classname.'_'.$id);

        return $log;
    }

    /**
     * Add (technical) context to the message
     *
     * This method implements a fluent interface.
     */
    public function addContext($context)
    {
        if (!is_null($this->last_log)) {
            $this->last_log->addTechnicalContext($context);
        }

        return $this;
    }

    /**
     * Set the importance of the message
     *
     * This method implements a fluent interface.
     */
    public function setImportance($importance)
    {
        if (!is_null($this->last_log)) {
            $this->last_log->setImportance($importance);
        }

        return $this;
    }

    /**
     * Message is not important
     *
     * This method implements a fluent interface.
     */
    public function notImportant()
    {
        return $this->setImportance(self::IMPORTANCE_NOT);
    }

    /**
     * Message is average important
     *
     * This method implements a fluent interface.
     */
    public function averageImportant()
    {
        return $this->setImportance(self::IMPORTANCE_AVERAGE);
    }

    /**
     * Message is very important
     *
     * This method implements a fluent interface.
     */
    public function veryImportant()
    {
        return $this->setImportance(self::IMPORTANCE_VERY);
    }

    /**
     * Message is most important
     *
     * This method implements a fluent interface.
     */
    public function mostImportant()
    {
        return $this->setImportance(self::IMPORTANCE_MOST);
    }

    public function test()
    {
        $activity = $this->get('pivotx_activity');

        $activity->siteMessage(null, 'Somebody logged in!')->log();
    }
}
