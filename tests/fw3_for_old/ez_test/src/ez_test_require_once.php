<?php
/**    _______       _______
 *    / ____/ |     / /__  /
 *   / /_   | | /| / / /_ <
 *  / __/   | |/ |/ /___/ /
 * /_/      |__/|__//____/
 *
 * Flywheel3: the inertia php framework for old php versions
 *
 * @category    Flywheel3
 * @package     ez_test
 * @author      wakaba <wakabadou@gmail.com>
 * @copyright   2020 - Wakabadou (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/)
 * @license     http://opensource.org/licenses/MIT The MIT License MIT
 * @varsion     1.0.0
 */

/**
 * ez_test関連ファイル一括読み込み
 */
require_once sprintf('%s/TestRunner.php', __DIR__);

require_once sprintf('%s/reflectors/annotations/abstracts/AbstractAnnotation.php', __DIR__);
require_once sprintf('%s/reflectors/annotations/abstracts/AbstractBoolAnnotation.php', __DIR__);

require_once sprintf('%s/reflectors/annotations/DataProvider.php', __DIR__);
require_once sprintf('%s/reflectors/annotations/ExclusionGroup.php', __DIR__);
require_once sprintf('%s/reflectors/annotations/ExpectedException.php', __DIR__);
require_once sprintf('%s/reflectors/annotations/ExpectedExceptionMessage.php', __DIR__);
require_once sprintf('%s/reflectors/annotations/Group.php', __DIR__);
require_once sprintf('%s/reflectors/annotations/InstanceFork.php', __DIR__);
require_once sprintf('%s/reflectors/annotations/ProcessFork.php', __DIR__);
require_once sprintf('%s/reflectors/annotations/StopWithAssertionFailed.php', __DIR__);
require_once sprintf('%s/reflectors/annotations/Test.php', __DIR__);

require_once sprintf('%s/reflectors/ReflectionTestObject.php', __DIR__);
require_once sprintf('%s/reflectors/ReflectionTestMethod.php', __DIR__);

require_once sprintf('%s/test_unit/TestInterface.php', __DIR__);

require_once sprintf('%s/test_unit/AbstractTest.php', __DIR__);
