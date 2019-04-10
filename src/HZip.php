<?php
namespace lywzx\epub;

class HZip {
    /**
     * Add files and sub-directories in a folder to zip file.
     * @param string $folder
     * @param \ZipArchive $zipFile
     * @param int $exclusiveLength Number of text to be exclusived from the file path.
     */
    private static function folderToZip($folder, &$zipFile, $exclusiveLength) {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip.
                $localPath = substr($filePath, $exclusiveLength);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }

    /**
     * Zip a folder (include itself).
     * Usage:
     *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
     *
     * @param string $sourcePath Path of directory to be zip.
     * @param string $outZipPath Path of output zip file.
     */
    public static function zipDir($sourcePath, $outZipPath)
    {
        $pathInfo = pathInfo($sourcePath);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];

        $z = new \ZipArchive();
        $z->open($outZipPath, \ZipArchive::CREATE);
        $z->addEmptyDir($dirName);
        self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
        $z->close();
    }

    /**
     * Check if the file is encrypted
     *
     * Notice: if file doesn't exists or cannot be opened, function
     * also return false.
     *
     * @param string $pathToArchive
     * @return boolean return true if file is encrypted
     */
    public static function isEncryptedZip( $pathToArchive ) {
        $fp = @fopen( $pathToArchive, 'r' );
        $encrypted = false;
        if ( $fp && fseek( $fp, 6 ) == 0 ) {
            $string = fread( $fp, 2 );
            if ( false !== $string ) {
                $data = unpack("vgeneral", $string);
                $encrypted = $data[ 'general' ] & 0x01 ? true : false;
            }
            fclose( $fp );
        }
        return $encrypted;
    }
}