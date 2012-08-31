<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Webresourcer;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

/**
 * A Webresourcer Service
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Service
{
    private $logger;
    private $groups;

    const HEAD_START = 'headStart';
    const TITLE_AFTER = 'titleAfter';
    const HEAD_END = 'headEnd';
    const BODY_START = 'bodyStart';
    const BODY_END = 'bodyEnd';

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        $this->groups = array(
            self::HEAD_START => array(),
            self::TITLE_AFTER => array(),
            self::HEAD_END => array(),
            self::BODY_START => array(),
            self::BODY_END => array()
        );
    }

    /**
     * Return all the webresources for a group in html form
     * 
     * @param string $group   webresource group to return
     * @return string         html of the resources
     */
    public function getWebresources($group)
    {
        $html = '';

        $html .= "\n\t\t".'<!-- webresources group: ['.$group.'] -->'."\n";
        $html .= "\t\t".  '<!-- /webresources group: ['.$group.'] -->'."\n";

        return new \Twig_Markup($html, 'utf-8');
    }

    public function addWebresource($group, Webresource $webresource)
    {
        if (!isset($this->groups[$group])) {
            return false;
        }

        $this->groups[$group][] = $webresource;

        return true;
    }
}
