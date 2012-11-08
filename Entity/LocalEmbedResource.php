<?php

namespace PivotX\CoreBundle\Entity;

/**
 */
class LocalEmbedResource extends EmbedResource
{
    protected $fileid;
    protected $filename;
    protected $filesize;

    /**
     * Set fileid
     *
     * @param string $fileid
     */
    public function setFileid($fileid)
    {
        $this->fileid = $fileid;
    }

    /**
     * Get fileid
     *
     * @return string 
     */
    public function getFileid()
    {
        return $this->fileid;
    }

    /**
     * Set filename
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Get filename
     *
     * @return string 
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set filesize
     *
     * @param string $filesize
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;
    }

    /**
     * Get filesize
     *
     * @return string 
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    public function getRealFilename()
    {
        list($fdir,$fid) = explode('-', $this->getFileid());
        $directory = 'data/genericresources/' . $fdir;

        return $directory . '/' . $fid . '.dat';
    }

    public function moveFile($tmp_name, $from_quarantaine = true)
    {
        if ($from_quarantaine) {
            $tmp_file = 'data/upload-quarantaine/'.$tmp_name;
        }
        else {
            $tmp_file = $tmp_name;
        }

        if (file_exists($tmp_file)) {
            list($fdir,$fid) = explode('-', $this->getFileid());
            $directory = 'data/genericresources/'.$fdir;

            if (!is_dir($directory)) {
                @mkdir($directory, 0777, true);
                @chmod($directory, 0777);
            }

            if (!is_dir($directory)) {
                // @todo throw some exception
                return false;
            }

            $new_file = $directory . '/' . $fid . '.dat';
            if (file_exists($new_file)) {
                unlink($new_file);
            }
            if (file_exists($new_file)) {
                // @todo throw some exception
                return false;
            }
            if (!rename($tmp_file, $new_file)) {
                // @todo throw some exception
                return false;
            }
        }

        return true;
    }

    public function updateImageInfo()
    {
        list($width,$height) = getimagesize($this->getRealFilename());

        $this->setWidth($width);
        $this->setHeight($height);
    }


    /**
     * Get the html to embed this
     */
    public function getHtml($inWidth = null, $inHeight = null, $options = null)
    {
        // @todo fix the source!
        //$src  = $this->getRealFilename();
        $src  = '/resource/'.$this->publicid;
        $alt  = '';

        $scaleMethod = 'keep-aspect';
        if (isset($options['scale'])) {
            $scaleMethod = $options['scale'];
            unset($options['scale']);
        }

        list($width, $height) = $this->determineWidthAndHeight($inWidth, $inHeight, $scaleMethod, $options);

        $html = '<img src="'.$src.'" width="'.$width.'" height="'.$height.'" alt="'.$alt.'" />';

        if (\PivotX\Component\Twig\Test::isTwigReturn()) {
            return new \Twig_Markup($html, 'utf-8');
        }
        return $html;
    }

    /**
     * Add a new resource
     * 
     * @param array $file   file to add
     *           ->mimetype
     *           ->size
     *           ->filename
     *           ->tmp_name
     */
    public function createNewResourceForField($file)
    {
        $this->filename = $file->name;

        if (isset($file->tmp_name)) {
            $this->media_type = $file->mimetype;
            $this->filesize   = $file->size;
            $this->fileid     = date('Ym') . '-' . md5(uniqid());

            // @todo should be better
            //       normalize filename?
            //       normalize mediatype?
            $this->publicid = mt_rand(1000,9999).'-'.trim(preg_replace('|[^a-z0-9.-]|','',mb_strtolower($this->filename)));

            $this->moveFile($file->tmp_name);

            $this->updateImageInfo();
        }
    }

    /**
     * Add a new resource from a local file
     * 
     * @param string $filename
     */
    public function createNewResourceForFile($file)
    {
        if (file_exists($file)) {
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $this->media_type = finfo_file($finfo, $file);
                finfo_close($finfo);
            }
            else if (function_exists('mime_content_type')) {
                $this->media_type = mime_content_type($file);
            }
            else {
                $this->media_type = 'application/octect-stream';

                if (preg_match('|[.]([^.]+)$|', $file, $match)) {
                    switch (mb_strtolower($match[1])) {
                        case 'jpg':
                        case 'jpeg':
                            $this->media_type = 'image/jpeg';
                            break;

                        case 'gif':
                            $this->media_type = 'image/gif';
                            break;

                        case 'png':
                            $this->media_type = 'image/png';
                            break;

                        case 'pdf':
                            $this->media_type = 'application/pdf';
                            break;

                        case 'txt':
                            $this->media_type = 'text/plain';
                            break;

                        case 'html':
                        case 'htm':
                            $this->media_type = 'text/html';
                            break;
                    }
                }
            }

            $this->filename   = basename($file);
            $this->filesize   = filesize($file);
            $this->fileid     = date('Ym') . '-' . md5(uniqid());

            $this->title    = preg_replace('|[^a-zA-Z0-9.-]|','',mb_strtolower($this->filename));
            $this->publicid = trim(preg_replace('|[^a-z0-9.-]|','',mb_strtolower($this->filename)));

            if ($this->publicid == '') {
                $this->publicid = null;
            }
            else {
                $this->publicid = mt_rand(1000,9999).'-'.$this->publicid;
            }

            $this->moveFile($file, false);

            $this->updateImageInfo();
        }
    }


    /**
     * Crud defaults
     */

    /**
     * @todo make a lifecycle event?
     */
    public function fixCrudBeforePersist()
    {
        if (substr($this->filename,0,5) == "[\n  {") {
            $json = json_decode($this->filename);

            $this->filename = $json[0]->name;

            if (isset($json[0]->tmp_name)) {
                $this->media_type = $json[0]->mimetype;
                $this->filesize   = $json[0]->size;
                $this->fileid     = date('Ym') . '-' . md5(uniqid());

                // @todo should be better
                //       normalize filename?
                //       normalize mediatype?
                $this->publicid = trim(preg_replace('|[^a-z0-9.-]|','',mb_strtolower($this->filename)));

                $this->moveFile($json[0]->tmp_name);

                $this->updateImageInfo();
            }
        }
    }

    /**
     * @todo do this all in the beforepersist (with random-id)
     * @todo make a lifecycle event?
     */
    public function fixCrudAfterPersist()
    {
    }

















































































































































































































































































































































    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-08, 14:19:17
     */
    public function getCrudConfiguration_publicid()
    {
        return array(
            'name' => 'publicid',
            'type' => false
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-08, 14:19:17
     */
    public function getCrudConfiguration_fileid()
    {
        return array(
            'name' => 'fileid',
            'type' => false
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-08, 14:19:17
     */
    public function getCrudConfiguration_filesize()
    {
        return array(
            'name' => 'filesize',
            'type' => false
        );
    }

    /**
     * Return the CRUD field configuration
     * 
     * @author PivotX Generator
     *
     * Generated on 2012-11-08, 14:19:17
     */
    public function getCrudConfiguration_filename()
    {
        $file_info = array(
            'valid' => true,
            'mimetype' => $this->media_type,
            'size' => $this->filesize,
            'name' => $this->filename
        );
        $file_info['json'] = json_encode($file_info);

        return array(
            'name' => 'filename',
            'type' => 'backend_file',
            'arguments' => array(
                'attr' => array('multiple' => false),
                'files' => array($file_info)
            )
        );
    }

}
