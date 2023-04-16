<?php
namespace PhpGettxt;
use Exception;


/**
 * Translation methods for loaded and parsed translations from a MO-file.
 *
 * @uses        MoPlurals
 * @uses        TranslationCache
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
 * - MoTranslator by phpMayAdmin<br>
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
class Translation {

  /**
   * @var int|null
   *      Number of plural forms
   */
  private $plural_count = null;

  /**
   * @var string|null
   *      Plural expression extracted from header field
   */
  private $plural_expr = null;

  /**
   * @var MoPlurals|null
   *      Evaluator for plurals
   */
  private $plural_forms = null;

  /**
   * @var TranslationCache
   *      Cached translation
   */
  private $entries;


  /**
   * Formats a string.
   *
   * @param  string $text
   * @param  array  $values
   * @return string
   */
  private function strformat($text, $values = []) {
    if (empty($values)) {
      return $text;
    }
    if (is_array($values[0])) {
      return strtr($text, $values[0]);
    }
    return vsprintf($text, $values);
  }


  /**
   * If not already done, get plural forms
   *
   * @return void
   */
  private function getPluralForms() {
    if (empty($this->plural_expr)) {
      $header = $this->entries->get('');
      $expr = $this->extractPluralsForms($header);
      $this->plural_expr  = $this->sanitizeExpression($expr);
      $this->plural_count = $this->extractPluralsCount($expr);
    }
  }


  /**
   * Sanitize plural form expression.
   *
   * @param  string $expr  Expression to sanitize
   * @return string        Sanitized plural form expression
   */
  public function sanitizeExpression($expr) {
    $parts = explode(';', $expr);
    $expr = count($parts) >= 2 ? $parts[1] : $parts[0];
    $expr = trim(strtolower($expr));
    // Strip plural prefix
    if (substr($expr, 0, 6) === 'plural') {
      $expr = ltrim(substr($expr, 6));
    }
    // Strip equals
    if (substr($expr, 0, 1) === '=') {
      $expr = ltrim(substr($expr, 1));
    }
    // Cleanup from unwanted chars
    $expr = preg_replace('@[^n0-9:()?=!<>/%&| ]@', '', $expr);
    return strval($expr);
  }


  /**
   * Extracts number of plurals from an expression
   * defining the plural form.
   *
   * @param  string  $expr  Expression to process
   * @return int            Total number of plurals
   */
  public function extractPluralsCount($expr) {
    $parts = explode(';', $expr, 2);
    $nplurals = explode('=', trim($parts[0]), 2);
    if (strtolower(rtrim($nplurals[0])) !== 'nplurals') {
      return 1;
    }
    if (count($nplurals) === 1) {
      return 1;
    }
    return intval($nplurals[1]);
  }


  /**
   * Parse full header and extract only lines with
   * plural information.
   */
  public function extractPluralsForms($header) {
    $headers = explode("\n", $header);
    $expr   = 'nplurals=2; plural=n == 1 ? 0 : 1;';
    foreach ($headers as $header) {
      if (stripos($header, 'Plural-Forms:') !== 0) {
        continue;
      }
      $expr = substr($header, 13);
    }
    return $expr;
  }


  /**
   * Count plural forms.
   *
   * @return int  Total number of plural forms
   */
  public function countPluralForms() {
    $this->getPluralForms();
    return $this->plural_count;
  }


  /**
   * Detects which plural form to take.
   *
   * @return int  Index of the right plural form
   */
  public function detectPluralForm($count) {
    $this->getPluralForms();
    if (!($this->plural_forms instanceof MoPlurals)) {
      $this->plural_forms = new MoPlurals();
    }
    try {
      $plural = $this->plural_forms->evaluate($this->plural_expr, $count);
    } catch (Exception $e) {
      $plural = 0;
    }
    if ($plural >= $this->plural_count) {
      $plural = $this->plural_count - 1;
    }
    return $plural;
  }


  /**
   * Adds a translation in place.
   *
   * @param  string  $msgid  Original string to be set
   * @param  string  $msgstr Translated string
   * @return void
   */
  public function setTranslation($msgid, $msgstr) {
    $this->entries->set($msgid, $msgstr);
  }


  /**
   * Sets all translations.
   *
   * @param  array  $translations  Array with original string as key
   *                               and translated string as value.
   * @return void
   */
  public function setTranslations($translations) {
    $this->entries->setAll($translations);
  }


  /**
   * Get all translations.
   *
   * @return array
   */
  public function getTranslations() {
    return $this->entries->getAll();
  }


  /**
   * Check if a string is translated.
   *
   * @param  string  $msgid  Original string  to be checked
   * @return bool  If exists <var>true</var>,
   *               otherwise <var>false</var>
   */
  public function exists($msgid) {
    return $this->entries->exists($msgid);
  }


  /**
   * Get translated string.
   *
   * @param  string  $msgid  Original string to get its translations
   * @return string  If found the translated string,
   *                 otherwise the original one
   */
  public function gettxt($msgid) {
    $values = $msgid;
    if (is_array($values)) {
      $msgid  = $values[0];
      $values = array_slice($values, 1);
    } else {
      $values = [];
    }
    return $this->strformat($this->entries->get($msgid), $values);
  }


  /**
   * Plural version of {@link self::gettxt() gettext()}.
   *
   * @param  string  $singular  Single form
   * @param  string  $plural    Plural form
   * @param  int     $count     Number of objects
   * @return string  If found the translated plural form,
   *                 otherwise the original one
   */
  public function ngettxt($singular, $plural, $count) {
    $values = $count;
    if (is_array($values)) {
      $count  = $values[0];
      $values = array_slice($values, 1);
    } else {
      $values = [];
    }
    $key = implode(chr(0), [$singular, $plural]);
    if (!$this->entries->exists($key)) {
       return $this->strformat($count !== 1 ? $plural : $singular, $values);
    }
    $cached = $this->entries->get($key);
    $select = $this->detectPluralForm($count);
    $list = explode(chr(0), $cached);
    if ($list === false) {
      return '';
    }
    if (!isset($list[$select])) {
      return $this->strformat($list[0], $values);
    }
    return $this->strformat($list[$select], $values);
  }


  /**
   * Translate with context.
   *
   * See {@link https://www.gnu.org/software/gettext/manual/html_node/Contexts.html Gettext nanual}
   * for further information.
   *
   * @param  string  $msgctxt  Context of the string
   * @param  string  $msgid    String to be translated
   * @return string  If found the translated string,
   *                 otherwise the original one
   */
  public function pgettxt($msgctxt, $msgid) {
    $values = $msgid;
    if (is_array($values)) {
      $msgid  = $values[0];
      $values = array_slice($values, 1);
    } else {
      $values = [];
    }
    $key = implode(chr(4), [$msgctxt, $msgid]);
    $ret = $this->gettxt($key);
    if (strpos($ret, chr(4)) !== false) {
      return $this->strformat($msgid, $values);
    }
    return $this->strformat($ret, $values);
  }


  /**
   * Plural version of {@link self::pgettxt() pgttext()}.
   *
   * See {@link https://www.gnu.org/software/gettext/manual/html_node/Contexts.html Gettext nanual}
   * for further information.
   *
   * @param  string  $msgctxt   Context of the string
   * @param  string  $singular  Single form
   * @param  string  $plural    Plural form
   * @param  int     $count     Number of objects
   * @return string  If found the translated plural form,
   *                 otherwise the original one
   */
  public function npgettxt($msgctxt, $singular, $plural, $count) {
    $values = $count;
    if (is_array($values)) {
      $count  = $values[0];
      $values = array_slice($values, 1);
    } else {
      $values = [];
    }
    $key = implode(chr(4), [$msgctxt, $singular]);
    $ret = $this->ngettxt($key, $plural, $count);
    if (strpos($ret, chr(4)) !== false) {
      return $this->strformat($count !== 1 ? $plural : $singular, $values);
    }
    return $this->strformat($ret, $values);
  }


  /**
   * Return string marked for translation
   *
   * @param  string  $msgid  String to be used
   * @return string          Original string
   */
  public function noop_gettxt($msgid) {
    $values = $msgid;
    if (is_array($values)) {
      $msgid  = $values[0];
      $values = array_slice($values, 1);
    } else {
      $values = [];
    }
    return $this->strformat($this->entries->get($msgid), $values);
  }


  /**
   * Plural version of {@link noop_gettxt()}
   *
   * @param  string  $singular  Single form
   * @param  string  $plural    Plural form
   * @param  int     $count     Number of objects
   * @return string  The original plural form
   */
  public function noop_ngettxt($singular, $plural, $count) {
    $values = $count;
    if (is_array($values)) {
      $count  = $values[0];
      $values = array_slice($values, 1);
    } else {
      $values = [];
    }
    return $this->strformat($count !== 1 ? $plural : $singular, $values);
  }


  /**
   * Constructor.
   *
   * @param  TranslationCache|string|null  $translations
   *         String for the mo-file to load, null for no file, or an
   *         instance of Translations
   */
  public function __construct($translations) {
    /** @var null|TranslationCache $cached */
    $cached = $translations;
    if (!($translations instanceof TranslationCache)) {
      $cached = new TranslationCache(new MoParser($translations));
    }
    $this->entries = $cached;
  }
}
