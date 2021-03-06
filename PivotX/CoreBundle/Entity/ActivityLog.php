<?php

namespace PivotX\CoreBundle\Entity;

use PivotX\Doctrine\Annotation as PivotX;
use PivotX\Component\Activity\Service as ActivityService;

/**
 */
class ActivityLog
{
    private $id;
    private $sitename;
    private $date_logged;
    private $level;
    private $importance;
    private $user;
    private $technical_context;
    private $friendly_language;
    private $friendly_message;
    private $friendly_arguments;
    private $primary_tag;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sitename
     *
     * @param string $sitename
     */
    public function setSitename($sitename)
    {
        $this->sitename = $sitename;
    }

    /**
     * Get sitename
     *
     * @return string 
     */
    public function getSitename()
    {
        return $this->sitename;
    }

    /**
     * Set date_logged
     *
     * @param datetime $dateLogged
     */
    public function setDateLogged($dateLogged)
    {
        $this->date_logged = $dateLogged;
    }

    /**
     * Get date_logged
     *
     * @return datetime 
     */
    public function getDateLogged()
    {
        return $this->date_logged;
    }

    /**
     */
    public function getDateLogged_Date()
    {
        return $this->date_logged->format('Y-m-d');
    }

    /**
     */
    public function getDateLogged_Time()
    {
        return $this->date_logged->format('H:i');
    }

    /**
     * Set level
     *
     * @param integer $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set importance
     *
     * @param integer $importance
     */
    public function setImportance($importance)
    {
        $this->importance = $importance;
    }

    /**
     * Get importance
     *
     * @return integer 
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * Get friendly level message
     *
     * @return string
     */
    public function getFriendlyLevel()
    {
        switch ($this->level) {
            case ActivityService::LEVEL_SITE:
                return 'website';
                break;
            case ActivityService::LEVEL_EDITORIAL:
                return 'editorial';
                break;
            case ActivityService::LEVEL_ADMINISTRATIVE:
                return 'administrative';
                break;
            case ActivityService::LEVEL_SECURITY:
                return 'security';
                break;
            case ActivityService::LEVEL_TECHNICAL:
                return 'technical';
                break;
        }

        return 'level:'.$this->level;
    }

    /**
     * Get friendly importance message
     *
     * @return string
     */
    public function getFriendlyImportance()
    {
        switch ($this->importance) {
            case ActivityService::IMPORTANCE_NOT:
                return 'not important';
                break;
            case ActivityService::IMPORTANCE_AVERAGE:
                return 'average important';
                break;
            case ActivityService::IMPORTANCE_VERY:
                return 'very important';
                break;
            case ActivityService::IMPORTANCE_MOST:
                return 'most important';
                break;
        }

        return 'importance:'.$this->importance;
    }

    /**
     * Set technical_context
     *
     * @param text $technicalContext
     */
    public function setTechnicalContext($technicalContext)
    {
        $this->technical_context = $technicalContext;
    }

    /**
     * Add technical_context
     *
     * @param text $technicalContext
     */
    public function addTechnicalContext($technicalContext)
    {
        $this->technical_context = array_merge($this->technical_context, $technicalContext);
    }

    /**
     * Get technical_context
     *
     * @return text 
     */
    public function getTechnicalContext()
    {
        return $this->technical_context;
    }

    /**
     * Has technical_context
     *
     * @return boolean 
     */
    public function hasTechnicalContext()
    {
        if (is_null($this->technical_context)) {
            return false;
        }
        if (is_array($this->technical_context) && (count($this->technical_context) == 0)) {
            return false;
        }
        if (is_string($this->technical_context) && ($this->technical_context == '')) {
            return false;
        }
        return true;
    }

    /**
     * Set friendly_language
     *
     * @param string $friendlyLanguage
     */
    public function setFriendlyLanguage($friendlyLanguage)
    {
        $this->friendly_language = $friendlyLanguage;
    }

    /**
     * Get friendly_language
     *
     * @return string 
     */
    public function getFriendlyLanguage()
    {
        return $this->friendly_language;
    }

    /**
     * Set friendly_message
     *
     * @param text $friendlyMessage
     */
    public function setFriendlyMessage($friendlyMessage)
    {
        $this->friendly_message = $friendlyMessage;
    }

    /**
     * Get friendly_message
     *
     * @return text 
     */
    public function getFriendlyMessage()
    {
        return $this->friendly_message;
    }

    /**
     * Set friendly_arguments
     *
     * @param array $friendlyArguments
     */
    public function setFriendlyArguments($friendlyArguments)
    {
        $this->friendly_arguments = $friendlyArguments;
    }

    /**
     * Get friendly_arguments
     *
     * @return array 
     */
    public function getFriendlyArguments()
    {
        return $this->friendly_arguments;
    }

    /**
     * Set primary_tag
     *
     * @param string $primary_tag
     */
    public function setPrimaryTag($primary_tag)
    {
        $this->primary_tag = $primary_tag;
    }

    /**
     * Get primary_tag
     *
     * @return string 
     */
    public function getPrimaryTag()
    {
        return $this->primary_tag;
    }

    /**
     */
    public function getCompiledFriendlyMessage()
    {
        $tr = array();
        if (is_array($this->friendly_arguments)) {
            foreach($this->friendly_arguments as $key => $value) {
                $tr[':'.$key] = $value;
            }
        }

        return new \Twig_MarkUp(strtr($this->friendly_message, $tr), 'utf-8');
    }

    /**
     * Set user
     *
     * @param PivotX\CoreBundle\Entity\User $user
     */
    public function setUser(\PivotX\CoreBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    /**
     * Get user
     *
     * @return PivotX\CoreBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }


}
