<?php

namespace PivotX\CoreBundle\Entity;

/**
 */
class EmbedResource extends GenericResource
{
    protected $width;
    protected $height;

    /**
     * Set width
     *
     * @param integer $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Get width
     *
     * @return integer 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Get height
     *
     * @return integer 
     */
    public function getHeight()
    {
        return $this->height;
    }


    protected function scaleMethodKeepAspect($inWidth, $inHeight)
    {
        $no_width  = (is_null($inWidth) || ($inWidth === false));
        $no_height = (is_null($inHeight) || ($inHeight === false));

        $width  = $this->getWidth();
        $height = $this->getHeight();

        $aspect = 1;
        if ($height != 0) {
            $aspect = $width / $height;
        }

        if ($no_width && $no_height) {
            // do nothing
        }
        else if ($no_width) {
            $height = $inHeight;
            $width  = round($height * $aspect);
        }
        else if ($no_height) {
            $width  = $inWidth;
            $height = round($width / $aspect);
        }
        else {
            // complete bounding box
            $inAspect = $inWidth / $inHeight;

            //echo 'inAspect = '.$inAspect.' '.$inWidth.'&times;'.$inHeight.'<br/>';
            //echo 'aspect = '.$aspect.' '.$width.'&times;'.$height.'<br/>';

            if ($inAspect > $aspect) {
                $factor = $height / $inHeight;
                $width  = round($width / $factor);
                $height = $inHeight;
            }
            else {
                $factor = $width / $inWidth;
                $width  = $inWidth;
                $height = round($height / $factor);
            }
        }

        return array($width, $height);
    }

    /**
     * Determine an actual width and height
     */
    public function determineWidthAndHeight($inWidth, $inHeight, $scaleMethod = 'keep-aspect', $options = null)
    {
        $width  = 100;
        $height = 100;

        switch ($scaleMethod) {
            case 'keep-aspect':
                // inWidth/inHeight are used as a 'bounding box'
                list($width, $height) = $this->scaleMethodKeepAspect($inWidth, $inHeight);
                break;

            case 'force':
                // inWidth/inHeight are used to force dimensions
                $width  = $inWidth;
                $height = $inHeight;
                break;
        }

        return array($width, $height);
    }

    /**
     * Get the html to embed this
     */
    public function getHtml($width = null, $height = null, $options = null)
    {
        return '';
    }































































































































































































































    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function getCrudConfiguration_width()
    {
        return array(
            'name' => 'width',
            'type' => false
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-09, 16:30:23
     */
    public function getCrudConfiguration_height()
    {
        return array(
            'name' => 'height',
            'type' => false
        );
    }

}
