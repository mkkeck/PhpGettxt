<?php
namespace PhpGettxt;
use Exception;


/**
 * Reads a MO-file, parse its content and caches.
 *
 * @uses        MoReader
 *
 * @package     PhpGettxt
 * @author      Michael Keck, github@michaelkeck.de
 * @copyright   2023 Michael Keck, github@michaelkeck.de
 * @license     MIT License
 *
 * ------------------------------------------------------------------------
 * This class is heavy based on
 * - php-gettext<br>
 *   Copyright (c) 2003, 2005, 2006, 2009 Danilo Segan, danilo@kvota.net
 * - MoTranslator by phpMyAdmin<br>
 *   Copyright (c) 2016 Michal Čihař, michal@cihar.com
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
class MoParser {

  /**
   * @var string
   *      Big endian mo-file magic bytes
   */
  const MAGIC_BE = "\x95\x04\x12\xde";

  /**
   * @var string
   *      Little endian mo-file magic bytes
   */
  const MAGIC_LE = "\xde\x12\x04\x95";

  /**
   * @var bool|string
   *      Error handling
   */
  public $error = false;

  /**
   * @var string|null
   *      MO-file to read
   */
  private $filename;


  /**
   * Reads and caches contents from a MO-file.
   *
   * @param  TranslationCache  $translations  Cached translations
   * @throws Exception  If filename was defined but could not be read.
   */
  public function getContents($translations) {
    if (is_null($this->filename)) {
      return;
    }
    $fileinfo = join('/', [
      basename(dirname($this->filename)),
      basename($this->filename)
    ]);
    if (!file_exists($this->filename)) {
      $this->error = sprintf('File "%s" does not exists', $fileinfo);
      throw new Exception($this->error);
    }
    if (!is_readable($this->filename)) {
      $this->error = sprintf('File "%s" could not be read, probably wrong permissions.', $fileinfo);
      throw new Exception($this->error);
    }
    try {
      $stream = new MoReader($this->filename);
      $magic = $stream->read(0, 4);
      if (strcmp($magic, self::MAGIC_LE) === 0) {
        $unpack = 'V';
      } elseif (strcmp($magic, self::MAGIC_BE) === 0) {
        $unpack = 'N';
      } else {
        $this->error = sprintf('File "%s" is not a translation file (Gettext MO-file).', $fileinfo);
        return;
      }
      // Parse mo-file header
      $total   = $stream->readint($unpack, 8);
      $indices = $stream->readint($unpack, 12);
      $strings = $stream->readint($unpack, 16);

      // read original and translation tables
      $totalTimes2 = intval($total * 2);
      $tblIndices  = $stream->readintarray($unpack, $indices, $totalTimes2);
      $tblStrings  = $stream->readintarray($unpack, $strings, $totalTimes2);

      // read all strings and cache
      for ($id = 0; $id < $total; $id++) {
        $idTimes2 = $id * 2;
        $idPlus1  = $idTimes2 + 1;
        $idPlus2  = $idTimes2 + 2;
        $msgid    = $stream->read($tblIndices[$idPlus2], $tblIndices[$idPlus1]);
        $msgstr   = $stream->read($tblStrings[$idPlus2], $tblStrings[$idPlus1]);
        $translations->set($msgid, $msgstr);
      }
    } catch (Exception $e) {
      $this->error = sprintf('File "%s" could not be read, probably wrong permissions.', $fileinfo);
      return;
    }
  }


  /**
   * Constructor.
   *
   * Initializes a new file reader for MO-files.
   *
   * @param  string|null  $filename  The mo-file to read
   */
  public function __construct($filename = null) {
    $this->filename = $filename;
  }
}
