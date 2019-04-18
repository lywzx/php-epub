<?php
namespace lywzx\epub;

class EpubParser {

    /**
     * @var string epub file path
     */
    private $filePath;

    /**
     * @var array
     */
    private $manifest = [];

    /**
     * Holds the (relative to $ebookDir) path to the OPF file
     * @var string Location + name of OPF file
     */
    private $opfFile;

    /**
     * Relative (to $ebookDir) OPF (ePub files) dir
     * @var string Files dir
     */
    private $opfDir;

    /**
     * @var \ZipArchive
     */
    private $zipArchive;

    /**
     * Holds all the found DC elements in the OPF file
     * @var array All found DC elements in the OPF file
     */
    private $dcElements;

    /**
     * Holds all the spin data
     * @var array Spine data
     */
    private $spine;

    /**
     * Holds the ToC data
     * @var array Array with ToC items
     */
    private $toc;

    /**
     * image extract static root
     * @var null|string
     */
    private $imageWebRoot = null;

    /**
     *
     * @var null|string
     */
    private $linkWebRoot = null;

    /**
     * EpubParser constructor.
     * @param $filePath
     * @param null $imageWebRoot
     * @param null $linkWebRoot
     * @throws \Exception
     */
    public function __construct( $filePath, $imageWebRoot = null, $linkWebRoot = null )
    {
        $this->filePath = $filePath;
        $this->imageWebRoot = $imageWebRoot;
        $this->linkWebRoot  = $linkWebRoot;
        $this->zipArchive = new \ZipArchive();

        // check file
        $this->fileCheck();
    }



    /**
     * check file is validated
     * @throws \Exception
     */
    private function fileCheck() {
        $this->open();

        $mimetype = $this->_getFileContentFromZipArchive('mimetype');
        if (strtolower($mimetype) !== 'application/epub+zip') {
            throw new \Exception('The epub file is not validated');
        }

        $this->close();
    }


    /**
     * parse epub file info
     * @throws \Exception
     */
    public function parse(){
        $this->open();

        $this->_getOPF();
        $this->_getDcData();
        $this->_getManifest();
        $this->_getSpine();
        $this->_getTOC();

        $this->close();
    }

    // Private functions

    /**
     * Get the path to the OPF file from the META-INF/container.xml file
     * @return string Relative path to the OPF file
     * @throws \Exception
     */
    private function _getOPF() {
        $file = 'META-INF/container.xml';
        $buf = $this->_getFileContentFromZipArchive($file);
        $opfContents = simplexml_load_string($buf);
        $opfAttributes = $opfContents->rootfiles->rootfile->attributes();
        $this->opfFile = (string) $opfAttributes->{'full-path'}; // Typecasting to string to get rid of the XML object

        // Set also the dir to the OPF (and ePub files)
        $opfDirParts = explode('/',$this->opfFile);
        unset($opfDirParts[(count($opfDirParts)-1)]); // remove the last part (it's the .opf file itself)
        $this->opfDir = implode('/',$opfDirParts);

        return $this->opfFile;
    }

    /**
     * Read the metadata DC details (title, author, etc.) from the OPF file
     * @throws \Exception
     */
    private function _getDcData() {
        $buf = $this->_getFileContentFromZipArchive($this->opfFile);
        $opfContents = simplexml_load_string($buf);
        $this->dcElements = (array) $opfContents->metadata->children('dc', true);
    }

    /**
     * Gets the manifest data from the OPF file
     * @throws \Exception
     */
    private function _getManifest() {
        $buf = $this->_getFileContentFromZipArchive($this->opfFile);
        $opfContents = simplexml_load_string($buf);
        $iManifest = 0;
        foreach ($opfContents->manifest->item AS $item) {
            $attr = $item->attributes();
            $id = (string) $attr->id;
            $this->manifest[$id]['href'] = (string) $attr->href;
            $this->manifest[$id]['media-type'] = (string) $attr->{'media-type'};
            $iManifest++;
        }
    }

