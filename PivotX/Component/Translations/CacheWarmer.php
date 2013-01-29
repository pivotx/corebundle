<?php
namespace PivotX\Component\Translations;

use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmer as BaseCacheWarmer;

class CacheWarmer extends BaseCacheWarmer
{
    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
    
    /**
     * Warms up the cache.
     *
     * @param string $cacheDir The cache directory
     */
    public function warmUp($cacheDir)
    {
        $cache = array();

        $languages = false;

        $translations = $this->doctrine->getRepository('PivotX\\CoreBundle\\Entity\\TranslationText')->findAll();
        foreach($translations as $translation) {
            if ($languages === false) {
                $languages = array();
                foreach(get_class_methods($translation) as $method) {
                    if (substr($method, 0, 7) == 'getText') {
                        $languages[strtolower(substr($method, 7))] = $method;
                    }
                }
            }

            if (!isset($cache[$translation->getSitename()])) {
                $cache[$translation->getSitename()] = array();
                foreach($languages as $lang => $method) {
                    $cache[$translation->getSitename()][$lang] = array();
                }
            }

            foreach($languages as $lang => $method) {
                $cache[$translation->getSitename()][$lang][$translation->getGroupname().'.'.$translation->getName()] = array($translation->getEncoding(), $translation->$method());
            }
        }

        $cacheContent = sprintf('<?php return %s;', var_export($cache, true));

        $this->writeCacheFile($cacheDir.'/translations.php', $cacheContent);
    }

    /**
     * Checks whether this warmer is optional or not.
     *
     * Optional warmers can be ignored on certain conditions.
     *
     * A warmer should return true if the cache can be
     * generated incrementally and on-demand.
     *
     * @return Boolean true if the warmer is optional, false otherwise
     */
    public function isOptional()
    {
        return true;
    }
}
