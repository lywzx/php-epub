<?php
namespace liuyang\epub;

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
     * @var type Files dir
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

    public function __construct( $filePath )
    {
        $this->filePath = $filePath;
        $this->zipArchive = new \ZipArchive();
    }

    /**
     *
     */
    public function read(){
        if ($this->zipArchive->open($this->filePath) ) {

            $this->_getOPF();
            $this->_getDcData();
            $this->_getManifest();
            $this->_getSpine();
            $this->_getTOC();
        }
    }

    // Private functions
    /**
     * Get the path to the OPF file from the META-INF/container.xml file
     * @return string Relative path to the OPF file
     */
    private function _getOPF() {
        $file = 'META-INF/container.xml';
        $buf = $this->_getFileContentFromZipArchive($file);
        $opfContents = simplexml_load_string($buf);
        $opfAttributes = $opfContents->rootfiles->rootfile->attributes();
        $this->opfFile = (string) $opfAttributes[0]; // Typecasting to string to get rid of the XML object

        // Set also the dir to the OPF (and ePub files)
        $opfDirParts = explode('/',$this->opfFile);
        unset($opfDirParts[(count($opfDirParts)-1)]); // remove the last part (it's the .opf file itself)
        $this->opfDir = implode('/',$opfDirParts);

        return $this->opfFile;
    }
    /**
     * Read the metadata DC details (title, author, etc.) from the OPF file
     */
    private function _getDcData() {
        $buf = $this->_getFileContentFromZipArchive($this->opfFile);
        $opfContents = simplexml_load_string($buf);
        $this->dcElements = (array) $opfContents->metadata->children('dc', true);
    }
    /**
     * Gets the manifest data from the OPF file
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
     */
    private function _getTOC() {
        $tocFile = $this->getManifest('ncx');
        $buf = $this->_getFileContentFromZipArchive($this->opfDir.'/'.$tocFile['href']);
        $tocContents = simplexml_load_string($buf);

        $toc = array();
        foreach($tocContents->navMap->navPoint AS $navPoint) {
            $navPointData = $navPoint->attributes();
            $toc[(string)$navPointData['playOrder']]['id'] = (string)$navPointData['id'];
            $toc[(string)$navPointData['playOrder']]['naam'] = (string)$navPoint->navLabel->text;
            $toc[(string)$navPointData['playOrder']]['src'] = (string)$navPoint->content->attributes();
        }

        $this->toc = $toc;
    }


    private function _getFileContentFromZipArchive($fileName) {
        $zip = $this->zipArchive;
        $fp = $zip->getStream($fileName);
        if (!$fp) {
            die("Error: can't get stream to epub file");
        }
        $stat = $zip->statName($fileName);

        $buf = ""; //file buffer
        ob_start(); //to capture CRC error message
        while (!feof($fp)) {
            $buf .= fread($fp, 2048); //reading more than 2156 bytes seems to disable internal CRC32 verification (bug?)
        }
        $s = ob_get_contents();
        ob_end_clean();
        if(stripos($s, "CRC error") != FALSE){
            echo 'CRC32 mismatch, current ';
            printf("%08X", crc32($buf)); //current CRC
            echo ', expected ';
            printf("%08X", $stat['crc']); //expected CRC
        }

        fclose($fp);

        return $buf;
    }

    /**
     * Get the specified manifest item
     * @param string $item The manifest ID
     * @return string/boolean String when manifest item exists, otherwise false
     */
    public function getManifest($item) {
        if(key_exists($item, $this->manifest)) {
            return $this->manifest[$item];
        } else {
            return false;
        }
    }

    /**
     * Get the specified DC item
     * @param string $item The DC Item key
     * @return string/boolean String when DC item exists, otherwise false
     */
    public function getDcItem($item) {
        if(key_exists($item, $this->dcElements)) {
            return $this->dcElements[$item];
        } else {
            return false;
        }
    }

    /**
     * Get the specified manifest by type
     * @param string $type The manifest type
     * @return string/boolean String when manifest item exists, otherwise false
     */
    public function getManifestByType($type) {
        foreach($this->manifest AS $manifestID => $manifest) {
            if($manifest['media-type'] == $type) {
                $return[$manifestID]['href'] = $manifest['href'];
                $return[$manifestID]['media-type'] = $manifest['media-type'];
            }
        }

        return (count($return) == 0) ? false : $return;
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
}