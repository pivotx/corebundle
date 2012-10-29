<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 *
 *
 *
 * @todo
 *
 * Bad, bad, bad. We attempt to keep the best from assetic and merge our own stuff
 * but the result now is something terrible! We keep it for now because it works,
 * but we need to fix it.
 *
 * Should we keep paths in cached filenames?
 * Should we filter css and copy url(..) etc, etc.?
 */

namespace PivotX\Component\Outputter;

use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;

/**
 * An output stores
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Output
{
    protected $content = false;
    protected $type;

    private static $last_asset_directory = false;
    private static $active_temp_directory = false;

    const TYPE_HTML = 'text/html';
    const TYPE_SCRIPT = 'text/javascript';
    const TYPE_STYLE = 'text/css';
    const TYPE_SCRIPT_SRC = 'text/x-javascript-src';
    const TYPE_LINK_HREF = 'text/x-css-href';
    const TYPE_LINK_LESS_HREF = 'text/x-less-href';

    /**
     * Constructor
     *
     * @param string $content  Content to output later
     * @param string $type     Type of the content
     */
    public function __construct($content, $type = self::TYPE_HTML)
    {
        $this->setContent($content, $type);
    }

    /**
     * Set the content for this output
     *
     * @param string $content  Content to output later
     * @param string $type     Type of the content
     */
    public function setContent($content, $type = self::TYPE_HTML)
    {
        $this->content = $content;
        $this->type    = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get a suggested cache filename (without extension)
     */
    public function getFilename()
    {
        $content = $this->content;
        if (is_array($this->content)) {
            $content = $this->content[0];
        }

        $dirname = pathinfo($content, PATHINFO_DIRNAME);
        if (preg_match('#(.+)(/src|app|web/)(.+)#', $dirname, $match)) {
            $dirname = $match[3];
        }

        $dirname = preg_replace('|[^a-zA-Z0-9]|', '_', $dirname);

        return $dirname . '_' . pathinfo($content, PATHINFO_FILENAME);
    }

    /**
     * Prepare a cache-file
     *
     * @param string $content    the cached contents
     * @param string $extension  of the cached file
     * @return string            the cache url to use
     */
    public static function prepareCacheFile($content, $extension, $temp_directory)
    {
        $hash = sha1(substr($content, 0, 500));

        $fname = $hash.'.'.$extension;

        file_put_contents($temp_directory.'/'.$fname, $content);

        // @todo error, error, wrong, error, wrong
        $src = '/app_dev.php/cwr/'.$fname;

        return $src;
    }

    /**
     * Copy some resource to the cache and return new url
     *
     * @param string $source          source filename
     * @param string $temp_directory  our cache directory
     * @return string                 the cache url to use
     */
    public static function copyFileToCache($source, $temp_directory)
    {
        $content = file_get_contents($source);

        $extension = 'dat';
        if (preg_match('|[.]([^.\\/]+)$|', $source, $match)) {
            $extension = $match[1];
        }

        return self::prepareCacheFile($content, $extension, $temp_directory);
    }

    /**
     */
    public static function fixCssUrl($match)
    {
        $target = trim($match[1]);

        if (preg_match('|^(["\'])(.+)\\1$|', $target, $quotematch)) {
            $target = $quotematch[2];
        }

        if (preg_match('/(https?|data)/', $target, $urlmatch)) {
            // ignore this string
            // @todo a https? target could match our host of course
            // @todo then we should do something about it..
            return $match[0];
        }

        //echo 'found-for-rewriting[ '.$match[0].' ]<br/>match[ '.$target.' ]<br/>';

        $source_filename = self::$last_asset_directory . '/' . $target;
        //echo 'source filename[ '.$source_filename.' ]<br/>';

        if (file_exists($source_filename)) {
            return self::copyFileToCache($source_filename, self::$active_temp_directory);
        }

        //echo '<br/>';

        $out = $match[0];

        return $out;
    }

    /**
     * Our own css url filter
     *
     * @todo in the future we should assetic again for this
     *
     * @param string $content   the content to filter
     * @return string           the filtered content
     */
    protected function rewriteCssUrlFilter($content)
    {
        return preg_replace_callback('|url[(]([^)]+)[)]|U', array(get_class($this), 'fixCssUrl'), $content);
    }

    /**
     * Return the content snippets ready for inclusion in the html
     *
     * @return string    html valid output
     */
    public function getHtml($temp_directory)
    {
        $output = '';

        $assets = false;
        $filters = array();
        switch ($this->type) {
            case 'text/x-less-href':
                // @todo this should not be hard-coded
                $filters[] = new \Assetic\Filter\LessFilter('/usr/bin/node', array('/usr/lib/nodejs', '/usr/local/lib/node_modules'));
            case 'text/x-javascript-src':
            case 'text/x-css-href':
                $assets = new AssetCollection;
                break;
        }

        // @todo our flawed implementation for CSS URL filtering
        //       needs to know what the 'current' director is
        self:$last_asset_directory   = false;
        self::$active_temp_directory = $temp_directory;

        echo 'new assets<br/>';

        $content = '';
        if ($assets instanceof AssetCollection) {
            if (is_array($this->content)) {
                foreach($this->content as $c) {
                    echo 'asset[ '.$c.' ]<br/>';
                    self::$last_asset_directory = dirname($c);
                    $assets->add(new FileAsset($c, $filters));
                }
            }
            else {
                self::$last_asset_directory = dirname($this->content);
                echo 'asset[ '.$this->content.' ]<br/>';
                $assets->add(new FileAsset($this->content, $filters));
            }

            $content = $assets->dump();
        }
        else {
            $content = $this->content;
        }

        switch ($this->type) {
            case 'text/x-css-href':
            case 'text/x-less-href':
            case 'text/css':
                // @todo this filtering is now hard-coded
                $content = $this->rewriteCssUrlFilter($content);
                break;
        }

        switch ($this->type) {
            case 'text/javascript':
                $output .= '<script type="text/javascript">'."\n";
                $output .= $content;
                $output .= '</script>'."\n";
                break;

            case 'text/x-javascript-src':
                $src = self::prepareCacheFile($content, 'js', $temp_directory);
                $output .= '<script type="text/javascript" src="'.$src.'"></script>'."\n";
                break;

            case 'text/css':
                $output .= '<style type="text/css">'."\n";
                $output .= $content;
                $output .= '</style>'."\n";
                break;

            case 'text/x-css-href':
                $href = self::prepareCacheFile($content, 'css', $temp_directory);
                $output .= '<link rel="stylesheet" href="'.$href.'" />'."\n";
                break;

            case 'text/x-less-href':
                $href = self::prepareCacheFile($content, 'css', $temp_directory);
                $output .= '<link rel="stylesheet" href="'.$href.'" />'."\n";
                break;

            case 'text/html':
                $output .= $this->content;
                break;
        }

        return $output;
    }
}
