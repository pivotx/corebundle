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
class CopyOutput extends Output
{
    private $source;
    private $destination;

    /**
     * Constructor
     *
     * @param string $source
     * @param string $destination
     */
    public function __construct($source, $destination)
    {
        $this->type    = false;
        $this->content = '';

        $this->source      = $source;
        $this->destination = $destination;
    }

    public function allowConcat()
    {
        return false;
    }

    public function getHtml($temp_directory, $routing_service, $site, $target, $version)
    {
        $src_dir     = $this->source . '/';
        $dest_dir    = $temp_directory . '/' . $this->destination;
        $directories = array('');

        for($dcnt=0; $dcnt < count($directories); $dcnt++) {
            $directory = $directories[$dcnt];
            $files     = scandir($src_dir.$directory);

            if (!is_dir($dest_dir.$directory)) {
                @mkdir($dest_dir.$directory, 0777);
                chmod($dest_dir.$directory, 0777);
            }

            foreach($files as $file) {
                if (substr($file, 0, 1) != '.') {
                    if (is_dir($src_dir.$directory.'/'.$file)) {
                        $directories[] = $directory .'/'. $file;
                    }
                    else {
                        copy($src_dir.$directory.'/'.$file, $dest_dir.'/'.$directory.'/'.$file);
                        chmod($dest_dir.'/'.$directory.'/'.$file, 0666);
                    }
                }
            }
        }

        return '';
    }
}
