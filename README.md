# PhpGettxt

Simple and dependency free translation API for PHP using GNU Gettext MO-Files.


## About

This library is based on [MoTranslator](https://github.com/phpmyadmin/motranslator) and
inspired on WordPress translation functions, but without dependency on third party
libraries or frameworks.

### Why a new Gettext library?

- The [php-gettext](https://launchpad.net/php-gettext) library is not maintained anymore
- Other libraries depend on third-party libraries or build on third-party frameworks.
- There should be a simple "copy and run" solution.

### Why not using native gettext in PHP?

You cannot use locales not known to system, what is something you cannot control from
web application.

### Why not using JSON, YAML, PHP-Arrays or whatever?

Translators should be able to use their favorite tools available with Gettext (e.g. PoEdit)
as well such as web based [translations using Weblate](https://weblate.org/).
Using custom formats for translations may add another barrier for translators.


## Features

- Does not depend on third-party libraries or frameworks
- Simple "copy and run"
- Fast loading GNU Gettext MO-Files
- Translations are stored in memory for fast lookup
- GNU Gettext compatible API functions: for example instead of `gettext()` use `gettxt()`
- WordPress compatible API functions: `__()`, `_n()`, `_x()`, `_nx()`, `load_textdomain()`


## Limitations

Input, output and file encodings should match. Preferably UTF-8 should be used.


## Object-oriented API usage

### Low level API usage 

```php
// include PhpGettxt
require_once 'PhpGettxt/autoload.php';

// use PhpGettxt
use PhpGettxt\MoParser;
use PhpGettxt\TranslationCache;
use PhpGettxt\Translation;

// Load and parse a MO-file 
$parse = new MoParser('/path/to/file.mo');

// Cache result
$cache = new TranslationCache($parse);

// Instantiate the Translation
$translation = new Translation($cache);
```

### Translator API usage

```php
// ...

// Get instance
use PhpGettxt\Translation;$translator = Translator::getInstance();

// Set locale
$locale = $translator->setLocale('de_DE');
// returns 'de_DE'

// Get locale
$locale = $translator->setLocale();
// returns 'de_DE'

// Alternative get locale
$locale = $translator->getLocale();
// returns 'de_DE'

// Override the name of $GLOBALS to query or set the locale 
$var_name = $translator->setVarname('locale');
// returns 'locale': for $GLOBALS['locale']

// Get current defined name of $GLOBALS to query or set the locale 
$var_name = $translator->getVarname();
// returns 'locale': for $GLOBALS['locale']


// Detects configured locale. It checks:
// - global locale variable: $GLOBALS['locale']
// - environment for `LC_ALL`, `LC_MESSAGES` and `LANG`
$detected_locale = $translator->detectLocale();
// returns the locale name if defined (e.g. 'de_DE'), otherwise 'en'.

$browser_locale = $translator->getAcceptLocale();
// returns the most preferred accepted locale from browser,
// e.g. 'de_DE', or null if none found

// Sets the default directory where to find translation files.
$locale_dir = $translator->setLocaleDir(__DIR__ . '/locales/');

// Sets domain to library default
$translator->setTextdomain();
// or $translator->setTextdomain('default')
// or $translator->setTextdomain('')
// or $translator->setTextdomain(null)

// Sets domain
$domain = $translator->setTextdomain('myapp');
// returns 'myapp'

// Get current domain
$domain = $translator->getTextdomain();
// returns 'myapp'

// Gettext compatible method: Sets domain to library default
$domain = $translator->textdomain('');
// or $translator->textdomain('default');

// Gettext compatible method: Sets the current domain to 'myapp'
$domain = $translator->textdomain('myapp');

// Gettext compatible method: Get current domain
$domain = $translator->textdomain();
// returns the current defined domain

// Sets the path where to find translation files for
// the 'default' domain. The files have to be named
// like 'de.mo', ''de_DE.mo', 'de_AT.mo'.
$translator->bindTextdomain();
// or:
// $translator->bindTextdomain(Translator::DEFAULT_DOMAIN, $locale_dir);

// Binding another domain
// On binding another domain, e.g. 'myapp', then the files have to
// be named like 'myapp-de.mo', 'myapp-de_DE.mo', 'myapp-de_AT.mo'.
$translator->bindTextdomain('myapp', __DIR__ . '/locales/');

// Get default translation 
$translation = $translator->getTranslation();

// Get translation from another domain
$myapp = $translator->getTranslation('myapp');

// Load global functions API
Translator::loadApi();
```


### Translation API usage

Translate a string

```php
// ...
// Translate 'Hello World'
echo $translation->gettxt('Hello World');

// Method 1: Translate 'Hello %s' and replace '%s' with 'John'
echo sprintf($translation->gettxt('Hello %s'), 'John');

// Method 2: Translate 'Hello %s' and replace '%s' with 'John'
echo $translation->gettxt(['Hello %s', 'John']);
```

Translate a string with context

```php
// ...
// Translate 'Hello World'
echo $translation->pgettxt('Context', 'Hello World');

// Method 1: Translate 'Hello %s' and replace '%s' with 'John'
echo sprintf($translation->pgettxt('Context', 'Hello %s'), 'John');

// Method 2: Translate 'Hello %s' and replace '%s' with 'John'
echo $translation->pgettxt('Context', ['Hello %s', 'John']);
```

Return a string marked for translation

```php
// ...
// Returns 'Hello World'
echo $translation->noop_gettxt('Hello World');

// Method 1: Returns 'Hello %s' and replace '%s' with 'John'
echo sprintf($translation->noop_gettxt('Hello %s'), 'John');

// Method 2: Returns 'Hello %s' and replace '%s' with 'John'
echo $translation->noop_gettxt(['Hello %s', 'John']);
```

Translate a plural form

```php
// ...
// Method 1: returns translation for 'Found %d items'
echo $translation->ngettxt('Found one item', 'Found %d items', 2);

// Method 2: returns translation for 'Found %d items' and replace '%d' with 2
echo sprintf($translation->ngettxt('Found one item', 'Found %d items', 2), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo $translation->ngettxt('Found one item', 'Found %d items', [2, 2]);
```

Translate a plural form with context

```php
// ...
// Method 1: returns translation for 'Found %d items'
echo $translation->npgettxt('Context', 'Found one item', 'Found %d items', 2);

// Method 2: returns translation for 'Found %d items' and replace '%d' with 2
echo sprintf($translation->npgettxt('Context', 'Found one item', 'Found %d items', 2), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo $translation->npgettxt('Context', 'Found one item', 'Found %d items', [2, 2]);
```

Return plural form marked for translation

```php
// ...
// Method 1: returns 'Found %d items'
echo $translation->noop_ngettxt('Found one item', 'Found %d items', 2);

// Method 2: returns 'Found %d items' and replace '%d' with 2
echo sprintf($translation->noop_ngettxt('Found one item', 'Found %d items', 2), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo $translation->noop_ngettxt('Found one item', 'Found %d items', [2, 2]);
```


## Gettext compatibility usage

```php
// include PhpGettxt
require_once 'PhpGettxt/autoload.php';

// use PhpGettxt
use PhpGettxt\Translator;

// Load compatibility layer
Translator::loadApi();

// Configure locale
_setlocale(0, 'de');
// or: set_locale('de');

// Configure default text domain
_textdomain(Translator::DEFAULT_DOMAIN);
// or: set_textdomain();

_bindtextdomain(Translator::DEFAULT_DOMAIN, __DIR__ . '/locales/');
// or: load_textdomain(Translator::DEFAULT_DOMAIN,  __DIR__ . '/locales/');

// Gettext compatibility only, does nothing
//_bind_textdomain_codeset('', 'UTF-8');
```

Translate a string

```php
// ...
// Translate 'Hello World'
echo gettxt('Hello World');

// Method 1: Translate 'Hello %s' and replace '%s' with 'John'
echo sprintf(gettxt('Hello %s'), 'John');

// Method 2: Translate 'Hello %s' and replace '%s' with 'John'
echo gettxt(['Hello %s', 'John']);
```

Translate a string with context

```php
// ...
// Translate 'Hello World'
echo pgettxt('Context', 'Hello World');

// Method 1: Translate 'Hello %s' and replace '%s' with 'John'
echo sprintf(pgettxt('Context', 'Hello %s'), 'John');

// Method 2: Translate 'Hello %s' and replace '%s' with 'John'
echo pgettxt('Context', ['Hello %s', 'John']);
```

Return a string marked for translation

```php
// ...
// Returns 'Hello World'
echo noop_gettxt('Hello World');

// Method 1: Returns 'Hello %s' and replace '%s' with 'John'
echo sprintf(noop_gettxt('Hello %s'), 'John');

// Method 2: Returns 'Hello %s' and replace '%s' with 'John'
echo noop_gettxt(['Hello %s', 'John']);
```

Translate a plural form

```php
// ...
// Method 1: returns translation for 'Found %d items'
echo ngettxt('Found one item', 'Found %d items', 2);

// Method 2: returns translation for 'Found %d items' and replace '%d' with 2
echo sprintf(ngettxt('Found one item', 'Found %d items', 2), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo ngettxt('Found one item', 'Found %d items', [2, 2]);
```

Translate a plural form with context

```php
// ...
// Method 1: returns translation for 'Found %d items'
echo npgettxt('Context', 'Found one item', 'Found %d items', 2);

// Method 2: returns translation for 'Found %d items' and replace '%d' with 2
echo sprintf(npgettxt('Context', 'Found one item', 'Found %d items', 2), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo npgettxt('Context', 'Found one item', 'Found %d items', [2, 2]);
```

Return plural form marked for translation

```php
// ...
// Method 1: returns 'Found %d items'
echo noop_ngettxt('Found one item', 'Found %d items', 2);

// Method 2: returns 'Found %d items' and replace '%d' with 2
echo sprintf(noop_ngettxt('Found one item', 'Found %d items', 2), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo noop_ngettxt('Found one item', 'Found %d items', [2, 2]);
```

Other supported Gettext functions

```php

// Gettext compatible function to translate a string
// from another domain.
echo dgettxt('domain', 'Hello');

// Gettext compatible function to translate a string
// with context from another domain.
echo dpgettxt('domain', 'context', 'Hello');

// Gettext compatible function to translate plurals
// from another domain.
echo dngettxt('domain', 'Found one item', 'Found %d items', 2);

// Gettext compatible function to translate plurals
// with context from another domain.
echo dnpgettxt('domain', 'context', 'Found one item', 'Found %d items', 2);
```


## WordPress compatibility usage

```php
// include PhpGettxt
require_once 'PhpGettxt/autoload.php';

// use PhpGettxt
use PhpGettxt\Translator;

// Load compatibility layer
Translator::loadApi();

// Configure locale
_setlocale(0, 'de');
// or: set_locale('de')

// Configure default text domain
set_textdomain(Translator::DEFAULT_DOMAIN);
load_textdomain(Translator::DEFAULT_DOMAIN, __DIR__ . '/locales/');

// Or alternate
set_locale_dir( __DIR__ . '/locales/');
set_textdomain(Translator::DEFAULT_DOMAIN);
load_textdomain(Translator::DEFAULT_DOMAIN);

// Gettext compatibility only, does nothing
//_bind_textdomain_codeset('', 'UTF-8');
```

Translate a string

```php
// ...
// Translate 'Hello World'
echo __('Hello World');

// Method 1: Translate 'Hello %s' and replace '%s' with 'John'
echo sprintf(__('Hello %s'), 'John');

// Method 2: Translate 'Hello %s' and replace '%s' with 'John'
echo __(['Hello %s', 'John']);
```

Translate a string from another domain

```php
// ...
// Translate 'Hello World'
echo __('Hello World', 'domain');

// Method 1: Translate 'Hello %s' and replace '%s' with 'John'
echo sprintf(__('Hello %s', 'domain'), 'John');

// Method 2: Translate 'Hello %s' and replace '%s' with 'John'
echo __(['Hello %s', 'John'], 'domain');
```

Translate a string with context

```php
// ...
// Translate 'Hello World'
echo _x('Hello World', 'context');

// Method 1: Translate 'Hello %s' and replace '%s' with 'John'
echo sprintf(_x('Hello %s', 'context'), 'John');

// Method 2: Translate 'Hello %s' and replace '%s' with 'John'
echo _x(['Hello %s', 'John'], 'context');
```

Translate a string with context from another domain

```php
// ...
// Translate 'Hello World'
echo _x('Hello World', 'context', 'domain');

// Method 1: Translate 'Hello %s' and replace '%s' with 'John'
echo sprintf(_x('Hello %s', 'context', 'domain'), 'John');

// Method 2: Translate 'Hello %s' and replace '%s' with 'John'
echo _x(['Hello %s', 'John'], 'context', 'domain');
```

Return a string marked for translation

```php
// ...
// Returns 'Hello World'
echo noop__('Hello World');

// Method 1: Returns 'Hello %s' and replace '%s' with 'John'
echo sprintf(noop__('Hello %s'), 'John');

// Method 2: Returns 'Hello %s' and replace '%s' with 'John'
echo noop__(['Hello %s', 'John']);
```

Return a string marked for translation from another domain

```php
// ...
// Returns 'Hello World'
echo noop__('Hello World', 'domain');

// Method 1: Returns 'Hello %s' and replace '%s' with 'John'
echo sprintf(noop__('Hello %s', 'domain'), 'John');

// Method 2: Returns 'Hello %s' and replace '%s' with 'John'
echo noop__(['Hello %s', 'John'], 'domain');
```

Translate a plural form

```php
// ...
// Method 1: returns translation for 'Found %d items'
echo _n('Found one item', 'Found %d items', 2);

// Method 2: returns translation for 'Found %d items' and replace '%d' with 2
echo sprintf(_n('Found one item', 'Found %d items', 2), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo _n('Found one item', 'Found %d items', [2, 2]);
```

Translate a plural form from another domain

```php
// ...
// Method 1: returns translation for 'Found %d items'
echo _n('Found one item', 'Found %d items', 2, 'domain');

// Method 2: returns translation for 'Found %d items' and replace '%d' with 2
echo sprintf(_n('Found one item', 'Found %d items', 2, 'domain'), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo _n('Found one item', 'Found %d items', [2, 2], 'domain');
```

Translate a plural form with context

```php
// ...
// Method 1: returns translation for 'Found %d items'
echo _nx('Found one item', 'Found %d items', 2, 'context');

// Method 2: returns translation for 'Found %d items' and replace '%d' with 2
echo sprintf(_nx('Found one item', 'Found %d items', 2, 'context'), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo _nx('Found one item', 'Found %d items', [2, 2], 'context');
```

Translate a plural form with context from another domain

```php
// ...
// Method 1: returns translation for 'Found %d items'
echo _nx('Found one item', 'Found %d items', 2, 'context', 'domain');

// Method 2: returns translation for 'Found %d items' and replace '%d' with 2
echo sprintf(_nx('Found one item', 'Found %d items', 2, 'context', 'domain'), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo _nx('Found one item', 'Found %d items', [2, 2], 'context', 'domain');
```

Return plural form marked for translation

```php
// ...
// Method 1: returns 'Found %d items'
echo noop_n('Found one item', 'Found %d items', 2);

// Method 2: returns 'Found %d items' and replace '%d' with 2
echo sprintf(noop_n('Found one item', 'Found %d items', 2), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo noop_n('Found one item', 'Found %d items', [2, 2]);
```

Return plural form marked for translation from another domain

```php
// ...
// Method 1: returns 'Found %d items'
echo noop_n('Found one item', 'Found %d items', 2, 'domain');

// Method 2: returns 'Found %d items' and replace '%d' with 2
echo sprintf(noop_n('Found one item', 'Found %d items', 2, 'domain'), 2);

// Method 3: returns translation for 'Found %d items' and replace '%d' with 2
echo noop_n('Found one item', 'Found %d items', [2, 2], 'domain');
```


## Other global functions

```php
// Set the name for the $GLOBALS
$name = set_translator_varname('locale');
// Get the name for the $GLOBALS
$name = get_translator_varname('locale');

// Set the text domain; alias for _textdomain($domain)
$domain = set_textdomain('domain'); 
// Get the text domain; alias for _textdomain(null)
$domain = get_textdomain();

// Detects configured locale
$locale = detect_locale()

// Set the locale name; alias for _setlocale(0, $locale)
$locale = set_locale('en')
// Get the locale name; alias for _setlocale(0, null)
$locale = get_locale()

// Get user agent's most preferred accepted locale
$locale = get_accepted_locale();

// Get instance of Translator; alias for Translator::getInstance()
$translator = translator();
```
