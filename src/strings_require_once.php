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
require_once sprintf('%s/tabular/Tabular.php', __DIR__);
require_once sprintf('%s/builder/StringBuilder.php', __DIR__);
require_once sprintf('%s/builder/DebugHtmlBuilder.php', __DIR__);
require_once sprintf('%s/html/traits/Htmlable.php', __DIR__);
require_once sprintf('%s/html/config/HtmlConfigInterface.php', __DIR__);
require_once sprintf('%s/html/elements/traits/HtmlElementInterface.php', __DIR__);
require_once sprintf('%s/html/config/HtmlConfig.php', __DIR__);
require_once sprintf('%s/html/traits/AbstractHtmlable.php', __DIR__);
require_once sprintf('%s/html/elements/traits/AbstractHtmlElement.php', __DIR__);
require_once sprintf('%s/html/HtmlAttribute.php', __DIR__);
require_once sprintf('%s/html/HtmlElement.php', __DIR__);
require_once sprintf('%s/html/HtmlTextNode.php', __DIR__);
require_once sprintf('%s/html/Html.php', __DIR__);
