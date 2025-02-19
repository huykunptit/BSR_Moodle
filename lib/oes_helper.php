<?php
/**
 * OES Helper
 * The helper class for OES to provide some common functions.
 *
 * @package   None
 * @copyright 2024 OES
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//function print_debug(...$args) {
//    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
//    $filePath = $caller['file'];
//    $lineNumber = $caller['line'];
//    echo '<div style="background-color: #F4F4F4; padding: 20px; border: 1px solid #CCCCCC; border-radius: 4px; font-family: Segoe UI, Arial, sans-serif; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">';
//    echo '<div style="margin-bottom: 20px;">';
//    echo '<h2 style="color: #333333; margin: 0; font-size: 18px;">Dumped Data (' . $filePath . ', Line ' . $lineNumber . '):</h2>';
//    echo '</div>';
//    foreach ($args as $arg) {
//        echo '<div style="background-color: #FFFFFF; border: 1px solid #DDDDDD; border-radius: 4px; padding: 10px; margin-bottom: 10px; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);">';
//        echo '<pre style="margin: 0;">';
//        var_dump($arg);
//        echo '</pre>';
//        echo '</div>';
//    }
//    echo '</div>';
//}
//
//function dd(...$args) {
//    print_debug(...$args);
//    die();
//}
//
//function dump(...$args): void
//{
//    print_debug(...$args);
//}
require_once __DIR__ . '/../auth/iomadsaml2/.extlib/simplesamlphp/vendor/autoload.php';

use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('dump')) {
    /**
     * @author Nicolas Grekas <p@tchwork.com>
     */
    function dump($var, ...$moreVars)
    {
        VarDumper::dump($var);

        foreach ($moreVars as $v) {
            VarDumper::dump($v);
        }

        if (1 < func_num_args()) {
            return func_get_args();
        }

        return $var;
    }
}

if (!function_exists('dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $v) {
            VarDumper::dump($v);
        }

        exit(1);
    }
}


function visanitize($title): string
{
    $replacement = '-';
    $map = array();
    $quotedReplacement = preg_quote($replacement, '/');
    $default = array(
        '/à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ|À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ|å/' => 'a',
        '/è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ|È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ|ë/' => 'e',
        '/ì|í|ị|ỉ|ĩ|Ì|Í|Ị|Ỉ|Ĩ|î/' => 'i',
        '/ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ|Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ|ø/' => 'o',
        '/ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ|Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ|ů|û/' => 'u',
        '/ỳ|ý|ỵ|ỷ|ỹ|Ỳ|Ý|Ỵ|Ỷ|Ỹ/' => 'y',
        '/đ|Đ/' => 'd',
        '/ç/' => 'c',
        '/ñ/' => 'n',
        '/ä|æ/' => 'ae',
        '/ö/' => 'oe',
        '/ü/' => 'ue',
        '/Ä/' => 'Ae',
        '/Ü/' => 'Ue',
        '/Ö/' => 'Oe',
        '/ß/' => 'ss',
        '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
        '/\\s+/' => $replacement,
        sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
    );
    //Some URL was encode, decode first
    $title = urldecode($title);
    $map = array_merge($map, $default);
    return strtolower(preg_replace(array_keys($map), array_values($map), $title));
}
