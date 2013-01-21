<?php

/**
 * This file is part of the PivotX Core bundle
 *
 * (c) Marcel Wouters / Two Kings <marcel@twokings.nl>
 */

namespace PivotX\Component\Twig;

use PivotX\Component\Routing\Service as RoutingService;
use PivotX\Component\Translations\Service as TranslationsService;
use PivotX\Component\Formats\Service as FormatsService;
use PivotX\Component\Webresourcer\Service as WebresourcerService;
use PivotX\Component\Outputter\Service as OutputterService;
use PivotX\Component\Siteoptions\Service as SiteoptionsService;

include_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/Resources/lib/utilphp/util.php';

/**
 * Twig Query interface
 *
 * @author Marcel Wouters <marcel@twokings.nl>
 *
 * @api
 */
class Service extends \Twig_Extension
{
    protected $environment = false;
    protected $pivotx_routing = false;
    protected $pivotx_translations = false;
    protected $pivotx_formats = false;
    protected $pivotx_webresourcer = false;
    protected $pivotx_outputter = false;
    protected $pivotx_siteoptions = false;

    /**
     */
    public function __construct(RoutingService $pivotx_routing, TranslationsService $pivotx_translations, FormatsService $pivotx_formats, WebresourcerService $pivotx_webresourcer, OutputterService $pivotx_outputter, SiteoptionsService $pivotx_siteoptions)
    {
        $this->pivotx_routing      = $pivotx_routing;
        $this->pivotx_translations = $pivotx_translations;
        $this->pivotx_formats      = $pivotx_formats;
        $this->pivotx_webresourcer = $pivotx_webresourcer;
        $this->pivotx_outputter    = $pivotx_outputter;
        $this->pivotx_siteoptions  = $pivotx_siteoptions;
    }

    /**
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function getName()
    {
        return 'pivotx';
    }

    public function getFunctions()
    {
        $functions = array(
            // normal use
            'ref' =>  new \Twig_Function_Method($this, 'getReference'),
            'pagination' => new \Twig_Function_Method($this, 'getPagination'),
            'translate' => new \Twig_Function_Method($this, 'getTranslate'),

            // house-keeping
            'outputter' => new \Twig_Function_Method($this, 'getOutputter'),
            'pxdump' => new \Twig_Function_Method($this, 'getPxDump')
        );

        if ($this->pivotx_siteoptions->getValue('translations.debug', false) === true) {
            $functions['translate'] = new \Twig_Function_Method($this, 'getDebugTranslate');
        }

        return $functions;
    }

    public function getFilters()
    {
        return array(
            // normal use
            'formatas' => new \Twig_Filter_Method($this, 'filterFormatAs'),
            'fa' => new \Twig_Filter_Method($this, 'filterFormatAs'),
            'htmliterator' => new \Twig_Filter_Method($this, 'filterHtmlIterator'),

            // pivotx internal
            'pivotx_documentation' => new \Twig_Filter_Method($this, 'filterPivotxDocumentation')
        );
    }

    public function getTokenParsers()
    {
        return array(
            new Loadview(),
            new Loadlist()
        );
    }

    /**
     * Build an URL based on a Reference
     *
     * Shortcut into the Reference/Routing system
     * 
     * @param mixed $text      Either a TextReference (=string) or an array to implode
     * @param array $arguments Query arguments to add to URL
     * @param array $options   URL options see, Routing/Service::buildUrl
     * @return string          URL
     */
    public function getReference($text, $arguments = array(), $options = array())
    {
        if (is_array($text)) {
            $text = implode('', $text);
        }

        $url = $this->pivotx_routing->buildUrl($text, $arguments, $options);

        return $url;
    }

    /**
     * Shortcut into the translation system
     */
    public function getTranslate($key, $macros = array())
    {
        $sitename    = null;
        $output_type = 'twig';

        if (is_array($key)) {
            $key = implode('', $key);
        }
        return $this->pivotx_translations->translate(mb_strtolower($key), $sitename, $output_type, $macros);
    }

