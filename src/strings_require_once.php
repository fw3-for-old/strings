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
 * @package     strings
 * @author      akira wakaba <wakabadou@gmail.com>
 * @copyright   Copyright (c) @2020  Wakabadou (http://www.wakabadou.net/) / Project ICKX (https://ickx.jp/). All rights reserved.
 * @license     http://opensource.org/licenses/MIT The MIT License.
 *              This software is released under the MIT License.
 * @varsion     1.0.0
 */

/**
 * Strings関連ファイル一括読み込み。
 */
require_once sprintf('%s/converter/Convert.php', __DIR__);
require_once sprintf('%s/builder/traits/converter/ConverterInterface.php', __DIR__);
require_once sprintf('%s/builder/traits/converter/AbstractConverter.php', __DIR__);
require_once sprintf('%s/builder/modifiers/ModifierInterface.php', __DIR__);
require_once sprintf('%s/builder/modifiers/AbstractModifier.php', __DIR__);
require_once sprintf('%s/builder/modifiers/strings/ToDebugStringModifier.php', __DIR__);
require_once sprintf('%s/builder/modifiers/security/EscapeModifier.php', __DIR__);
require_once sprintf('%s/builder/modifiers/datetime/StrtotimeModifier.php', __DIR__);
require_once sprintf('%s/builder/modifiers/datetime/DefaultModifier.php', __DIR__);
require_once sprintf('%s/builder/modifiers/datetime/DateModifier.php', __DIR__);
require_once sprintf('%s/builder/indentor/Indentor.php', __DIR__);
require_once sprintf('%s/builder/builder/StringBuilder.php', __DIR__);
