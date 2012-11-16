<?php
/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Lists;


class RouteItem extends Item
{
    private static $routing_service = null;
    private static $routing_latest_routematch = null;
    private static $routing_latest_reference = null;
    private static $routing_latest_reference_text = null;
    private static $routing_latest_reference_attrs = array();

    private $item_reference;

    public function __construct($name, $reference)
    {
        parent::__construct($name);

        $this->item_reference = $reference;
    }

    public static function setRoutingService($routing_service)
    {
        self::$routing_service = $routing_service;
    }

    public function isActive()
    {
        if (self::$routing_latest_routematch === null) {
            self::$routing_latest_routematch = self::$routing_service->getLatestRouteMatch();

            if (!is_null(self::$routing_latest_routematch)) {
                self::$routing_latest_reference = self::$routing_latest_routematch->buildReference();
                self::$routing_latest_reference_text = self::$routing_latest_routematch->buildReference()->buildTextReference();
                $all_attrs = self::$routing_latest_routematch->getAttributes();
                $attrs     = array();
                foreach($all_attrs as $key => $value) {
                    if (substr($key, 0, 1) != '_') {
                        $attrs['{'.$key.'}'] = $value;
                    }
                }
                self::$routing_latest_reference_attrs = $attrs;
            }
        }

        if (!is_null(self::$routing_latest_reference)) {
            $this->reference = new \PivotX\Component\Referencer\Reference(self::$routing_latest_reference, $this->item_reference);
            $this->reference_text = $this->reference->buildTextReference();
        }

        if (!is_null($this->reference)) {
            // @todo maybe this should be better?
            // sketchy implementation
            $text = strtr($this->reference_text, self::$routing_latest_reference_attrs);
            //echo 'latest = "'.self::$routing_latest_reference_text.'"<br/>'."\n";
            //echo 'text = "'.$text.'"<br/>'."\n";
            if ($text == self::$routing_latest_reference_text) {
                return true;
            }
        }

        return false;
    }

    public function getLink()
    {
        return self::$routing_service->buildUrl($this->item_reference, null);
    }
}
