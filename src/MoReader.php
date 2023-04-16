<?php
namespace PhpGettxt;
use Exception;


/**
 * String reader for GNU Gettext MO-file.
 *
 * @package     PhpGettxt
 * @author      Michael Keck, github@michaelkeck.de
 * @copyright   2023 Michael Keck, github@michaelkeck.de
 * @license     MIT License
 *
 * ------------------------------------------------------------------------
 * This package is inspired by
 *
 * - MoTranslator (https://github.com/phpmyadmin/motranslator)
 * - WordPress l10n-functions (https://wordpress.org)
 * ------------------------------------------------------------------------
 *
 * Copyright (c) 2023 Michael Keck, github@michaelkeck.de
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the
 * 'Software'), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * ------------------------------------------------------------------------
 */
class MoReader {

  /**
   * @var bool|string
   *      Error handling
   */
  public $error = false;

  /**
   * @var string
   *      Read file content string
   */
  private $string;

  /**
   * @var int
   *      Length of file content string
   */
  private $length;


  /**
   * Read number of bytes from given position.
   *
   * @param  int $pos
   * @param  int $bytes
   * @return string
   * @throws Exception
   */
  public function read($pos, $bytes) {
    $limit = $pos + $bytes;
    if ($limit > $this->length) {
      $this->error = __METHOD__ . ': Not enough bytes!';
      throw new Exception($this->error);
    }
    $data = substr($this->string, $pos, $bytes);
    return $data === false ? '' : $data;
  }


  /**
   * Reads a 32bit integer from the stream.
   *
   * @param  string  $unpack  Unpack string
   * @param  int     $pos     Position
   * @return int     Integer from the stream
   * @throws Exception
   * @see  {@link https://www.php.net/manual/en/function.unpack.php function unpack()}
   * @see  {@link https://www.php.net/manual/en/function.unpack.php#refsect1-function.unpack-notes Notes for function unpack()}
   */
  public function readint($unpack, $pos) {
    $data = unpack($unpack, $this->read($pos, 4));
    if ($data === false) {
      return PHP_INT_MAX;
    }
    $result = $data[1];
    return $result < 0 ? PHP_INT_MAX : $result;
  }


  /**
   * Reads an array of integers from the stream.
   *
   * @param  string  $unpack  Unpack string
   * @param  int     $pos     Position
   * @param  int     $limit   How many elements should be read
   * @return int[]   Array of Integers
   * @throws Exception
   */
  public function readintarray($unpack, $pos, $limit) {
    $data = unpack($unpack . $limit, $this->read($pos, 4 * $limit));
    if ($data === false) {
      return [];
    }
    return $data;
  }


  /**
   * Constructor.
   *
   * @param  string  $filename  The name of the file to read.
   */
  public function __construct($filename) {
    $this->string = '';
    $this->length = 0;
    $fileinfo = join('/', [
      basename(dirname($filename)),
      basename($filename)
    ]);
    if (!file_exists($filename)) {
      $this->error = __CLASS__ . ': ' . sprintf('File "%s" does not exists', $fileinfo);
      return;
    }
    $contents = file_get_contents($filename);
    if ($contents === false) {
      $this->error = __CLASS__ . ': ' . sprintf('File "%s" could not be read, probably wrong permissions.', $fileinfo);
      return;
    }
    $this->length = strlen($contents);
    $this->string = $contents;
  }
}
