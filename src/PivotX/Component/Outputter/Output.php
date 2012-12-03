<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 *
 *
 *
 * Rewritten non-assetic compatible version
 *
 */

namespace PivotX\Component\Outputter;

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
    protected $debuggable = false;

    private static $last_source_directory = false;
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
     * Don't compress or concat this output
     */
    public function allowDebugging()
    {
        $this->debuggable = true;
    }

    /**
     */
    public function shouldBeDebuggable()
    {
        return $this->debuggable;
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
     * Return a sort-of descriptive cache filename
     *
     * The developer should be able to determine what the original file was.
     *
     * @param string $extension        of the cached file
     * @param string $name_suggestion  name suggestion
     * @return string                  cache filename
     */
    public function determineCacheFilename($extension, $name_suggestion)
    {
        $dirname  = dirname($name_suggestion);
        $basename = pathinfo($name_suggestion, PATHINFO_FILENAME);

        if (preg_match('#(.+)(/src|app|web/)(.+)#', $dirname, $match)) {
            $dirname = $match[3];
        }
        $dirname = str_replace('/PivotX/', '/', $dirname);
        $dirname = str_replace('/Resources/', '/', $dirname);
        $dirname = str_replace('Bundle', '', $dirname);
        $dirname = substr($dirname, 0, 40);
        $dirname = trim(preg_replace('|[^a-zA-Z0-9+]|', ' ', $dirname));
        $dirname = str_replace(' ', '_', $dirname);

        return $dirname.'_'.$basename.'.'.$extension;
    }

    /**
     * Get the url for a cache filename
     *
     * @param string $filename    cached filename
     * @return string             cache url to use
     */
    public function getCacheUrl($filename)
    {
        // @todo error, error, wrong, error, wrong
        return '/app_dev.php/cwr/'.$filename;
    }

    /**
     * Prepare a cache-file
     *
     * @param string $content          the cached contents
     * @param string $extension        of the cached file
     * @param string $temp_directory   location where we place the file
     * @param string $name_suggestion  name suggestion
     * @return string                  the cache url to use
     */
    public function prepareCacheFile($content, $extension, $temp_directory, $name_suggestion)
    {
        $fname = $this->determineCacheFilename($extension, $name_suggestion);

        file_put_contents($temp_directory.'/'.$fname, $content);

        $src = $this->getCacheUrl($fname);

        return $src;
    }

    /**
     * Copy some resource to the cache and return new url
     *
     * @param string $source          source filename
     * @param string $temp_directory  our cache directory
     * @return string                 the cache url to use
     */
    public function copyFileToCache($source, $temp_directory)
    {
        $extension = 'dat';
        if (preg_match('|[.]([^.\\/]+)$|', $source, $match)) {
            $extension = $match[1];
        }

        //* don't cache this yet
        $fname = $this->determineCacheFilename($extension, $source);
        if (file_exists($temp_directory.'/'.$source)) {
            return $this->getCacheUrl($fname);
        }
        //*/

        $content = file_get_contents($source);

        return $this->prepareCacheFile($content, $extension, $temp_directory, $source);
    }

    /**
     * Internal preg callback
     */
    public function fixCssUrl($match)
    {
        $target = trim($match[1]);

        if (preg_match('|^(["\'])(.+)\\1$|', $target, $quotematch)) {
            $target = $quotematch[2];
        }

        if (preg_match('/(https?|data)/', $target, $urlmatch)) {
            // ignore this string for now
            // @todo a https? target could match our host of course
            return $match[0];
        }

        $source_filename = realpath(self::$last_source_directory . '/' . $target);

        if (file_exists($source_filename)) {
            $out = $this->copyFileToCache($source_filename, self::$active_temp_directory);

            $out = 'url('.$out.')';
        }
        else {
            $out = $match[0];
        }

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
    private function rewriteCssUrlFilter($content)
    {
        return preg_replace_callback('|url[(]([^)]+)[)]|U', array($this, 'fixCssUrl'), $content);
    }

    /**
     * Our text/javascript filter
     */
    private function filterTextJavascript($content)
    {
        return 
            '<script type="text/javascript">'."\n".
            $content.
            '</script>'."\n"
            ;
    }

    /**
     * Possible merge various text/javascript hrefs
     */
    private function mergeTextJavascriptSrc($temp_directory, $srcs, $merge_filter = 'concat')
    {
        $content = '';

        switch ($merge_filter) {
            case 'concat':
                $data = '';
                $first_src = $srcs[0];
                foreach($srcs as $src) {
                    $src  = preg_replace('|(.*)/cwr/(.+)|', '\\2', $src);
                    $file = $temp_directory . '/' . $src;

                    $data .= file_get_contents($file) . "\n";
                }
                $src = $this->prepareCacheFile($data, 'js', $temp_directory, '/merged/'.basename($first_src));
                $content = '<script type="text/javascript" src="'.$src.'"></script>'."\n";
                break;

            default:
                foreach($srcs as $src) {
                    $content .= '<script type="text/javascript" src="'.$src.'"></script>'."\n";
                }
                break;
        }

        return $content;
    }

    /**
     * Our text/x-javascript-src filter
     */
    private function filterTextJavascriptSrc($sources, $temp_directory)
    {
        if (!is_array($sources)) {
            $sources = array($sources);
        }

        $srcs = array();
        foreach($sources as $source) {
            $src = $this->copyFileToCache($source, $temp_directory);

            $srcs[] = $src;
        }

        $content = $this->mergeTextJavascriptSrc($temp_directory, $srcs);

        return $content;
    }

    /**
     * Our text/css filter
     */
    private function filterTextCss($content)
    {
        return
            '<style type="text/css">'."\n".
            $this->rewriteCssUrlFilter($content).
            '</style>'."\n"
            ;
    }

    /**
     * Possible merge various text/css hrefs
     */
    private function mergeTextCssHref($temp_directory, $hrefs, $merge_filter = 'concat')
    {
        $content = '';

        switch ($merge_filter) {
            case 'concat':
                $data = '';
                $first_href = $hrefs[0];
                foreach($hrefs as $href) {
                    $href  = preg_replace('|(.*)/cwr/(.+)|', '\\2', $href);
                    $file = $temp_directory . '/' . $href;

                    $data .= file_get_contents($file) . "\n";
                }
                $href = $this->prepareCacheFile($data, 'css', $temp_directory, '/merged/'.basename($first_href));
                $content = '<link rel="stylesheet" type="text/css" href="'.$href.'" />'."\n";
                break;

            default:
                foreach($hrefs as $href) {
                    $content .= '<link rel="stylesheet" type="text/css" href="'.$href.'" />'."\n";
                }
                break;
        }

        return $content;
    }

    /**
     * Our text/x-css-href filter
     */
    private function filterTextCssHref($sources, $temp_directory)
    {
        if (!is_array($sources)) {
            $sources = array($sources);
        }

        $hrefs = array();
        foreach($sources as $source) {
            self::$last_source_directory = dirname($source);

            $data = file_get_contents($source);

            $data = $this->rewriteCssUrlFilter($data);

            $href = $this->prepareCacheFile($data, 'css', $temp_directory, $source);

            $hrefs[] = $href;
        }

        $content = $this->mergeTextCssHref($temp_directory, $hrefs);

        return $content;
    }

    /**
     * Our text/x-less-href filter
     */
    private function filterTextLessHref($sources, $temp_directory)
    {
        if (!is_array($sources)) {
            $sources = array($sources);
        }

        foreach($sources as $source) {
            $cache_fname = $this->determineCacheFilename('css', $source);

            if (file_exists($temp_directory.'/'.$cache_fname)) {
                $source_time = filemtime($source);
                $cache_time  = filemtime($temp_directory.'/'.$cache_fname);

                $diff = $cache_time - $source_time;
                if ($diff >= 1) {
                    $hrefs[]= $this->getCacheUrl($cache_fname);
                    continue;
                }
            }

            self::$last_source_directory = dirname($source);

            $cmd = '/usr/local/bin/lessc '.$source;
            //echo 'cmd[ '. $cmd .' ]<br/>';
            $fp = popen($cmd, 'r');
            $data = '';
            while (!feof($fp)) {
                $data .= fread($fp, 4096);
            }
            pclose($fp);

            $data = $this->rewriteCssUrlFilter($data);

            $href = $this->prepareCacheFile($data, 'css', $temp_directory, $source);

            $hrefs[] = $href;
        }

        $content = $this->mergeTextCssHref($temp_directory, $hrefs);

        return $content;
    }

    /**
     * Return the content snippets ready for inclusion in the html
     *
     * @return string    html valid output
     */
    public function getHtml($temp_directory)
    {
        $output = '';

        self::$active_temp_directory = $temp_directory;

        switch ($this->type) {
            case 'text/javascript':
                $output .= $this->filterTextJavascript($this->content);
                break;

            case 'text/x-javascript-src':
                $output .= $this->filterTextJavascriptSrc($this->content, $temp_directory);
                break;

            case 'text/css':
                $output .= $this->filterTextCss($this->content);
                break;

            case 'text/x-css-href':
                $output .= $this->filterTextCssHref($this->content, $temp_directory);
                break;

            case 'text/x-less-href':
                $output .= $this->filterTextLessHref($this->content, $temp_directory);
                break;

            case 'text/html':
                $output .= $this->content;
                break;
        }

        return $output;
    }
}
