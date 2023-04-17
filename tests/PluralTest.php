<?php
use PhpGettxt\MoParser;
use PhpGettxt\Translation;
use PhpGettxt\TranslationCache;
use PHPUnit\Framework\TestCase;


class PluralTest extends TestCase {

  /**
   * Add PhpGettxt autoloader.
   */
  protected function setUp(): void {
    include_once dirname(__DIR__). '/autoload.php';
  }

  /**
   * Returns a translation instance.
   *
   * @return Translation
   */
  private function getTranslation() {
    return new Translation(new TranslationCache(new MoParser('')));
  }


  /**
   * @param  int     $number
   * @param  string  $expected
   * @return void
   * @dataProvider dataProviderNpgettxt
   */
  public function testNpgettxt(int $number, string $expected) {
    $translation = $this->getTranslation();
    $result = $translation->npgettxt(
      'context',
      "%d pig went to the market\n",
      "%d pigs went to the market\n",
      $number
    );
    $this->assertSame($expected, $result);
  }

  /**
   * Data provider for {@link self::testNpgettxt() testNpgettxt()}.
   *
   * @return array[]
   */
  public static function dataProviderNpgettxt() {
    return [
      [1, "%d pig went to the market\n",],
      [2, "%d pigs went to the market\n",],
    ];
  }


  /**
   * Test for ngettxt
   */
  public function testNgettxt() {
    $translation = $this->getTranslation();
    $translationKey = implode(chr(0), [
      "%d pig went to the market\n",
      "%d pigs went to the market\n"
    ]);
    $translation->setTranslation($translationKey, '');
    $result = $translation->ngettxt(
      "%d pig went to the market\n",
      "%d pigs went to the market\n",
      1
    );
    $this->assertSame('', $result);
  }

  /**
   * Simple test for plural forms
   *
   * @dataProvider dataProviderPluralForms
   */
  public function testPluralForms(string $pluralForms) {
    $translation = $this->getTranslation();
    $translation->setTranslation(
      '',
      "Project-Id-Version: PhpGettxt 1.0.0\n"
      . "Report-Msgid-Bugs-To: https://github.com/mkkeck/PhpGettxt/issues\n"
      . "Language: en_GB\n"
      . "MIME-Version: 1.0\n"
      . "Content-Type: text\/plain; charset=UTF-8\n"
      . "Content-Transfer-Encoding: 8bit\n"
      . $pluralForms . "\n"
    );
    $translationKey = implode(chr(0), [
      "%d pig went to the market\n",
      "%d pigs went to the market\n"
    ]);
    $translation->setTranslation($translationKey, 'ok');
    $result = $translation->ngettxt(
      "%d pig went to the market\n",
      "%d pigs went to the market\n",
      1
    );
    $this->assertSame('ok', $result);
  }

  /**
   * Data provider for {@link self::testPluralForms() testPluralForms()}
   *
   * @return array[]
   */
  public static function dataProviderPluralForms() {
    return [
      ['Plural-Forms: nplurals=2; plural=n != 1;'],
      ['Plural-Forms: nplurals=1; plural=0;'],
      ['Plural-Forms: nplurals=2; plural=(n > 1);'],
      [
        'Plural-Forms: nplurals=3; plural=n%10==1 && n%100!=11 ? 0 : n'
        . '%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2;',
      ],
      ['Plural-Forms: nplurals=2; plural=n >= 2 && (n < 11 || n > 99);'],
      ['Plural-Forms: nplurals=4; plural=n%100==1 ? 0 : n%100==2 ? 1 : n%100==3 || n%100==4 ? 2 : 3;'],
      ['Plural-Forms: nplurals=3; plural=n==1 ? 0 : (n==0 || (n%100 > 0 && n%100 < 20)) ? 1 : 2;'],
      [
        'Plural-Forms: nplurals=2; plural=n != 1 && n != 2 && n != 3 &'
        . '& (n % 10 == 4 || n % 10 == 6 || n % 10 == 9);',
      ],
    ];
  }

}
