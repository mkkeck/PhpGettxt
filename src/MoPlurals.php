<?php
namespace PhpGettxt;
use Exception;


/**
 * Parse and validate plural forms from the plural forms expression
 * of the loaded MO-file.
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
class MoPlurals {

  /**
   * @var string
   *      Operator characters.
   */
  const OP_CHARS = '|&><!=%?:';

  /**
   * @var string
   *      Valid number characters.
   */
  const NUM_CHARS = '0123456789';

  /**
   * @var array
   *      Operator precedence from highest to lowest.
   */
  protected static $op_precedence = [
    '%'  => 6,

    '<'  => 5,
    '<=' => 5,
    '>'  => 5,
    '>=' => 5,

    '==' => 4,
    '!=' => 4,

    '&&' => 3,

    '||' => 2,

    '?:' => 1,
    '?'  => 1,

    '('  => 0,
    ')'  => 0
  ];

  /**
   * @var array
   *      List of tokens (generated from the string).
   */
  protected $tokens;

  /**
   * @var array
   *      Cache for repeated calls to the function. Map of $n => $result.
   */
  protected $cache;


  /**
   * Execute the plural form function.
   *
   * @param  int $n     Variable "n" to substitute.
   * @return int        Plural form value.
   * @throws Exception  If the plural form value cannot be calculated.
   */
  protected function execute($n) {
    $stack = [];
    $i     = 0;
    $total = count($this->tokens);
    while ($i < $total) {
      $next = $this->tokens[$i];
      $i++;
      if ($next[0] === 'var') {
        $stack[] = $n;
        continue;
      } elseif ($next[0] === 'value') {
        $stack[] = $next[1];
        continue;
      }

      // Only operators left.
      switch ($next[1]) {
        case '%':
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 % $v2;
          break;
        case '||':
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 || $v2;
          break;
        case '&&':
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 && $v2;
          break;
        case '<':
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 < $v2;
          break;
        case '<=':
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 <= $v2;
          break;
        case '>':
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 > $v2;
          break;
        case '>=':
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 >= $v2;
          break;
        case '!=':
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 != $v2;
          break;
        case '==':
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 == $v2;
          break;
        case '?:':
          $v3 = array_pop($stack);
          $v2 = array_pop($stack);
          $v1 = array_pop($stack);
          $stack[] = $v1 ? $v2 : $v3;
          break;
        default:
          throw new Exception(sprintf('Unknown operator "%s"', $next[1]));
      }
    }

    if (count($stack) !== 1) {
      throw new Exception('Too many values remaining on the stack');
    }
    return intval($stack[0]);
  }


  /**
   * Parse a `Plural-Forms` string into tokens.
   *
   * Uses the shunting-yard algorithm to convert the string to
   * Reverse Polish Notation tokens.
   *
   * @param  string    $expr  String to parse.
   * @throws Exception        If there is a syntax or parsing error
   *                          with the string.
   *
   */
  protected function parse($expr) {
    if (!is_array($this->tokens)) {
      $pos = 0;
      $len = strlen($expr);

      // Convert infix operators to postfix using the
      // shunting-yard algorithm.
      $output = [];
      $stack  = [];
      while ($pos < $len) {
        $next = substr($expr, $pos, 1);
        switch ($next) {
          // Ignore whitespace.
          case ' ':
          case "\t":
            $pos++;
            break;

          // Variable (n).
          case 'n':
            $output[] = ['var'];
            $pos++;
            break;

          // Parentheses.
          case '(':
            $stack[] = $next;
            $pos++;
            break;
          case ')':
            $found = false;
            while (!empty($stack)) {
              $o2 = $stack[count($stack) - 1];
              if ($o2 !== '(') {
                $output[] = ['op', array_pop($stack)];
                continue;
              }
              // Discard open paren.
              array_pop($stack);
              $found = true;
              break;
            }
            if (!$found) {
              throw new Exception('Mismatched parentheses');
            }
            $pos++;
            break;

          // Operators.
          case '|':
          case '&':
          case '>':
          case '<':
          case '!':
          case '=':
          case '%':
          case '?':
            $end_operator = strspn($expr, self::OP_CHARS, $pos);
            $operator     = substr($expr, $pos, $end_operator);
            if (!array_key_exists($operator, self::$op_precedence)) {
              throw new Exception(sprintf('Unknown operator "%s"', $operator));
            }
            while (! empty($stack)) {
              $o2 = $stack[count($stack) - 1];
              // Ternary is right-associative in C.
              if ($operator === '?:' || $operator === '?') {
                if (self::$op_precedence[$operator] >= self::$op_precedence[$o2]) {
                  break;
                }
              } elseif (self::$op_precedence[$operator] > self::$op_precedence[$o2]) {
                break;
              }
              $output[] = ['op', array_pop($stack)];
            }
            $stack[] = $operator;
            $pos += $end_operator;
            break;

          // Ternary "else".
          case ':':
            $found = false;
            $s_pos = count($stack) - 1;
            while ($s_pos >= 0) {
              $o2 = $stack[$s_pos];
              if ($o2 !== '?') {
                $output[] = ['op', array_pop($stack)];
                $s_pos--;
                continue;
              }
              // Replace.
              $stack[$s_pos] = '?:';
              $found = true;
              break;
            }

            if (!$found) {
              throw new Exception('Missing starting "?" ternary operator');
            }
            $pos++;
            break;

          // Default - number or invalid.
          default:
            if ($next >= '0' && $next <= '9') {
              $span     = strspn($expr, self::NUM_CHARS, $pos);
              $output[] = ['value', intval(substr($expr, $pos, $span))];
              $pos     += $span;
              break;
            }
            throw new Exception(sprintf('Unknown symbol "%s"', $next));
        }
      }
      while (!empty($stack)) {
        $o2 = array_pop($stack);
        if ($o2 === '(' || $o2 === ')') {
          throw new Exception('Mismatched parentheses');
        }
        $output[] = ['op', $o2];
      }
      $this->tokens = $output;
    }
  }


  /**
   * Returns the plural form for a number and caches the value
   * for repeated calls.
   *
   * @param  int  $n  Number to get plural form for.
   * @return int      Plural form value.
   * @throws Exception
   */
  protected function get($n) {
    if (!isset($this->cache[$n])) {
      $this->cache[$n] = $this->execute($n);
    }
    return $this->cache[$n];
  }


  /**
   * Evaluates the plural form expression and returns the
   * plural form for a number.
   *
   * @param  string $expr  Expression to process
   * @param  int    $n     Number to get plural form for.
   * @return int           Plural form value.
   * @throws Exception
   */
  public function evaluate($expr, $n) {
    $this->parse($expr);
    return $this->get($n);
  }


  /**
   * Constructor.
   */
  public function __construct() {
    $this->cache = [];
  }
}
