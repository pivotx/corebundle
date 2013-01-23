<?php

namespace PivotX\Component\Webdebug;


use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class TemplateDataCollector extends DataCollector
{
    protected $templates = array();

    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $templates = \PivotX\Component\Twig\WebdebugLoader::getTemplates();

        $first_template = array('template' => '(unknown)', 'file' => '(unknown)' );
        if (count($templates) > 0) {
            $first_template = array_shift($templates);
        }

        $this->data = array(
            'first_template' => $first_template,
            'templates' => $templates
        );
    }

    public function getFirstTemplate()
    {
        return $this->data['first_template'];
    }

    public function getTemplates()
    {
        return $this->data['templates'];
    }

    public function getName()
    {
        return 'template';
    }
}
