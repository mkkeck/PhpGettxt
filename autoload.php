<?php
namespace PhpGettxt;


/**
 * Autoload for PhpGettxt.
 *
 * @package     PhpGettxt
 * @author      Michael Keck, github@michaelkeck.de
 * @copyright   2023 Michael Keck, github@michaelkeck.de
 * @license     MIT License
 *
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
class Autoload {

  private static $_includes;
  private static $_namespace;

  private static function init() {
    spl_autoload_register(function($class) {
      if (preg_match('/^' . self::$_namespace . '(\S*)/i', $class, $match)) {
        $file = ltrim(str_replace('\\', '/', $match[1]), '/');
        $file = self::$_includes . '/src/' . $file . '.php';
        if (file_exists($file)) {
          require_once $file;
          return true;
        }
      }
      return false;
    });
  }


  /**
   * Register autoload.
   */
  public static function register() {
    self::$_namespace = preg_quote(__NAMESPACE__ . '\\', '/');
    self::$_includes = __DIR__;
    self::init();
  }
}

Autoload::register();
