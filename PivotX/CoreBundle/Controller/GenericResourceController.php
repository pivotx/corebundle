<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class GenericResourceController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    public function getDownloadAction(Request $request, $options, $publicid)
    {
        $repository = $this->get('doctrine')->getRepository('PivotX\CoreBundle\Entity\LocalEmbedResource');
        $resource = $repository->findOneByPublicid($publicid);

        $code    = 404;
        $content = '<h1>File not found.</h1>';
        $headers = array();
        if ($resource instanceof \PivotX\CoreBundle\Entity\LocalEmbedResource) {
            $file = $resource->getRealFilename();
            if (is_file($file)) {
                $code    = 200;
                $content = file_get_contents($file);
                $headers = array( 'content-type' => $resource->getMediatype() );
            }

            switch ($options) {
                case 'download':
                    $headers['content-disposition'] = 'attachment; filename="'.$resource->getFilename().'"';
                    $headers['pragma'] = 'no-cache';
                    $headers['cache-control'] = 'no-cache, must-revalidate';
                    /*
                    header("Content-type: application/octet-stream");
                    header("Content-Disposition: inline; filename=\"".$name_mb."\"");
                    header("Content-length: ".(string)(filesize($path_mb)));
                    header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")+2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
                    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
                    header("Cache-Control: no-cache, must-revalidate");
                    header("Pragma: no-cache");  
                     */
                    break;

                case 'source':
                    // just return
                    break;
            }
        }

        return new Response($content, $code, $headers);
    }
}