    /**
     * Shortcut into the translation system
     */
    public function getDebugTranslate($key, $macros = array())
    {
        $sitename    = null;
        $output_type = 'twig';

        if (is_array($key)) {
            $key = implode('', $key);
        }
        $class_automagic = ($this->pivotx_translations->isTranslatedAutomagically(mb_strtolower($key), $sitename)) ? '' : ' automagic';
        $text = $this->pivotx_translations->translate(mb_strtolower($key), $sitename, $output_type, $macros);
        $text = new \Twig_Markup('<span class="pivotx-is-translated'.$class_automagic.'" title="'.htmlspecialchars($key).'">'.$text.'</span>', 'utf-8');

        return $text;
    }

    /**
     * Fast pagination filler
     *
     * @todo support for multiple kinds
     * @todo get this code out of here
     */
    public function getPagination(\PivotX\Component\Views\ViewInterface $view, $link, $link_arguments = array(), $pagination_variable = 'page')
    {
        $show_pages     = 9;
        $want_firstlast = true;
        $want_prevnext  = true;
        $no_of_pages    = $view->getNoOfPages();

        $current_page = $view->getCurrentPage();

        $first_page = $current_page - floor($show_pages / 2);
        if ($first_page < 1) {
            $first_page = 1;
        }

        $last_page = $first_page + $show_pages - 1;
        if ($last_page > $no_of_pages) {
            $last_page = $no_of_pages;
        }


        // with accept a link as array
        if (is_array($link)) {
            $link = implode('', $link);
        }


        // update the actual link with all arguments

        $paginationmacro = 'PAGINATION';
        $link_arguments[$pagination_variable] = $paginationmacro;

        $query_arguments = $view->getQueryArguments($link_arguments);


        $array = array();

        $base_link = $this->getReference($link, $query_arguments);

        if (($current_page > 1) && ($want_firstlast)) {
            $page_link = str_replace($paginationmacro, '1', $base_link);
            $array[] = array( 'page' => 1, 'class' => 'first', 'title' => $this->getTranslate('pagination.first-page'), 'link' => $page_link );
        }
        if (($current_page > 1) && ($want_prevnext)) {
            $page_link = str_replace($paginationmacro, ($current_page-1), $base_link);
            $array[] = array( 'page' => $current_page-1, 'class' => 'previous', 'title' => $this->getTranslate('pagination.previous-page'), 'link' => $page_link );
        }

        for($page=$first_page; $page <= $last_page; $page++) {
            $page_link = str_replace($paginationmacro, $page, $base_link);
            $class     = false;

            if ($page == $current_page) {
                $class = 'active';
            }

            $array[] = array ( 'page' => $page, 'class' => $class, 'title' => $page, 'link' => $page_link );
        }

        if (($current_page < $no_of_pages) && ($last_page != $no_of_pages)) {
            $page_link = str_replace($paginationmacro, $no_of_pages, $base_link);

            $array[] = array ( 'page' => false, 'class' => 'disabled', 'title' => '...', 'link' => false );

            $array[] = array ( 'page' => $no_of_pages, 'class' => false, 'title' => $no_of_pages, 'link' => $page_link );
        }

        if (($current_page < $no_of_pages) && ($want_prevnext)) {
            $page_link = str_replace($paginationmacro, ($current_page+1), $base_link);
            $array[] = array( 'page' => $current_page+1, 'class' => 'next', 'title' => $this->getTranslate('pagination.next-page'), 'link' => $page_link );
        }
        if (($current_page < $no_of_pages) && ($want_firstlast)) {
            $page_link = str_replace($paginationmacro, $no_of_pages, $base_link);
            $array[] = array( 'page' => $no_of_pages, 'class' => 'last', 'title' => $this->getTranslate('pagination.last-page'), 'link' => $page_link );
        }

        $view = new \PivotX\Component\Views\ArrayView($array, 'Common/Pagination', 'PivotX/Core', 'This is a dynamic view for pagination');

        return $view;
    }

