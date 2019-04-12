# php-epub parser and maker

## About php-epub library
php-epub is a php library, for epub reader and maker. We can use it to read the ebook file and extract to cdn domain.

## Installition

### With Composer

>     composer require lywzx/php-epub


>     {
>        "require": {
>            "lywzx/php-epub": "^0.0.2"
>        }
>     }

>     require 'vendor/autoload.php'
>     
>     use lywzx\php-epub\EpubParser;
>
>     $epubParser = new EpubParser('./alice.epub');

### Without Composer

Why are you not using composer? Download the php-epub [latest release](https://github.com/lywzx/php-epub/releases) and put the contents of the ZIP archive into a directory in your project. Then require the file `autoload.php` to get all classes and dependencies loaded on need.

>     <?php
>           require_once 'path_to_php-epub_directory/autoload.php';
>         
>           use lywzx\php-epub\EpubParser;
>           
>           $epubParser = new EpubParser('./alice.epub');


## epub parser use example

get ebook metinfo

>     $epubParser = new EpubParser('./alice.epub');  // open file and check file is a validated epub
>    
>     $epubParser->parse();                          // parser file
>    
>     var_dump($epubParser->getTOC());                // you will get the book category tree, e.g 1.0
>    
>     var_dump($epubParser->getDcItem());              // you will get the book meta info, e.g 1.1
>
>     var_dump($epubParser->getDcItem('rights'))       // Public domain in the USA.

e.g 1.0

    array(2) {
      [1]=>
      array(4) {
        ["id"]=>
        string(4) "np-1"
        ["naam"]=>
        string(22) "THE "STORYLAND" SERIES"
        ["src"]=>
        string(65) "www.gutenberg.org@files@19033@19033-h@19033-h-0.htm#pgepubid00000"
        ["page_id"]=>
        string(13) "pgepubid00000"
      }
      [3]=>
      array(5) {
        ["id"]=>
        string(4) "np-2"
        ["naam"]=>
        string(32) "ALICE'S ADVENTURES IN WONDERLAND"
        ["src"]=>
        string(65) "www.gutenberg.org@files@19033@19033-h@19033-h-0.htm#pgepubid00002"
        ["page_id"]=>
        string(13) "pgepubid00002"
        ["children"]=>
        array(11) {
          [5]=>
          array(5) {
            ["id"]=>
            string(4) "np-3"
            ["naam"]=>
            string(37) "SAM'L GABRIEL SONS & COMPANY NEW YORK"
            ["src"]=>
            string(65) "www.gutenberg.org@files@19033@19033-h@19033-h-0.htm#pgepubid00004"
            ["page_id"]=>
            string(13) "pgepubid00004"
            ["children"]=>
            array(1) {
              [7]=>
              array(4) {
                ["id"]=>
                string(4) "np-4"
                ["naam"]=>
                string(57) "Copyright, 1916, by SAM'L GABRIEL SONS & COMPANY NEW YORK"
                ["src"]=>
                string(65) "www.gutenberg.org@files@19033@19033-h@19033-h-0.htm#pgepubid00006"
                ["page_id"]=>
                string(13) "pgepubid00006"
              }
            }
          }
        }
      }
    }
    
e.g 1.1

    array(9) {
      ["rights"]=>
      string(25) "Public domain in the USA."
      ["identifier"]=>
      string(37) "http://www.gutenberg.org/ebooks/19033"
      ["contributor"]=>
      string(15) "Gordon Robinson"
      ["creator"]=>
      string(13) "Lewis Carroll"
      ["title"]=>
      string(32) "Alice's Adventures in Wonderland"
      ["language"]=>
      string(2) "en"
      ["subject"]=>
      array(2) {
        [0]=>
        string(7) "Fantasy"
        [1]=>
        string(24) "Fantasy fiction, English"
      }
      ["date"]=>
      array(2) {
        [0]=>
        string(10) "2006-08-12"
        [1]=>
        string(32) "2010-02-16T12:34:12.754941+00:00"
      }
      ["source"]=>
      string(56) "http://www.gutenberg.org/files/19033/19033-h/19033-h.htm"
    }

more example you can view example directory.


## epub maker is in development.
document is on the way.

