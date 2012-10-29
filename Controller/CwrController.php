<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CwrController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    public function cwrAction(Request $request, $name)
    {
        $code    = 404;
        $content = '<h1>File not found.</h1>';
        $headers = array();

        $directory = $this->get('kernel')->getCacheDir() . '/outputter/';
        $filename  = preg_replace('|[^a-zA-Z0-9_.-]|', '', $name);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        if (file_exists($directory.$filename)) {
            $code    = 200;
            $content = file_get_contents($directory.$filename);

            switch ($extension) {
                case 'js':
                    $headers['content-type'] = 'text/javascript';
                    break;
                case 'css':
                    $headers['content-type'] = 'text/css';
                    break;
            }
        }

        return new \Symfony\Component\HttpFoundation\Response($content, $code, $headers);
    }
}
