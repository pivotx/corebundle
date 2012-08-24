<?php

namespace PivotX\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class GenericResourceController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    public function getDownloadAction(Request $request, $publicid)
    {
        //$entity_manager = $this->get('doctrine')->getEntityManager();
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
        }

        return new \Symfony\Component\HttpFoundation\Response($content, $code, $headers);
    }
}
