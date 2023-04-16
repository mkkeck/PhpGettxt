<?php
use PhpGettxt\Translator;
use PHPUnit\Framework\TestCase;


class TranslatorTest extends TestCase {

  /**
   * Add PhpGettxt autoloader.
   *
   * @param $name
   * @param $data
   * @param $dataName
   */
  public function __construct($name = null, $data = [], $dataName = '') {
    include_once dirname(__DIR__). '/autoload.php';
    parent::__construct($name, $data, $dataName);
  }


  /**
   * Test to sanitize and list locales
   *
   * @param string $locale
   * @param array  $expected
   * @dataProvider dataProviderListLocales
   */
  public function testListLocales($locale, $expected) {
    $this->assertEquals(
      $expected, Translator::getInstance()->listLocales($locale)
    );
  }

  /**
   * Data provider {@link self::testListLocales() testListLocales()}.
   *
   * @return array[]
   */
  public static function dataProviderListLocales(): array {
    return [
      ['sr_RS', ['sr_RS', 'sr-RS', 'sr',],],
      ['sr-RS', ['sr_RS', 'sr-RS', 'sr',],],
      ['sr', ['sr'],],
      ['sr.UTF-8', ['sr',],],
      ['sr_RS.UTF-8', ['sr_RS', 'sr-RS', 'sr',],],
      ['sr-RS.UTF-8', ['sr_RS', 'sr-RS', 'sr',],],
      ['sr-RS.utf8',  ['sr_RS', 'sr-RS', 'sr',],],
      ['sr_RS.UTF-8@latin', ['sr_RS', 'sr-RS', 'sr',],],
      ['sr.UTF-8@latin', ['sr',],],
      ['sr@latin', ['sr',],],
      ['something', [],], // not following the regular POSIX pattern return empty array
      ['', [],],          // empty string returns an empty array
    ];
  }


  /**
   * Test for global var
   *
   * @return void
   */
  public function testDetectGlobal() {
    $GLOBALS['locale'] = 'de_DE';
    $this->assertEquals('de_DE', Translator::getInstance()->detectLocale());
    unset($GLOBALS['locale']);
  }


  /**
   * Test for ENV-vars
   *
   * @return void
   */
  public function testDetectEnv() {
    $translator = Translator::getInstance();
    foreach (['LC_MESSAGES', 'LC_ALL', 'LANG'] as $var) {
      putenv($var);
      if (getenv($var) === false) {
        continue;
      }
      $this->markTestSkipped('Unsetting environment does not work');
    }
    unset($GLOBALS['lang']);
    putenv('LC_ALL=baz');
    $this->assertEquals(
      'baz',
      $translator->detectLocale()
    );
    putenv('LC_ALL');
    putenv('LC_MESSAGES=bar');
    $this->assertEquals(
      'bar',
      $translator->detectLocale()
    );
    putenv('LC_MESSAGES');
    putenv('LANG=barr');
    $this->assertEquals(
      'barr',
      $translator->detectLocale()
    );
    putenv('LANG');
    $this->assertEquals(
      'en',
      $translator->detectLocale()
    );
  }


  /**
   * Test instance
   *
   * @return void
   */
  public function testInstance() {
    $translator = Translator::getInstance();
    $translator->setLocale('cs');
    $translator->textdomain('pma');
    $translator->bindTextdomain('pma', __DIR__ . '/data/locale/');

    $translation = $translator->getTranslation();
    $this->assertEquals('Typ', $translation->gettxt('Type'));

    $translator = Translator::getInstance();
    $translation = $translator->getTranslation();
    $this->assertEquals('Typ', $translation->gettxt('Type'));


    $translator = Translator::getInstance();
    $translator->setLocale('be');
    $translator->bindTextdomain('pma', __DIR__ . '/data/locale/');
    $translation = $translator->getTranslation();
    $this->assertEquals('Тып', $translation->gettxt('Type'));
  }


  /**
   * Test for changing the locale
   *
   * @return void
   */
  public function testChangeLocale() {
    $translator = Translator::getInstance();
    $translator->setLocale('cs');
    $translator->textdomain('pma');
    $translator->bindTextdomain('pma', __DIR__ . '/data/locale/');

    $translation = $translator->getTranslation('pma');
    $this->assertEquals('Typ', $translation->gettxt('Type'));

    $translator->setLocale('be');

    $translation = $translator->getTranslation('pma');
    $this->assertEquals('Тып', $translation->gettxt('Type'));
  }


  /**
   * Simple test for retrieving translation from domains
   *
   * @param  string  $domain
   * @param  string  $locale
   * @param  string  $other
   * @param  string  $expected
   * @return void
   * @dataProvider dataProviderTranslation
   */
  public function testTranslation($domain, $locale, $other, $expected) {
    $translator = Translator::getInstance();
    $translator->setLocale($locale);
    $translator->textdomain($domain);
    $translator->bindTextdomain($domain, __DIR__ . '/data/locale/');
    $translation = $translator->getTranslation($other);
    $this->assertEquals(
      $expected,
      $translation->gettxt('Type')
    );
  }


  /**
   * Data provider for {@link self::testTranslation() testTranslation()}.
   *
   * @return array[]
   */
  public static function dataProviderTranslation() {
    return [
      ['pma', 'cs', '', 'Typ',],
      ['pma', 'be', '', 'Тып',],
      ['pma', 'cs', 'other', 'Type',],
      ['other', 'cs', 'pma', 'Typ',],
      ['pma', 'be', 'other', 'Type',],
      ['other', 'be', 'pma', 'Тып',],
    ];
  }

  public function testTextdomain() {
    $translator = Translator::getInstance();

    // set
    $domain  = 'test';
    $this->assertEquals(
      $domain, $translator->textdomain($domain)
    );

    // get
    $this->assertEquals(
      $domain, $translator->textdomain(null)
    );

    // reset
    $domain  = 'default';
    $this->assertEquals(
      $domain, $translator->textdomain('')
    );
  }

  /**
   * Test to get / set the varname to query globals for locale
   *
   * @return void
   */
  public function testVarname() {
    $translator = Translator::getInstance();

    // get default
    $name = 'locale';
    $this->assertEquals(
      $name, $translator->getVarname()
    );

    // set
    $name = 'lang';
    $this->assertEquals(
      $name, $translator->setVarname($name)
    );

    // get
    $this->assertEquals(
      $name, $translator->getVarname()
    );

    // reset
    $name = 'locale';
    $this->assertEquals(
      $name, $translator->setVarname($name)
    );
  }

}
