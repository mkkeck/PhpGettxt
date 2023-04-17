<?php
use PhpGettxt\Translation;
use PhpGettxt\Translator;
use PHPUnit\Framework\TestCase;


class PluralFormTest extends TestCase {

  /**
   * Add PhpGettxt autoloader.
   */
  protected function setUp(): void {
    include_once dirname(__DIR__). '/autoload.php';
  }

  /**
   * Returns a translation instance
   *
   * @return Translation
   */
  private function getTranslation() {
    $translator = Translator::getInstance();
    return $translator->getTranslation();
  }


  /**
   * Test for extracting plural forms.
   *
   * @param  string  $header
   * @param  string  $expected
   * @return void
   * @dataProvider dataProviderPluralForms
   */
  public function testPluralsForms($header, $expected) {
    $result = $this->getTranslation()->extractPluralsForms($header);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for {@link self::testPluralsForms() testExtractPluralsForms()}
   *
   * @return array[]
   */
  public static function dataProviderPluralForms() {
    return [
      // It defaults to a "Western-style" plural header.
      [
        '',
        'nplurals=2; plural=n == 1 ? 0 : 1;',
      ],
      // Extracting it from the middle of the header works.
      [
        "Content-type: text/html; charset=UTF-8\n"
        . "Plural-Forms: nplurals=1; plural=0;\n"
        . "Last-Translator: nobody\n",
        ' nplurals=1; plural=0;',
      ],
      // It's also case-insensitive.
      [
        "PLURAL-forms: nplurals=1; plural=0;\n",
        ' nplurals=1; plural=0;',
      ],
      // It falls back to default if it's not on a separate line.
      [
        'Content-type: text/html; charset=UTF-8' // note the missing \n here
        . "Plural-Forms: nplurals=1; plural=0;\n"
        . "Last-Translator: nobody\n",
        'nplurals=2; plural=n == 1 ? 0 : 1;',
      ],
    ];
  }


  /**
   * Test for extracting number of plural forms.
   *
   * @param  string  $expr
   * @param  int     $expected
   * @return void
   * @dataProvider dataProviderPluralCounts
   */
  public function testPluralCounts(string $expr, int $expected) {
    $result = $this->getTranslation()->extractPluralsCount($expr);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for {@link self::testPluralCounts() testPluralCounts()}
   * @return array[]
   */
  public static function dataProviderPluralCounts() {
    return [
      ['', 1,],
      ['foo=2; expr', 1,],
      ['nplurals=2; epxr', 2,],
      [' nplurals = 3 ; epxr', 3,],
      [' nplurals = 4 ; epxr ; ', 4,],
      ['nplurals', 1,],
    ];
  }


  /**
   * Test plural expression and sanitization.
   *
   * @param  string  $expr
   * @param  string  $expected
   * @return void
   * @dataProvider dataProviderPluralExpressions
   */
  public function testPluralExpression(string $expr, string $expected) {
    $result = $this->getTranslation()->sanitizeExpression($expr);
    $this->assertEquals($expected, $result);
  }

  /**
   * Data provider for {@link self::testPluralExpression() testPluralExpression()}.
   *
   * @return array[]
   */
  public static function dataProviderPluralExpressions() {
    return [
      [
        '', // test
        '', // expected
      ],
      [
        'nplurals=2; plural=n == 1 ? 0 : 1;',
        'n == 1 ? 0 : 1',
      ],
      [
        ' nplurals=1; plural=0;',
        '0',
      ],
      [
        "nplurals=6; plural=n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 ? 4 : 5;\n",
        'n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 ? 4 : 5',
      ],
      [
        ' nplurals=1; plural=baz(n);',
        '(n)',
      ],
      [
        ' plural=n',
        'n',
      ],
      [
        'nplurals',
        'n',
      ],
    ];
  }

}
