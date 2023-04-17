<?php
use PhpGettxt\Translator;
use PHPUnit\Framework\TestCase;


class ApiTest extends TestCase {

  protected $locale_dir;

  /**
   * Add PhpGettxt autoloader.
   *
   * @throws Exception
   */
  protected function setUp(): void {
    include_once dirname(__DIR__) . '/autoload.php';
    Translator::loadApi();
    $this->locale_dir = rtrim(str_replace('\\', '/', __DIR__ .'/data/locale/'), '/');
    set_locale_dir($this->locale_dir);
    _setlocale(0, 'cs');
    //_textdomain('pma');
    _bindtextdomain('pma');
    _bind_textdomain_codeset('pma', 'UTF-8');
  }

  public function testGettxt() {
    $this->assertEquals(
      'Typ', gettxt('Type')
    );
    $this->assertEquals(
      'Typ', __('Type')
    );
    $this->assertEquals(
      'Aktuální verze: 1.0.0',
      __(['Current version: %s', '1.0.0'])
    );
    $this->assertEquals(
      'Tabulka',
      pgettxt('Display format', 'Table')
    );
    $this->assertEquals(
      'Tabulka',
      _x('Table', 'Display format')
    );
    $this->assertEquals(
      'Current version: 1.0.0',
      _x(['Current version: %s', '1.0.0'], 'context')
    );
  }


  /**
   * Test plurals
   *
   * @return void
   */
  public function testNgettxt() {
    $this->assertEquals(
      '%d sekundy',
      ngettxt('%d second', '%d seconds', 2)
    );
    $this->assertEquals(
      '2 sekundy',
      _n('%d second', '%d seconds', [2, 2])
    );
    $this->assertEquals(
      '%d seconds',
      npgettxt('context', '%d second', '%d seconds', 2)
    );
    $this->assertEquals(
      '2 seconds',
      _nx('%d second', '%d seconds', [2, 2], 'context')
    );
  }


  /**
   * Test translations from another domain
   *
   * @return void
   */
  public function testDgettxt() {
    $this->assertEquals(
      'Typ',
      dgettxt('pma', 'Type')
    );
    $this->assertEquals(
      'Typ',
      __('Type', 'pma')
    );
    $this->assertEquals(
      '%d sekundy',
      dngettxt('pma', '%d second', '%d seconds', 2)
    );
    $this->assertEquals(
      '2 sekundy',
      _n('%d second', '%d seconds', [2, 2], 'pma')
    );
    $this->assertEquals(
      '%d seconds',
      dnpgettxt('pma', 'context', '%d second', '%d seconds', 2)
    );
    $this->assertEquals(
      '2 seconds',
      _nx('%d second', '%d seconds', [2, 2], 'context', 'pma')
    );
    $this->assertEquals(
      'Tabulka',
      dpgettxt('pma', 'Display format', 'Table')
    );
    $this->assertEquals(
      'Tabulka',
      _x('Table', 'Display format', 'pma')
    );
    $this->assertEquals(
      'Aktuální verze: 1.0.0',
      __(['Current version: %s', '1.0.0'], 'pma')
    );
    $this->assertEquals(
      'Current version: 1.0.0',
      _x(['Current version: %s', '1.0.0'], 'context', 'pma')
    );
  }


  public function testSetup() {
    $this->assertEquals('cs', _setlocale(0));
    $this->assertEquals('pma', _textdomain(null));
    $this->assertEquals('pma', get_textdomain());
    $this->assertEquals($this->locale_dir, get_locale_dir());

    $this->expectException(Exception::class);
    set_locale_dir(__DIR__ . '/foo');
  }
}
