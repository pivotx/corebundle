<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class CwrController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    public function cwrAction(Request $request, $file)
    {
        $code    = 404;
        $content = '<h1>File not found.</h1>';
        $headers = array();

        $directory = $this->get('kernel')->getCacheDir() . '/outputter/';
        $filename  = preg_replace('|[^a-zA-Z0-9_.-]|', '', $file);
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
                case 'png':
                    $headers['content-type'] = 'image/png';
                    break;
                case 'jpg':
                case 'jpeg':
                    $headers['content-type'] = 'image/jpeg';
                    break;
                case 'gif':
                    $headers['content-type'] = 'image/gif';
                    break;
            }
        }

        return new \Symfony\Component\HttpFoundation\Response($content, $code, $headers);
    }
}
