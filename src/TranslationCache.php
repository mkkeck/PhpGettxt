<?php
namespace PhpGettxt;
use Exception;


/**
 * Translation Cache.
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
class TranslationCache {

  /**
   * @var bool|string
   *      Error handling
   */
  public $error = false;

  /**
   * @var array
   *      Cached translations where<br>
   *      - original string as key<br>
   *      - translated string as value
   */
  private $translations;


  /**
   * Checks if a translation for the original message does exist.
   *
   * @param  string $msgid  The original message to check for its
   *                        translation
   * @return bool           If translation exist <var>true</var>,
   *                        otherwise <var>false</var>
   */
  public function exists($msgid) {
    return array_key_exists($msgid, $this->translations);
  }


  /**
   * Returns if exist the cached translation,
   * otherwise the original message.
   *
   * @param  string $msgid    The original message
   * @return string           If exists the translation, otherwise
   *                          the original message
   */
  public function get($msgid) {
    if (array_key_exists($msgid, $this->translations)) {
      return $this->translations[$msgid];
    }
    return $msgid;
  }


  /**
   * Returns all cached translations.
   *
   * @return array
   */
  public function getAll() {
    return $this->translations;
  }



  /**
   * Pushes translated message for the original message into the cache.
   *
   * Example:
   * ```
   * $his->add('Hello', 'Hallo');
   * $his->add('Message', 'Nachricht');
   * ```
   *
   * @param  string $msgid   The original string
   * @param  string $msgstr  The translated string
   * @return void
   */
  public function set($msgid, $msgstr) {
    $this->translations[$msgid] = $msgstr;
  }


  /**
   * Populates cache with array of messages and their translations.
   *
   * Example:
   * ```
   * $his->set([
   *  'Hello'   => 'Hallo',
   *  'Message' => 'Nachricht'
   *  // ...
   * ]);
   * ```
   *
   * @param  array  $translations  Multidimensional array with original
   *                               string as key and translated string
   *                               as value.
   * @return void
   */
  public function setAll($translations) {
    $this->translations = $translations;
  }


  /**
   * Constructor
   *
   * @param  MoParser|null  $reader
   */
  public function __construct($reader = null) {
    $this->translations = [];
    if ($reader instanceof MoParser) {
      try {
        $reader->getContents($this);
      } catch (Exception $e) {
        $this->error = !empty($reader->error) ? $reader->error : false;
      }
    }
  }
}
