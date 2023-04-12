<?php
namespace PhpGettxt;
use Exception;


/**
 * Translator API.
 *
 * @package     PhpGettxt
 * @author      Michael Keck, github@michaelkeck.de
 * @copyright   2023 Michael Keck, github@michaelkeck.de
 * @license     MIT License
 *
 * ------------------------------------------------------------------------
 * This class is heavy inspired and taken from the WordPress
 * project and its l10n-functions (https://wordpress.org)
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
class Translator {

  /**
   * @var string
   *      Default text domain
   */
  const DEFAULT_DOMAIN = 'default';

  /**
   * @var string
   *      Current used text-domain
   */
  private $domain = self::DEFAULT_DOMAIN;

  /**
   * @var Translation[]|string[]
   */
  private $translations = [];

  /**
   * @var string[]
   *      Bound directories for domains
   */
  private $directories = [];

  /**
   * @var string
   *      Defined locale.
   */
  private $locale = 'en';

  /**
   * @var string
   *      The variable name to query or set the
   *      locale in `$GLOBALS`.
   */
  private $varname = 'locale';

  /**
   * @var Translator
   *      Instance of this class
   */
  private static $_instance;


  /**
   * Figures out possible locale names, e.g. for 'de_DE' it would return
   * a list with 'de_DE', 'de-DE' and 'de'.
   *
   * @param  string $locale   Locale code
   * @return array            List of locales to try for different
   *                          language and country combinations
   */
  public function listLocales($locale) {
    $locales = [];
    if (!empty($locale) && is_string($locale)) {
      $matches = [];
      $language = null;
      $country = null;
      if (preg_match('/^([a-z]{2,3})(?:[\-_]([a-z]{2}))?(?:\.([\-\w]+))?(?:@([\-\w]+))?$/i', $locale, $matches)) {
        if (!empty($matches[1])) {
          $language = strtolower($matches[1]);
        }
        if (!empty($matches[2])) {
          $country = strtoupper($matches[2]);
        }
        if (!empty($country)) {
          $locales[] = sprintf('%s_%s', $language, $country);
          $locales[] = sprintf('%s-%s', $language, $country);
        }
        $locales[] = $language;
      }
    }
    return $locales;
  }


  /**
   * Validates a text-domain.
   *
   * @param  string  $domain  The text-domain to validate
   * @return string           The validated text-domain
   */
  private function getDomain($domain) {
    if (!is_string($domain) || $domain === '') {
      $domain = $this->domain;
    }
    return strtolower($domain);
  }


  /**
   * Loads a translator object for domain.
   *
   * @param  string $domain
   * @return Translation
   */
  private function loadTranslation($domain = null) {
    $domain = $this->getDomain($domain);
    if (!isset($this->translations[$this->locale])) {
      $this->translations[$this->locale] = [];
    }
    if (!isset($this->translations[$this->locale][$domain])) {
      $directory = './';
      if (isset($this->directories[$domain])) {
        $directory = $this->directories[$domain];
      }
      $directory = rtrim(str_replace('\\', '/', $directory), '/');
      $filename = null;
      $template = $domain !== self::DEFAULT_DOMAIN ? '%1$s/%3$s-%2$s.mo' : '%1$s/$2%s.mo';
      $locales = $this->listLocales($this->locale);
      foreach ($locales as $locale) {
        $filename = sprintf($template, $directory, $locale, $domain);
        if (file_exists($filename)) {
          break;
        }
      }
      $parser  = new MoParser($filename);
      $entries = new TranslationCache($parser);
      $this->translations[$this->locale][$domain] = new Translation($entries);
    }
    return $this->translations[$this->locale][$domain];
  }


  /**
   * Set a directory for a domain
   *
   * @param  string $domain  The unique identifier for the translation
   * @param  string $dir     The directory where to find locales
   * @return string|false
   */
  public function bindTextdomain($domain, $dir = null) {
    $domain = $this->getDomain($domain);
    if (!is_null($dir)) {
      $this->directories[$domain] = $dir;
      $this->loadTranslation($domain);
    }
    return isset($this->directories[$domain]) ? $this->directories[$domain] : false;
  }


  /**
   * Gettext compatibility function.
   *
   * It is assumed, that the same character set is used for input and
   * output. Generally it is recommended to use the UTF-8 character
   * set and to provide the MO-files in UTF-8.
   *
   * @param  string  $domain   The unique identifier for the translation
   * @param  string  $codeset  Character set to set
   * @return void
   */
  public function bindTextdomainCodeset($domain, $codeset) { }


  /**
   * Returns translation object for a domain
   *
   * @param  string $domain  The unique identifier for the translation
   * @return object
   */
  public function getTranslation($domain = null) {
    return $this->loadTranslation($domain);
  }


  /**
   * Defines a locale to use.
   *
   * @param  string $locale   Locale name
   * @return string           Set or current locale.
   */
  public function setLocale($locale) {
    if (preg_match('/^([a-z]{2,3})(?:[\-_]([a-z]{2}))?/i', $locale)) {
      $this->locale = $locale;
    }
    return $this->locale;
  }



  /**
   * Defines / returns the default text-domain.
   *
   * If <b>$domain</b> is <var>null</var> the current default domain
   * is returned.
   *
   * @param  string|null  $domain  Name of text-domain
   * @return string       Set or current domain.
   */
  public function textdomain($domain) {
    if (!is_string($domain)) {
      return $this->domain;
    }
    if ($domain === '' || $domain === self::DEFAULT_DOMAIN) {
      $domain = self::DEFAULT_DOMAIN;
    } else {
      $domain = $this->getDomain($domain);
    }
    if ($this->domain !== $domain) {
      $this->domain = $domain;
    }
    return $this->domain;
  }


  /**
   * Detects configured locale.
   *
   * It checks:
   *
   * - global locale variable: `$GLOBALS['locale']'`
   * - environment for `LC_ALL`, `LC_MESSAGES` and `LANG`
   *
   * @return string  With locale name. If it could not detect
   *                 <b>'en'</b> is used as fallback.
   * @uses   {@link self::$varname $varname}
   */
  public function detectLocale() {
    if (isset($GLOBALS[$this->varname])) {
      return $GLOBALS[$this->varname];
    }
    $locale = getenv('LC_ALL');
    if ($locale !== false) {
      return $locale;
    }
    $locale = getenv('LC_MESSAGES');
    if ($locale !== false) {
      return $locale;
    }
    $locale = getenv('LANG');
    if ($locale !== false) {
      return $locale;
    }
    return 'en';
  }


  /**
   * Get most preferred accepted language from user agent.
   *
   * @return string|null   The most preferred accepted language from
   *                       user agent (browser). Otherwise <var>null</var>
   *                       if none defined.
   */
  public function getAcceptLocale() {
    $accepted = '';
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      $accepted .= $_SERVER['HTTP_ACCEPT_LANGUAGE'];
    }
    if (!empty($accepted) && preg_match_all(
        '~([\w-]+)(?:[^,\d]+([\d.]+))?~',
        strtolower($accepted),
        $matches,
        PREG_SET_ORDER
      )) {
      $languages = [];
      foreach ($matches as $match) {
        $prior = (isset($match[2]) ? floatval($match[2]) : 1.0) * 100;
        $values = explode('-', $match[1]);
        if (!isset($values[1])) {
          $prior = $prior - 1;
        } else {
          $values[1] = strtoupper($values[1]);
        }
        $languages[$prior] = join('-', $values);
      }
      krsort($languages);
      return array_shift($languages);
    }
    return null;
  }


  /**
   * Defines the name to query or set the locale in `$GLOBALS`.
   *
   * @param  string  $name  The name of the query var
   * @return string  The defined query var
   */
  public function setVarname($name) {
    if (!empty($name)) {
      $this->varname = $name;
    }
    return $this->varname;
  }


  /**
   * Returns the defined name to query or set the locale in `$GLOBALS`.
   *
   * @return string
   */
  public function getVarname() {
    return $this->varname;
  }


  /**
   * Constructor.
   *
   * Defines initial values:
   * - `'en'` (for english) as initial local
   * - {@link self::DEFAULT_DOMAIN `DEFAULT_DOMAIN`} as initial text-domain
   *
   * @see {@link self::DEFAULT_DOMAIN DEFAULT_DOMAIN}
   * @see {@link self::setLocale() setLocale()}
   * @see {@link self::textdomain() textdomain()}
   */
  protected function __construct() {
    $this->setLocale('en');
    $this->textdomain(self::DEFAULT_DOMAIN);
  }


  /**
   * Registers global API functions e.g. __(), _n(), gettxt(),
   * ngettxt().
   *
   * @return void
   */
  public static function registerApi() {
    require_once __DIR__ . '/api.php';
  }


  /**
   * Returns an instance of Translator.
   *
   * @return Translator
   */
  public static function getInstance() {
    if (is_null(self::$_instance)) {
      self::$_instance = new self();
    }
    return self::$_instance;
  }
}
