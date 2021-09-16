<?php
/**    _______       _______
 *    / ____/ |     / /__  /
 *   / /_   | | /| / / /_ <
 *  / __/   | |/ |/ /___/ /
 * /_/      |__/|__//____/
 *
 * Flywheel3: the inertia php framework for old php versions
 *
 * @category    test
 * @package     strings
 * @author      wakaba <wakabadou@gmail.com>
 * @copyright   2020 - Wakabadou (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license     http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion     1.0.0
 */

namespace fw3_for_old\tests\strings;

use fw3_for_old\ez_test\TestRunner;

// includes
require_once sprintf('%s/src/strings_require_once.php', \dirname(__DIR__));

// test runner
require_once \sprintf('%s/fw3_for_old/ez_test/src/ez_test_require_once.php', __DIR__);

return TestRunner::factory(array(
    'php_binary_path'   => "C:/php/5.3.3/php.exe",
))->run()->getExitStatus();