    /**
     * Get the spine data from the OPF file
     * @throws \Exception
     */
    private function _getSpine() {
        $buf = $this->_getFileContentFromZipArchive($this->opfFile);
        $opfContents = simplexml_load_string($buf);
        foreach ($opfContents->spine->itemref AS $item) {
            $attr = $item->attributes();
            $this->spine[] = (string) $attr->idref;
        }
    }


    /**
     * Build an array with the TOC
     * @throws \Exception
     */
    private function _getTOC() {
        $tocFile = $this->getManifest('ncx');
        $buf = $this->_getFileContentFromZipArchive($this->opfDir.'/'.$tocFile['href']);
        $tocContents = simplexml_load_string($buf);

        $callback = function($navPoints) use(& $callback) {
            $ret = [];
            foreach ($navPoints as $navPoint) {
                $attributes = $navPoint->attributes();
                $payOrder = (string) $attributes['playOrder'];
                $src = (string) $navPoint->content->attributes();
                $explodeUrl = strpos($src, "#") ? explode("#", $src) : [$src, null];
                $ret[$payOrder] = [
                    'id' => (string) $attributes['id'],
                    'naam' => (string) $navPoint->navLabel->text,
                    'file_name' => $explodeUrl[0],
                    'src'  => $src,
                    'page_id' => $explodeUrl[1]
                ];

                if (isset($navPoint->navPoint) && !empty($navPoint->navPoint)) {
                    $ret[$payOrder]['children'] = $callback($navPoint->navPoint);
                }
            }
            return $ret;
        };

        $toc = $callback($tocContents->navMap->navPoint);

        $this->toc = $toc;
    }

    /**
     * read file from Archive file
     * @param $fileName
     * @return string
     * @throws \Exception
     */
    private function _getFileContentFromZipArchive($fileName) {
        $fp = $this->zipArchive->getStream($fileName);
        if (!$fp) {
            throw new \Exception("Error: can't get stream to epub file");
        }
        //$stat = $zip->statName($fileName);

        $buf = ""; //file buffer
        ob_start(); //to capture CRC error message
        while (!feof($fp)) {
            $buf .= fread($fp, 2048); //reading more than 2156 bytes seems to disable internal CRC32 verification (bug?)
        }
        $s = ob_get_contents();
        ob_end_clean();
        if(stripos($s, "CRC error") != FALSE){
            throw new \Exception('CRC error');
            /*echo 'CRC32 mismatch, current ';
            printf("%08X", crc32($buf)); //current CRC
            echo ', expected ';
            printf("%08X", $stat['crc']); //expected CRC*/
        }
        fclose($fp);
        return $buf;
    }

    /**
     * Get the specified manifest item
     * @param string $item The manifest ID
     * @return string|boolean|array String when manifest item exists, otherwise false
     */
    public function getManifest($item = null) {
        if ( is_null($item) ) {
            return $this->manifest;
        } else if(is_string($item) && key_exists($item, $this->manifest)) {
            return $this->manifest[$item];
        } else {
            return false;
        }
    }

    /**
     * Get the specified DC item
     * @param string $item The DC Item key
     * @return string|boolean|array String when DC item exists, otherwise false
     */
    public function getDcItem($item = null) {
        if ( is_null($item) ) {
            return $this->dcElements;
        } else if(is_string($item) && key_exists($item, $this->dcElements)) {
            return $this->dcElements[$item];
        } else {
            return false;
        }
    }

    /**
     * Get the specified manifest by type
     * @param string $pattern The manifest type
     * @return string|boolean String when manifest item exists, otherwise false
     */
    public function getManifestByType($pattern) {
        $isRegExp =  @preg_match($pattern, '') !== FALSE;
        $ret = array_filter($this->manifest, function($manifest) use($pattern, $isRegExp) {
            if ($isRegExp) {
                return preg_match($pattern, $manifest['media-type']);
            }
            return $manifest['media-type'] === $pattern;
        });

        return (count($ret) == 0) ? false : $ret;
    }

