# Flywheel3 String processing library for old php versions

Rapid Development FrameworkであるFlywheel3 の文字列処理ライブラリです。

[fw3/strings: Flywheel3 strings processing library](https://github.com/fw3/strings)で公開しているStringBuilderをPHP5.3.3以降でも動作するようにした、実用向けのストリームフィルタです。

お手軽簡単、今すぐに利用したい方は [設定などの注意点](#設定などの注意点) 、 [使い方](#使い方) を参照してください。

PHP7.2.0未満への対応が不要な場合、 [fw3/strings](https://github.com/fw3/strings) を使用してください。

## 対象バージョンおよび動作確認バージョン

対象バージョン：PHP5.3.3以降

### 動作確認バージョン

- **5.3.3**
- 5.3.4
- 5.3.5
- 5.3.6
- 5.3.7
- 5.3.8
- **5.3.9**
- 5.4.16
- 5.4.39
- **5.4.45**
- **5.5.38**
- **5.6.40**
- **7.0.33**
- **7.1.33**
- **7.2.33**
- **7.3.21**
- **7.4.0**
- **7.4.9**
- **8.0.0beta2**

### 設定などの注意点

#### Windows (php7.2.0未満)

php.iniの次の行のコメントを除去してください。

```diff
- ; extension_dir = "ext"
+ extension_dir = "ext"
```

```diff
- ;extension=php_mbstring.dll
+ extension=php_mbstring.dll
```

#### Windows (php7.2.0以上)

php.iniの次の行のコメントを除去してください。

```diff
- ; extension_dir = "ext"
+ extension_dir = "ext"
```

```diff
- ;extension=mbstring
+ extension=mbstring
```

#### Linux系 (パッケージマネージャ使用)

各種パッケージマネージャで`php-mbstring`またはそれに類するものをインストールしてください。

#### Linux系 (phpenv使用)

`default_configure_options`または各definitionに次の一つを追加してください。

```
--enable-mbstring
```

#### Linux系 (ソースコードからビルド)

configureオプションに次の一つを追加してください。
詳細は[PHP マニュアル 関数リファレンス 自然言語および文字エンコーディング マルチバイト文字列 インストール/設定](https://www.php.net/manual/ja/mbstring.installation.php)を参照してください。

```
--enable-mbstring
```

## 使い方

### 1 . インストール

#### composerを使用できる環境の場合

次のコマンドを実行し、インストールしてください。

`composer require fw3_for_old/strings`

#### composerを使用できない環境の場合

[Download ZIP](https://github.com/fw3-for-old/strings/archive/master.zip)よりzipファイルをダウンロードし、任意のディレクトリにコピーしてください。

使用対象となる処理より前に`require_once sprintf('%s/src/strings_require_once.php', $path_to_copy_dir);`として`src/strings_require_once.php`を読み込むようにしてください。

## 主な機能

### StringBuilder - 文字列テンプレートエンジン

PDOにおける名前付きプレースホルダのようなテンプレート文字列を扱えます。

SmartやTwigのように変数に対して修飾したり、実行時に変数名から動的に値を返したり、複数の変数から、見つかり次第値を埋めるといったことが可能です。

### Converter - 文字列変換器

エスケープや安全なJSONize、ケースコンバート、変数の文字列表現化を行えます。

## 機能詳細：StringBuilder

次の例の用にあらかじめフォーマットが定まった文字列に対して、容易に値を展開することができます。

```
<?php

use fw3_for_old\strings\builder\StringBuilder;

echo StringBuilder::factory()->build('{:value1}はあります。', [
    'value1'    => 'VALUE1',
]);
// VALUE1はあります。と表示されます。

```

いずれかの値が変数として存在する場合に、見つかり次第展開することもできます。

```
<?php

use fw3_for_old\strings\builder\StringBuilder;

echo StringBuilder::factory()->build('{:value1:value2}はあります。', [
    'value2'	=> 'VALUE2',
]);
// VALUE2はあります。と表示されます。
```

SmartやTwigのように変数に対して修飾を行う事も出来ます。

```
<?php

use fw3_for_old\strings\builder\StringBuilder;

echo StringBuilder::factory()->build('現在の時刻は{:now|date('Y/m/d H:i:s')}です。', [
    'now'	=> strtotime('2020/01/01 00:00:00'),
]);
// 現在の時刻は2020/01/01 00:00:00です。と表示されます。

```

デバッグやエラーログの取り扱いに便利な文字列の出力も行えます。

```
<?php

use fw3_for_old\strings\builder\StringBuilder;

echo StringBuilder::factory()->build('値はそれぞれ{:bool|to_debug}、{:null|to_debug}、{:string|to_debug}、{:array|to_debug}、{:array2|to_debug(2)}です。', [
    'bool'		=> false,
    'null'		=> NULL,
    'string'	=> '',
    'array'		=> [[[]]],
    'array2'	=> [[[]]],
]);
// 値はそれぞれfalse、’’、NULL、Array、[0 => [0 => Array]]です。と表示されます。
```

## 機能詳細：Converter

### escape

HTML用のエスケープとJavaScript用のエスケープを提供します。

実行時に動的にエスケープタイプを変更したい場合は`escape`を使用します。

```
<?php

use fw3_for_old\strings\converter\Convert;

echo Converter::escape('<a href="https://ickx.jp">ickx.jp</a>');
// &lt;a href=&quot;https://ickx.jp&quot;&gt;ickx.jp&lt;/a&gt; と表示されます

echo Convert::escape('alert(\'alert\');');
// alert(&apos;alert&apos;); と表示されます

echo Convert::escape('alert(\'alert\');', Convert::ESCAPE_TYPE_JS);
// alert\x28\x27alert\x27\x29\x3b と表示されます
```

明確にescapeする対象が定まっている場合はhtmlEscapeなどの特化処理を利用してください。

HTMLに特化したescape処理は`htmlEscape`を使用してください。

```
<?php

use fw3_for_old\strings\converter\Convert;

echo Converter::hmlEscape('<a href="https://ickx.jp">ickx.jp</a>');
// &lt;a href=&quot;https://ickx.jp&quot;&gt;ickx.jp&lt;/a&gt; と表示されます
```

同様にJavaScriptに特化したescape処理は`jsEscape`を使用してください。

```
<?php

use fw3_for_old\strings\converter\Convert;

echo Convert::escape('alert(\'alert\');', Convert::ESCAPE_TYPE_JS);
// alert\x28\x27alert\x27\x29\x3b と表示されます
```

### JSONize

与えられたPHP変数を安全なJSON文字列に変換します。

```
<?php

use fw3_for_old\strings\converter\Convert;

echo Convert::toJson('alert(\'alert\');');
// alert(\u0027alert\u0027); と表示されます。
```

### ケースコンバート

SNAKE_CASEやCHAIN-CASE、CamelCaseを相互に変換します。

to SNAKE_CASE

```
<?php

use fw3_for_old\strings\converter\Convert;

echo Convert::toSnakeCase('toSnakeCase'); // to_Snake_Case と表示されます
echo Convert::toSnakeCase('ToSnakeCase'); // To_Snake_Case と表示されます
echo Convert::toUpperSnakeCase('toSnakeCase'); // to_snake_case と表示されます
echo Convert::toLowerSnakeCase('toSnakeCase'); // TO_SNAKE_CASE と表示されます

echo Convert::toSnakeCase('to-Snake-Case'); // to_Snake_Case と表示されます

echo Convert::toSnakeCase('to_Snake_Case'); // to_Snake_Case と表示されます
```

to CHAIN_CASE

```
<?php

use fw3_for_old\strings\converter\Convert;

echo Convert::toSnakeCase('toSnakeCase'); // to-Snake-Case と表示されます
echo Convert::toSnakeCase('ToSnakeCase'); // To-Snake-Case と表示されます
echo Convert::toUpperSnakeCase('toSnakeCase'); // to-snake-case と表示されます
echo Convert::toLowerSnakeCase('toSnakeCase'); // TO-SNAKE-CASE と表示されます

echo Convert::toSnakeCase('to-Snake-Case'); // to-Snake-Case と表示されます

echo Convert::toSnakeCase('to_Snake_Case'); // to-Snake-Case と表示されます
```

to CamelCase

```
<?php

use fw3_for_old\strings\converter\Convert;

echo Convert::toSnakeCase('to_Snake_Case'); // toSnakeCase と表示されます
echo Convert::toSnakeCase('To_snake_case'); // ToSnakeCase と表示されます
echo Convert::toUpperSnakeCase('to_Snake_Case'); // toSnakeCase と表示されます
echo Convert::toLowerSnakeCase('to_Snake_Case'); // ToSnakeCase と表示されます

echo Convert::toSnakeCase('to-Snake-Case'); // toSnakeCase と表示されます

echo Convert::toSnakeCase('to_Snake_Case'); // toSnakeCase と表示されます
```

### 変数情報展開

変数に関する情報を文字列にします。

変数が実際はどういう状態になっているかをさっと見たりログに残したりする場合に便利です。

配列などの階層構造になっている値は指定した深さまでは表示するように制約できます。

実際に感謝されたケースはfalseやnullなどの文字列化した場合に空文字となってしまった場合や、数値が文字列か整数かを簡単に識別できた場合などでした。

```
<?php

use fw3_for_old\strings\converter\Convert;

echo Convert::toDebugString(true); // true と表示されます
echo Convert::toDebugString(false); // false と表示されます
echo Convert::toDebugString(null); // null と表示されます

echo Convert::toDebugString(0.0); // 0.0 と表示されます
echo Convert::toDebugString(0.1); // 0.1 と表示されます
echo Convert::toDebugString(0); // 0 と表示されます

echo Convert::toDebugString('0'); // '0' と表示されます

echo Convert::toDebugString([0 => [], 'a' => [1, 2]]); // Array と表示されます
echo Convert::toDebugString([], 1); // [0 => Array, 'a' => Array] と表示されます

echo Convert::toDebugString(new stdClass()); // object((stdClass)#381) と表示されます #381の箇所は実行環境により異なります
```