    /**
     * Shortcut into the translation system
     */
    public function getOutputter($group)
    {
        return $this->pivotx_outputter->getOutputs($group);
    }

    /**
     * Nice dump
     */
    public function getPxDump($in)
    {
        return new \Twig_Markup(\util::var_dump($in, true), 'utf-8');
    }

    public function filterFormatAs($in, $name = '')
    {
        $arguments = false;
        $out = $in;

        if ($name == '') {
            $name = 'auto';
        }
        else if (is_array($name)) {
            $arguments = $name;
            $name      = 'auto';
        }

        $format = $this->pivotx_formats->findFormat($name);
        if (!is_null($format)) {
            if (($arguments === false) && (func_num_args() > 2)) {
                $arguments = func_get_args();
                array_shift($arguments);
                array_shift($arguments);
            }
            $out = $format->format($in, $arguments);
        }

        return $out;
    }

    /**
     * Return an HTML iterator for any array
     */
    public function filterHtmlIterator($in, $active_key = null, $active_value = null)
    {
        $args = array();
        if (!is_null($active_key)) {
            $args['active_key'] = $active_key;
        }
        if (!is_null($active_value)) {
            $args['active_value'] = $active_value;
        }
        return new HtmlIterator($in, $args);
    }

    /**
     * A simple documentation extractor (not really fault tolerant)
     *
     * @param string $doccomment      the DocComment found
     * @param string $property_name   property name
     * @param string $object_name     name to use in the example
     * @return array                  associative array
     *                                - description   all the text found
     *                                - snippet       an example snippet
     */
    private function convertDocCommentToDocumentation($doccomment, $property_name, $object_name)
    {
        $source  = explode("\n", trim($doccomment));
        $text    = array();
        $snippet = '{{ ' . $object_name . '.' . strtolower($property_name) . ' }}';

        foreach($source as $src) {
            $line = trim(preg_replace('|/?[*]+/? *(.*)|', '\\1', trim($src)));

            if (preg_match('|@return +([^ ]+)|', $line, $match)) {
                switch ($match[1]) {
                    case 'datetime':
                        $snippet = '{{ ' . $object_name . '.' . strtolower($property_name) . '|formatas(\'Backend/Auto\') }}';
                        break;
                }
                continue;
            }

            $text[] = $line;
        }

        //$snippet     = '<pre class="snippet">' . $snippet . '</pre>';
        $description = trim(implode("\n", $text));
        $description = preg_replace("|\n+|", "\n", $description);
        $description = str_replace("\n", '<br/>', $description);

        return array(
            'description' => new \Twig_Markup($description, 'utf-8'),
            'snippet' => new \Twig_Markup($snippet, 'utf-8')
        );
    }

    /**
     * Extra documentation from a random object
     */
    public function filterPivotxDocumentation($in, $object_name)
    {
        $documentation = array(
            'introduction' => '',
            'template_suggestion' => $object_name.'.html.twig',
            'template_example' => <<<THEEND
{% extends "CoreBundle::Html/Html5.html.twig" %}

{% block body_content %}

<h1>{{ $object_name.title }}</h1>

{% endblock %}
THEEND
,
            'examples' => array()
        );

        $refl_class = new \ReflectionClass($in);
        $methods    = $refl_class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach($methods as $method) {
            if (substr($method->name, 0, 3) == 'get') {
                $name        = substr($method->name, 3);
                $doccomment  = $method->getDocComment();

                if (strstr($doccomment, '@PivotX\\Internal') === false) {
                    $docs        = $this->convertDocCommentToDocumentation($doccomment, $name, $object_name);

                    $documentation['examples'][] = array(
                        'title' => $name,
                        'property' => strtolower($name),
                        'snippet' => $docs['snippet'],
                        'description' => $docs['description'],
                    );
                }
            }
        }

        return $documentation;
    }
}