    /**
     * start open epub file
     * @throws \Exception
     */
    private function open() {
        $zip_status = $this->zipArchive->open($this->filePath);
        if ( $zip_status !== true ) {
            throw new \Exception("Failed opening ebook: ". @$this->zipArchive->getStatusString(), $zip_status );
        }
    }

    /**
     * close epub file
     */
    private function close() {
        $this->zipArchive->close();
    }

    /**
     * Retrieve the ToC
     * @return array Array with ToC Data
     */
    public function getTOC() {
        return $this->toc;
    }

    /**
     * Returns the OPF/Data dir
     * @return string The OPF/data dir
     */
    public function getOPFDir() {
        return $this->opfDir;
    }

    /**
     * get chapter html text
     * @param $chapterId string chapterId
     * @return string
     * @throws \Exception
     */
    public function getChapter($chapterId) {
        $result = $this->getChapterRaw($chapterId);

        $path = explode('/', $this->opfDir);

        // remove linebreaks (no multi line matches in JS regex!)
        $result = preg_replace("/\r?\n/", "\u0000", $result);

        // keep only <body> contents
        $match = [];
        preg_match('/<body[^>]*?>(.*)<\/body[^>]*?>/i', $result, $match);
        $result = trim($match[1]);

        // remove <script> blocks if any
        $result = preg_replace('/<script[^>]*?>(.*?)<\/script[^>]*?>/i', '', $result);

        // remove <style> blocks if any
        $result = preg_replace('/<style[^>]*?>(.*?)<\/style[^>]*?>/i', '', $result);

        // remove onEvent handlers
        $result = preg_replace_callback('/(\s)(on\w+)(\s*=\s*["\']?[^"\'\s>]*?["\'\s>])/', function($matches){
            return $matches[1]."skip-".$matches[2].$matches[3];
        }, $result);

        // replace images
        $result = preg_replace_callback('/(\ssrc\s*=\s*["\']?)([^"\'\s>]*?)(["\'\s>])/', function($matches) use($path){
            $img = (new \ArrayObject($path))->getArrayCopy();
            $img[] = $matches[2];
            $img = implode('/', $img);

            $element = null;
            foreach ($this->manifest as $key => $value) {
                $mainestUrl = $this->opfDir.'/'.$value['href'];
                if ($mainestUrl === $img) {
                    $element = $value;
                    break;
                }
            }
            if (!is_null($element)) {
                return $matches[1].$this->imageWebRoot.'/'.$img.$matches[3];
            }
            return '';
        }, $result);

        $result = preg_replace_callback('/(\shref\s*=\s*["\']?)([^"\'\s>]*?)(["\'\s>])/', function($matches) use($path){
            $linkparts = isset($matches[2]) ? [] : explode($matches[2], "#");
            $link      = (new \ArrayObject($path))->getArrayCopy();
            $link[]    = array_shift($linkparts) ?? '';
            $link      = trim(implode($link, '/'));
            $element   = null;

            foreach ($this->manifest as $key => $value) {
                if(explode('#', $value['href'])[0] === $link) {
                    $element = $value;
                    break;
                }
            }

            if (count($linkparts)) {
                $link .= '#'.implode( '#',$linkparts);
            }

            // include only images from manifest
            if ($element) {
                return $matches[1].$this->linkWebRoot.$element['id']."/".$link.$matches[3];
            }
            return $matches[1].$matches[2].$matches[3];
        }, $result);

        return preg_replace("/\\\u0000/", "\n",  $result);
    }

    /**
     * get chapter html text
     * @param $chapterId string chapterId
     * @return string
     * @throws \Exception
     */
    public function getChapterRaw($chapterId) {
        if (isset($this->manifest[$chapterId])) {
            $chapter = $this->manifest[$chapterId];
            if (!($chapter['media-type'] == "application/xhtml+xml" || $chapter['media-type'] == "image/svg+xml")) {
                throw new \Exception('Invalid mime type for chapter');
            }
            $filePath = $chapter['href'];
            $this->open();
            $result = $this->_getFileContentFromZipArchive($this->opfDir.'/'.$filePath);
            $this->close();
            return $result;
        }
        throw new \Exception('File not found');
    }

