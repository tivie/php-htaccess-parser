PHP Htaccess Parser [![SensioLabsInsight](https://insight.sensiolabs.com/projects/8bd10e2c-33d5-4800-8d19-e9be2362eeef/small.png)](https://insight.sensiolabs.com/projects/8bd10e2c-33d5-4800-8d19-e9be2362eeef)
===================
[![Build Status](https://travis-ci.org/tivie/php-htaccess-parser.svg)](https://travis-ci.org/tivie/php-htaccess-parser) [![Latest Stable Version](https://poser.pugx.org/tivie/htaccess-parser/v/stable.svg)](https://packagist.org/packages/tivie/htaccess-parser) [![License](https://poser.pugx.org/tivie/htaccess-parser/license.svg)](https://packagist.org/packages/tivie/htaccess-parser) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/8bd10e2c-33d5-4800-8d19-e9be2362eeef/mini.png)](https://insight.sensiolabs.com/projects/8bd10e2c-33d5-4800-8d19-e9be2362eeef)

A lightweight PHP htaccess parser

## Introduction
PHP Htaccess Parser is a small lightweight library that can parse (or tokenize, if you prefer) an apache .htaccess file. It was developed for personal use to safely read and manipulate .htaccess files.

## Features
The parser supports:

 - Directives (example: `Options -MultiViews`)
 - Blocks and subBlocks (example: `<IfModule mod_headers.c>`)
 - Multiline statements (lines ended with `/`)
 - White lines
 - Comments

## Installation
You can install it by cloning the git repository, ~~downloading the phar archive~~ (coming soon) or using composer.

### Git clone

    git clone https://github.com/tivie/php-htaccess-parser.git

### Composer
Add these lines to your composer.json:
```json
    {
        "require": {
            "tivie/htaccess-parser": "*"
        }
    }
```
or run the following command:

    php composer.phar require tivie/htaccess-parser


## How it works
Each line of the file .htaccess file is parsed and then converted into one of the following tokens:

 - [**WHITELINE**][4]
 - [**COMMENT**][5]
 - [**DIRECTIVE**][6]
 - [**BLOCK**][7]


Each token implements [**TokenInterface**][1] provides a simple api to read and modify its properties.

Tokens are aggregated in an [**HtaccessContainer**][2] that can be used to add, modify or remove Token objects and dumping it as plain text or json.


## Quick Usage guide
Using PHP Htaccess Parser is very simple.

```php
$file = new \SplFileObject('path/to/.htaccess');
$parser = new \Tivie\HtaccessParser\Parser();
$htaccess = $parser->parse($file);
```
You can then use `$htaccess` to manipulate .htaccess contents.

```php
$block = new Block();
$htaccess[0] = $block;
```

For instance, to print the first token of the file:

```php
echo $htaccess[0];
```

Casting a Token or an HtaccessContainer object to string returns a string representation of that element that can be used to recreate the .htaccess file.


```php
$output = (string) $htaccess;
file_put_content('.htaccess', $htaccess);
```

## Components

### The Parser

The [Parser class](https://github.com/tivie/php-htaccess-parser/blob/master/src/Parser.php) is the main component of the library. Since it's constructor doesn't require any mandatory argument, initializing a Parser object is very simple:

```php
$parser = new \Tivie\HtaccessParser\Parser();
```

### Parser Options
The parser's behavior can be changed through the following methods and/or options:

#### Setting the file
The parser uses \SplFileObject to access files. You can set the appropriate file in the constructor, by calling `setFile()` or as the first parameter of `$parser->parse`

```php
$parser = new Parser(new \SplObjectStorage('/path/to/file'));
$parser->setFile(new \SplObjectStorage('/path/to/file'));
$parser->parse(new \SplObjectStorage('/path/to/file'));
```

#### Changing the container
By default, `$parser->parse()` returns an [HtaccessContainer][2] object (which extends ArrayObject) that contains the newly tokenized .htaccess file. You can change the returned object by calling `setContainer()`method:

```php
$parser->setContainer($myContainer);
```

`$myContainer` can be an array or an object that implements `ArrayAccess`.


#### Use arrays
Each Token is an object that implements the `TokenInterface`, which presents several useful methods. However, if you prefer, you can instruct the parser to use simple arrays instead by either:

 - calling the method `useArrays()`

```php
$parser->useArrays(true);
```

 - using the flag `USE_ARRAYS` with the `parse` method.

```php
$parser->parse(null, USE_ARRAYS);
```

#### Ignoring White and Comment Lines
You can instruct the parser to ignore WhiteLines, CommentLines or both by:

 - calling the respective method

```php
$parser->ignoreWhiteLines(true)
       ->ignoreComments(true);
```

 - or set it as a flag in `$parser->parse`

```php
$parser->parse(null, IGNORE_WHITELINES|IGNORE_COMMENTS);
```

#### Rewind file
By default, prior to serialization, the Parser rewinds the file pointer to the beginning. You can override this by calling the `rewindFile` method.

```php
$parser->rewindFile(false);
```

#### Extending the Parser
The Parser class provides API points that developers can override. For more information, you can check the code at https://github.com/tivie/php-htaccess-parser/blob/master/src/Parser.php

-----

### HtaccessContainer Object

The default returned object ([HtaccessContainer][2]) implements [ArrayAccess](http://php.net/manual/en/class.arrayaccess.php), so you can access the definitions as you would with an array. The keys of this array are numeric and ordered by their appearance in the original file.


#### Retrieving a Token
Since the Parser returns an array or an array like object, you can retrieve a specific token by its index:

```php
$firstToken = $htaccess[0];
```

You can also use the `search` method to find a specific token by its name:

```php
$modRewrite = $htaccess->search('modRewrite');
```

You can constrain the search to a specific token type.

```php
$modRewrite = $htaccess->search('modRewrite', \Tivie\HtaccessParser\Token\TOKEN_BLOCK);
```

#### Modifying a Token
[**TokenInterface**][1] provides a common API that you can use to manipulate the tokens.

```php
$token->setName('fooBar'); //Changes the name of the Token
$token->setArguments('foo', 'bar', 'baz'); //Changes the Token arguments
```
Keep in mind, however, that with some tokens, an array of arguments doesn't make much sense. For instance, [**Comments Tokens**][5] only expect 1 argument (the actual text of the comment) while [**WhiteLine Tokens**][4] expect none so extra arguments will be **silently ignored**.

#### Adding a Token
You can add a token by simply creating and appending it.

```php
$newBlock = new Block('modRewrite');
$htaccess[] = $block;
```

You can also insert a Token into a specific index using `insertAt`:

```php
$newBlock = new Block('modRewrite');
$htaccess->insertAt(4, $newBlock);
```

#### Outputting htaccess
In order to output an .htaccess txt file, you can cast the [**HtaccessContainer**][2] to string and write the resulting string to a file:

```php
$output = (string) $htaccess;
file_put_content('.htaccess', $output);
```

You can also use the method `txtSerialize` to control how the output should be formatted:

```php
$output = $htaccess->ignoreWhiteLines(true)
                   ->ignoreComments(false);
file_put_content('.htaccess', $output);
```

NOTE: Keep in mind that ignored elements in the parser won't be available to HtaccessContainer serialize methods.


## Contribute
Feel free to contribute by forking or making suggestions.

Issue tracker: https://github.com/tivie/php-htaccess-parser/issues

Source code: https://github.com/tivie/php-htaccess-parser


## License
PHP Htaccess Parser is released under Apache 2.0 license. For more information, please consult the [LICENSE](https://github.com/tivie/php-htaccess-parser/blob/master/LICENSE) file in this repository or http://www.apache.org/licenses/LICENSE-2.0.txt.



[1]: https://github.com/tivie/php-htaccess-parser/blob/master/src/Token/TokenInterface.php
[2]: https://github.com/tivie/php-htaccess-parser/blob/master/src/HtaccessContainer.php
[4]: https://github.com/tivie/php-htaccess-parser/blob/master/src/Token/WhiteLine.php
[5]: https://github.com/tivie/php-htaccess-parser/blob/master/src/Token/Comment.php
[6]: https://github.com/tivie/php-htaccess-parser/blob/master/src/Token/Directive.php
[7]: https://github.com/tivie/php-htaccess-parser/blob/master/src/Token/Block.php
