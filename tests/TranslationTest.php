<?php
use PhpGettxt\MoParser;
use PhpGettxt\Translation;
use PhpGettxt\TranslationCache;
use PHPUnit\Framework\TestCase;


class TranslationTest extends TestCase {

  /**
   * Add PhpGettxt autoloader.
   *
   * @param $name
   * @param $data
   * @param $dataName
   */
  public function __construct($name = null, $data = [], $dataName = '') {
    include_once dirname(__DIR__) . '/autoload.php';
    parent::__construct($name, $data, $dataName);
  }

  /**
   * Returns a translation instance
   *
   * @param  string $filename
   * @return Translation
   */
  private function getTranslation($filename) {
    return new Translation(new TranslationCache(new MoParser($filename)));
  }

  /**
   * Get file list
   *
   * @param string $pattern  Path names pattern to match
   * @return array
   */
  private static function getFiles($pattern) {
    $files = glob($pattern);
    if ($files === false) {
      return [];
    }
    $result = [];
    foreach ($files as $file) {
      $result[] = [$file];
    }
    return $result;
  }


  /**
   * @return array
   */
  public static function dataProviderFiles() {
    return self::getFiles(__DIR__ . '/data/*.mo');
  }

  /**
   * @return array
   */
  public static function dataProviderErrorFiles() {
    return self::getFiles(__DIR__ . '/data/error/*.mo');
  }

  /**
   * @return array
   */
  public static function dataProviderUnTranslatedFiles() {
    return self::getFiles(__DIR__ . '/data/untranslated/*.mo');
  }

  /**
   * @dataProvider dataProviderFiles
   */
  public function testPluralTranslation($filename) {
    $translation = $this->getTranslation($filename);
    $expected2 = '%d sekundy';
    if (stripos($filename, 'invalid-plurals.mo') !== false || stripos($filename, 'less-plurals.mo') !== false) {
      $expected0 = '%d sekunda';
      $expected2 = '%d sekunda';
    } elseif (stripos($filename, 'plurals.mo') !== false || stripos($filename, 'noheader.mo') !== false) {
      $expected0 = '%d sekundy';
    } else {
      $expected0 = '%d sekund';
    }
    $this->assertEquals(
      $expected0,
      $translation->ngettxt('%d second', '%d seconds', 0)
    );
    $this->assertEquals(
      '%d sekunda',
      $translation->ngettxt('%d second', '%d seconds', 1)
    );
    $this->assertEquals(
      $expected2,
      $translation->ngettxt('%d second', '%d seconds', 2)
    );
    $this->assertEquals(
      $expected0,
      $translation->ngettxt('%d second', '%d seconds', 5)
    );
    $this->assertEquals(
      $expected0,
      $translation->ngettxt('%d second', '%d seconds', 10)
    );
    // Non existing string
    $this->assertEquals(
      '"%d" seconds',
      $translation->ngettxt('"%d" second', '"%d" seconds', 10)
    );
  }

  /**
   * @dataProvider dataProviderFiles
   */
  public function testTranslation($filename) {
    $translation = $this->getTranslation($filename);
    $this->assertEquals(
      'Pole',
      $translation->gettxt('Column')
    );
    // Non existing string
    $this->assertEquals(
      'Column parser',
      $translation->gettxt('Column parser')
    );
  }

  /**
   * @dataProvider dataProviderFiles
   */
  public function testContextTranslation($filename) {
    $translation = $this->getTranslation($filename);
    $this->assertEquals(
      'Tabulka',
      $translation->pgettxt(
        'Display format',
        'Table'
      )
    );
  }

  /**
   * @dataProvider dataProviderUnTranslatedFiles
   */
  public function testUnTranslated($filename) {
    $translation = $this->getTranslation($filename);
    $this->assertEquals(
      '%d second',
      $translation->ngettxt(
        '%d second',
        '%d seconds',
        1
      )
    );
  }

  /**
   * @param $filename
   * @return void
   * @dataProvider dataProviderErrorFiles
   */
  public function testEmptyFile($filename) {
    $parser = new MoParser($filename);
    $translation = new Translation(new TranslationCache($parser));
    if (stripos($filename, 'magic.mo') !== false) {
      $this->assertStringContainsStringIgnoringCase(
        'not a translation file', $parser->error
      );
    } else {
      $this->assertStringContainsStringIgnoringCase(
        'could not be read', $parser->error
      );
    }
    $this->assertEquals(
      'Table',
      $translation->pgettxt('Display format', 'Table')
    );
    $this->assertEquals(
      '"%d" seconds',
      $translation->ngettxt('"%d" second', '"%d" seconds', 10)
    );

  }

  /**
   * @dataProvider dataProviderFiles
   */
  public function testExists($filename) {
    $translation = $this->getTranslation($filename);
    $this->assertTrue($translation->exists('Column'));
    $this->assertFalse($translation->exists('Column parser'));
  }

}