    /**
     * @param $imageId
     * @return string
     * @throws \Exception
     */
    public function getImage($imageId) {
        if (isset($this->manifest[$imageId])) {
            $image = $this->manifest[$imageId];
            if (substr(trim(strtolower($image['media-type'] ?? "")),0, 6)  !=  "image/") {
                throw new \Exception("Invalid mime type for image");
            }
            $this->open();
            $result = $this->_getFileContentFromZipArchive($this->opfDir.'/'.$image['href']);
            $this->close();
            return $result;
        }
        throw new \Exception("file not found");
    }

    /**
     * @param $fileId
     * @return string
     * @throws \Exception
     */
    public function getFile($fileId) {
        if (isset($this->manifest[$fileId])) {
            $file = $this->manifest[$fileId];
            $this->open();
            try {
                $result = $this->_getFileContentFromZipArchive($this->opfDir . '/' . $file['href']);
            } catch (\Exception $e) {
            }
            $this->close();
            return $result;
        }
        throw new \Exception("file not found");
    }

    /**
     * @param $path the epub file extract destination
     * @param null|array|string $fileType file mimetype will extract or except
     * @param bool $except
     * @throws \Exception
     */
    public function extract($path, $fileType = null, $except = false) {
        if ( !is_dir($path) ) {
            throw new \Exception('invalid folder given!');
        }
        $this->open();

        $allMainfest = array_map(function($item) {
            return $this->opfDir.'/'.$item['href'];
        }, $this->manifest);
        $fileLimit = null;
        if ( !is_null($fileType) && is_string($fileType)) {
            $mainfest = $this->getManifestByType($fileType);
            if ( $mainfest !== false ) {
                $fileLimit = array_map(function($item) {
                    return $this->opfDir.'/'.$item['href'];
                }, $mainfest);
            }
        } else if (is_array($fileType)) {
            $fileLimit = $fileType;
        }

        if ($except === true && !is_null($fileLimit)) {
            $fileLimit = array_diff($allMainfest, $fileLimit);
        }

        if (is_null($fileLimit)) {
            $this->zipArchive->extractTo($path);
        } else {
            $this->zipArchive->extractTo($path, array_values($fileLimit));
        }

        $needReplacePath = array_values(array_map(function($item) {
            return $this->opfDir.'/'.$item['href'];
        }, array_filter($this->manifest, function($item) {
            return $item['media-type'] === 'application/xhtml+xml';
        })));


        if (!is_null($fileLimit)) {
            $needReplacePath = array_intersect($needReplacePath, $fileLimit);
        }

        foreach ($needReplacePath as $file) {
            $this->_replaceExtractFile( implode(DIRECTORY_SEPARATOR, [rtrim($path, '/'), $file]));
        }

        $this->close();
    }


    /**
     * @param $realPath
     * @throws \Exception
     */
    private function _replaceExtractFile($realPath) {
        if ( file_exists($realPath) && is_file($realPath) && is_readable($realPath) && is_writable($realPath)) {
            $str = file_get_contents($realPath);
            $path = explode('/', $this->opfDir);

            $str = preg_replace_callback('/(\ssrc\s*=\s*["\']?)([^"\'\s>]*?)(["\'\s>])/', function($matches) use($path){
                $img = (new \ArrayObject($path))->getArrayCopy();
                $img[] = $matches[2];
                $img = implode('/', $img);

                $element = null;
                foreach ($this->manifest as $key => $value) {
                    $mainestUrl = $this->opfDir.'/'.$value['href'];
                    if ($mainestUrl === $img) {
                        $element = $value;
                        break;
                    }
                }
                if (!is_null($element)) {
                    return $matches[1].$this->imageWebRoot.'/'.$img.$matches[3];
                }
                return '';
            }, $str);

            file_put_contents($realPath, $str);
        } else {
            throw new \Exception("change $realPath error");
        }
    }
}
