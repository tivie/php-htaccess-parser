PHP Htaccess Parser
===================

## Introduction
PHP Htaccess Parser is a small lightweight library that can parse (or tokenize, if you prefer) an apache .htaccess
file. It was developed for personal use to safely read and manipulate .htaccess files.


## Instalation
You can install it by cloning the git repository, ~~downloading the phar archive~~ (coming soon) or using composer.

### Git clone

    git clone https://github.com/tivie/php-htaccess-parser.git

### Composer
Add these lines to your composer.json:

    {
        "require": {
            "tivie/htaccess-parser": "*"
        }
    }

or run the following command:

    php composer.phar require tivie/htaccess-parser


## Usage

### Basic Usage
Using PHP Htaccess Parser is very simple.

    $file = new \SplFileObject('path/to/.htaccess');
    $parser = new \Tivie\HtaccessParser\Parser();
    $htaccess = $parser->parse($file);


### Options
The parser's behaviour can be changed through the following methods and/or options:

#### Setting the file
The parser uses \SplFileObject to access files. You can set the appropriate file in the constructor, by calling
`setFile()` or as the first parameter of `$parser->parse`

    $parser = new Parser(new \SplObjectStorage('/path/to/file');
    $parser->setFile(new \SplObjectStorage('/path/to/file');
    $parser->parse(new \SplObjectStorage('/path/to/file');


#### Changing the container
By default, `$parser->parse()` returns an HtaccessContainer object (which extends ArrayObject) that contains the
newly tokenized .htaccess file. You can change the returned object by calling `setContainer()`method:

    $parser->setContainer($myContainer);

`$myContainer` can be an array or an object that implements `ArrayAccess`.


#### Use arrays
The parser can tokenize:

 - Directives (example: `Options -MultiViews`)
 - Blocks and subBlocks (example: `<IfModule mod_headers.c>`)
 - Multiline statements (lines ended with `/`)
 - White lines
 - Comments

Each Token is an object that implements the `TokenInterface`, which presents several useful methods. However, if you
prefer, you can instruct the parser to use simple arrays instead by either:

 - calling the method `useArrays()`


    $parser->useArrays(true);

 - using the flag `USE_ARRAYS` qith the `parse` method.


    $parser->parse(null, USE_ARRAYS);


#### Ignoring White and Comment Lines
You can instruct the parser to ignore WhiteLines, CommentLines or both by:

  - calling the respective method


    $parser->ignoreWhiteLines(true)
           ->ignoreComments(true);

  - or set it as a flag in `$parser->parse`


    $parser->parse(null, IGNORE_WHITELINES|IGNORE_COMMENTS);

## License
PHP Htaccess Parser is released under Apache 2.0 license. For more information, please consult the
[LICENSE](https://github.com/tivie/php-htaccess-parser/blob/master/LICENSE) file in this repository or
http://www.apache.org/licenses/LICENSE-2.0.txt.
