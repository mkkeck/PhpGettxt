<?php
/**
 * Gettext compatibility public methods for the Translator.
 *
 * @package     PhpGettxt
 * @author      Michael Keck, github@michaelkeck.de
 * @copyright   2023 Michael Keck, github@michaelkeck.de
 * @license     MIT License
 *
 * @noinspection ALL
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
use PhpGettxt\Translator;


/**
 * Get instance of Translator
 *
 * @return Translator
 */
function translator() {
  return Translator::getInstance();
}


/**
 * PHP compatible function to set the locale.
 *
 * @param  int|null     $category  Locale category, ignored
 * @param  string|null  $locale    Locale name
 * @return string       The defined locale name
 */
function _setlocale($category, $locale = null) {
  $args = func_get_args();
  return translator()->setLocale(array_pop($args));
}


/**
 * Gettext compatibility function to set the path for a domain.
 *
 * @param  string       $domain  The unique identifier for the translation
 * @param  string|null  $dir     The path where to find the translation
 *                               file.
 * @return string|false The direcotry, or <var>false</var> on failure
 */
function _bindtextdomain($domain = Translator::DEFAULT_DOMAIN, $dir = null) {
  return translator()->bindTextdomain($domain, $dir);
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
 */
function _bind_textdomain_codeset($domain, $codeset) {
  return translator()->bindTextdomainCodeset($domain, $codeset);
}


/**
 * Gettext compatibility function to set the default text-domain.
 *
 * @param  string       $domain  The unique identifier for the translation
 * @return string|null  The new domain or null if was not changed
 *
 * @see    get_textdomain()
 */
function _textdomain($domain) {
  return translator()->textdomain($domain);
}


/**
 * Gettext compatible function to translate a string.
 *
 * @param  string|string[]  $msgid  Original string to get its translations
 * @return string           The translated string
 */
function gettxt($msgid) {
  return translator()->getTranslation()->gettxt($msgid);
}


/**
 * Gettext compatible plural version of {@link gettxt()}.
 *
 * @param  string     $singular  Single form
 * @param  string     $plural    Plural form
 * @param  int|array  $count     Number of objects
 * @return string     Translated singular/plural form
 */
function ngettxt($singular, $plural, $count) {
  return translator()->getTranslation()->ngettxt($singular, $plural, $count);
}


/**
 * Gettext compatible function to translate a string
 * with context.
 *
 * @param  string           $msgctxt  Context
 * @param  string|string[]  $msgid    String to be translated
 * @return string           The translated string
 */
function pgettxt($msgctxt, $msgid) {
  return translator()->getTranslation()->pgettxt($msgctxt, $msgid);
}


/**
 * Gettext compatible plural version of {@link pgettxt()}
 *
 * @param  string    $msgctxt   Context
 * @param  string    $singular  Single form
 * @param  string    $plural    Plural form
 * @param  int|array $count     Number of objects
 * @return string               Translated singular/plural form
 */
function npgettxt($msgctxt, $singular, $plural, $count) {
  return translator()->getTranslation()->npgettxt($msgctxt, $singular, $plural, $count);
}


/**
 * Gettext compatible function to translate a string
 * from another domain.
 *
 * @param  string           $domain  The unique identifier for the translation
 * @param  string|string[]  $msgid   String to be translated
 * @return string           Translated string
 */
function dgettxt($domain, $msgid) {
  return translator()->getTranslation($domain)->gettxt($msgid);
}


/**
 * Plural version of {@link dgettxt()}.
 *
 * @param  string     $domain    The unique identifier for the translation
 * @param  string     $singular  Single form
 * @param  string     $plural    Plural form
 * @param  int|array  $count     Number of objects
 * @return string     Translated singular/plural form
 */
function dngettxt($domain, $singular, $plural, $count) {
  return translator()->getTranslation($domain)->ngettxt($singular, $plural, $count);
}


/**
 * Gettext compatible function to translate a string
 * with context from another domain.
 *
 * @param  string           $domain   The unique identifier for the translation
 * @param  string           $msgctxt  Context
 * @param  string|string[]  $msgid    String to be translated
 * @return string           Translated string
 */
function dpgettxt($domain, $msgctxt, $msgid) {
  return translator()->getTranslation($domain)->pgettxt($msgctxt, $msgid);
}


/**
 * Plural version of {@link dpgettxt()}.
 *
 * @param  string     $domain    The unique identifier for the translation
 * @param  string     $msgctxt   Context
 * @param  string     $singular  Single form
 * @param  string     $plural    Plural form
 * @param  int|array  $count     Number of objects
 * @return string     Translated singular/plural form
 */
function dnpgettxt($domain, $msgctxt, $singular, $plural, $count) {
  return translator()->getTranslation($domain)->npgettxt($msgctxt, $singular, $plural, $count);
}


/**
 * Translates a string.
 *
 * Examples:
 *
 * `__('Hello Universe');`
 * returns the translation for 'Hello Universe'.
 *
 * `__(['Hello %s', 'World']);`
 * returns the translation for 'Hello' where `%s` is replaced
 * with 'World' which results in 'Hello World'
 *
 * @param  string|string[]  $msgid
 *         String to be translated or an array of strings, where
 *         the 1st element is the string to be translated and
 *         following elements are the placeholders.
 * @param  string  $domain
 *         The unique identifier for the translation.
 *         If <b>$domain</b> empty the current domain
 *         is used.
 *
 * @return string  The translated string
 */
function __($msgid, $domain = null) {
  return translator()->getTranslation($domain)->gettxt($msgid);
}


/**
 * Translate a string with context.
 *
 * Examples:
 *
 * `_x('Hello Mars', 'Universe');`
 * returns the translation for 'Hello Mars' with context 'Universe'
 *
 * `_x(['Hello %s', 'World'], 'Universe');`
 * returns the translation for 'Hello' with context 'Universe'
 * where `%s` is replaced with 'World' wich results in 'Hello World'
 *
 * @param  string|string[]  $msgid
 *         String to be translated or an array of strings, where
 *         the 1st element is the string to be translated and
 *         following elements are the placeholders.
 * @param  string  $msgctxt
 *         Context of the string
 * @param  string  $domain
 *         The unique identifier for the translation.
 *         If <b>$domain</b> empty the current domain
 *         is used.
 *
 * @return string  The translated string
 */
function _x($msgid, $msgctxt, $domain = null) {
  return translator()->getTranslation($domain)->pgettxt($msgctxt, $msgid);
}


/**
 * Plural translate.
 *
 * Examples:
 *
 * `_n('One item', '%s items', 2);`
 * returns the translation for '%s items' (plural form for 2).
 *
 * To replace `%s` with the count, use e.g.
 * `sprintf(_n('One item', '%s items', 2), 2);`
 *
 * `_n('One item', '%s items', [2, '2']);`
 * returns the translation for '%s items' (plural form for 2),
 * where `%s` is replaced with '2' wich results in '2 items'
 *
 * @param  string  $singular
 *         Singular form to be translated if <b>$count</b> = 1
 * @param  string  $plural
 *         Plural form to be translated if <b>$count</b> != 1
 * @param  int|array  $count
 *         Numeric value or an array of values, where
 *         the 1st element is numeric value and
 *         following elements are the placeholders.
 * @param  string  $domain
 *         The unique identifier for the translation.
 *         If <b>$domain</b> empty the current domain
 *         is used.
 *
 * @return string  The translated singular/plural form
 */
function _n($singular, $plural, $count, $domain = null) {
  return translator()->getTranslation($domain)->ngettxt($singular, $plural, $count);
}


/**
 * Plural translate with context.
 *
 * Examples:
 *
 * `_nx('One star', '%s stars', 2, 'Universe');`
 * returns the translation for '%s stars' (plural form for 2)
 * with context 'Universe'
 *
 * To replace `%s` with the count, use e.g.
 * `sprintf(_nx('One star', '%s stars', 2, 'Universe'), 2);`
 *
 * `_nx('One star', '%s stars', [2, '2'], 'Universe');`
 * returns the translation for '%s stars' (plural form for 2),
 * with context 'Universe' where `%s` is replaced with '2'
 * wich results in '2 stars'
 *
 * @param  string  $singular
 *         Singular form to be translated if <b>$count</b> = 1
 * @param  string  $plural
 *         Plural form to be translated if <b>$count</b> != 1
 * @param  int|array  $count
 *         Numeric value or an array of values, where
 *         the 1st element is numeric value and
 *         following elements are the placeholders.
 * @param  string  $msgctxt
 *         Context of the string
 * @param  string  $domain
 *         The unique identifier for the translation.
 *         If <b>$domain</b> empty the current domain
 *         is used.
 *
 * @return string  The translated singular/plural form
 */
function _nx($singular, $plural, $count, $msgctxt, $domain = null) {
  return translator()->getTranslation($domain)->npgettxt($msgctxt, $singular, $plural, $count);
}


/**
 * Returns a marked string for translation.
 *
 * Examples:
 *
 * `__('Hello Universe');`
 * returns 'Hello Universe'.
 *
 * `__(['Hello %s', 'World']);`
 * returns 'Hello' where `%s` is replaced with 'World'
 * (results in 'Hello World')
 *
 * @param  string|string[]  $msgid
 *         String to be used or an array of strings, where
 *         the 1st element is the original string and
 *         following elements are the placeholders.
 * @param  string  $domain
 *         The unique identifier for the translation.
 *         If <b>$domain</b> empty the current domain
 *         is used.
 * @return string  The original string
 */
function noop_gettxt($msgid, $domain = null) {
  return translator()->getTranslation($domain)->noop_gettxt($msgid);
}


/**
 * Alias for {@link noop_gettxt()}.
 *
 * @param  string|string[]  $msgid
 * @param  string           $domain
 * @return string
 * @see    noop_gettxt()
 */
function noop__($msgid, $domain = null) {
  return translator()->getTranslation($domain)->noop_gettxt($msgid);
}


/**
 * Returns a marked plural form for translation.
 *
 * Examples:
 *
 * `noop_ngettxt('One item', '%s items', 2);`
 * returns '%s items' (plural form for 2)
 *
 * To replace `%s` with the count, use e.g.
 * `sprintf(_nx('One item', '%s items', 2), '2');`
 *
 * `noop_ngettxt('One item', '%s items', [2, '2'],);`
 * returns '%s items' (plural form for 2) where `%s`
 * is replaced with '2' wich results in '2 items'
 *
 * @param  string  $singular
 *         Singular form to be used if <b>$count</b> = 1
 * @param  string  $plural
 *         Plural form to be used if <b>$count</b> != 1
 * @param  int|array  $count
 *         Numeric value or an array of values, where
 *         the 1st element is numeric value and
 *         following elements are the placeholders.
 * @param  string  $domain
 *         The unique identifier for the translation.
 *         If <b>$domain</b> empty the current domain
 *         is used.
 * @return string  The plural or singular form
 */
function noop_ngettxt($singular, $plural, $count, $domain = null) {
  return translator()->getTranslation($domain)->noop_ngettxt($singular, $plural, $count);
}


/**
 * Alias for {@link noop_ngettxt()}.
 *
 * @param  string     $singular
 * @param  string     $plural
 * @param  int|array  $count
 * @param  string     $domain
 * @return string
 * @see    noop_ngettxt()
 */
function noop_n($singular, $plural, $count, $domain = null) {
  return translator()->getTranslation($domain)->noop_ngettxt($singular, $plural, $count);
}


/**
 * Defines the name to query or set the locale in `$GLOBALS`.
 *
 * @param  string $name  The name for the $GLOBALS
 * @return string        The defined name
 *
 * @see    get_translator_varname()
 */
function set_translator_varname($name) {
  return translator()->setVarname($name);
}


/**
 * Returns the defined name to query or set the locale in `$GLOBALS`.
 *
 * @return string  The defined name. The default value is 'locale' if
 *                 it was not defined.
 *
 * @see    set_translator_varname()
 */
function get_translator_varname() {
  return translator()->getVarname();
}


if (!function_exists('load_textdomain')) {
  /**
   * Alias for {@link _bindtextdomain()}.
   *
   * @param  string       $domain
   * @param  string|null  $dir
   * @return string|false
   */
  function load_textdomain($domain = Translator::DEFAULT_DOMAIN, $dir = null) {
    return translator()->bindTextdomain($domain, $dir);
  }
}


if (!function_exists('set_textdomain')) {
  /**
   * Alias for {@link _textdomain()}.
   *
   * @param  string       $domain  The unique identifier for the translation
   * @return string|null  The new domain or null if was not changed
   *
   * @see    _textdomain(), get_textdomain()
   */
  function set_textdomain($domain) {
    return translator()->setTextdomain($domain);
  }
}


if (!function_exists('get_textdomain')) {
  /**
   * Returns current used text-domain.
   *
   * @return string  The text-domain. Returns 'default' if
   *                 no text-domain was set.
   *
   * @see    _textdomain()
   */
  function get_textdomain() {
    return translator()->getTextdomain();
  }
}


if (!function_exists('detect_locale')) {
  /**
   * Detects configured locale.
   *
   * It checks:
   * - global locale variable: $GLOBALS['locale']'
   * - environment for LC_ALL, LC_MESSAGES and LAN
   *
   * @return string  With locale name. If it could not detect
   *                 <b>'en'</b> is used as fallback.
   *
   * @see    _setlocale(), get_accepted_locale(), get_locale(), set_locale()
   */
  function detect_locale() {
    return translator()->detectLocale();
  }
}


if (!function_exists('set_locale')) {
  /**
   * Alias for {@link _setlocale()} but accepts only parameter `$locale`.
   *
   * @param  string|null  $locale  Locale name
   * @return string       The defined locale name
   * @see    _setlocale(), detect_locale(), get_accepted_locale(), get_locale()
   */
  function set_locale($locale = null) {
    return translator()->setLocale($locale);
  }
}


if (!function_exists('get_locale')) {
  /**
   * Returns current used locale
   *
   * @return string  The current used locale, e.g. 'en_US'.
   *
   * @see    _setlocale(), detect_locale(), get_accepted_locale(), set_locale()
   */
  function get_locale() {
    return translator()->getLocale();
  }
}


if (!function_exists('set_locale_dir')) {
  /**
   * Sets the default locale directory where to find translation files.
   *
   * @param  string  $dir   The absolute dir where to find translation files.
   * @return string         The directory
   * @throws Exception - If directory does not exists or cannot be read.
   *
   * @see get_locale_dir()
   */
  function set_locale_dir($dir) {
    return translator()->setLocaleDir($dir);
  }
}


if (!function_exists('get_locale_dir')) {
  /**
   * Returns the default locale directory where to find translation files.
   *
   * @return string  Defined directory where to find the
   *                 translations files.
   *
   * @see    set_locale_dir()
   */
  function get_locale_dir() {
    return translator()->getLocaleDir();
  }
}


if (!function_exists('get_accepted_locale')) {
  /**
   * Get user agent's most preferred accepted language.
   *
   * @return string|null  The most preferred accepted language from the
   *                      user agent (browser). Otherwise <var>null</var>
   *                      if none defined.
   *
   * @see    _setlocale(), detect_locale(), get_locale(), set_locale()
   */
  function get_accepted_locale() {
    return translator()->getAcceptLocale();
  }
}

