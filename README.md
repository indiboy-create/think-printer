# youvision/think-printer

基于 [pkg6/cloud-print](https://github.com/pkg6/cloud-print) 的 ThinkPHP 8 云打印扩展，统一封装飞鹅云、芯烨云、易联云、快递100、映美云、佳博云、中午云、优声云。

## 环境要求

- PHP >= 8.1
- ThinkPHP >= 8.0
- ext-json、ext-zlib

## 安装

```bash
composer require youvision/think-printer
```

## 注册服务

扩展包已在 `composer.json` 中声明 `extra.think.services`。安装完成后在项目根目录执行：

```bash
php think service:discover
```

如果项目的 `composer.json` 已配置 `post-autoload-dump`，执行 `composer install` 或 `composer update` 时会自动生成 `vendor/services.php`，无需手动执行。

也可以直接在 `app/service.php` 中手动注册：

```php
<?php

return [
    \Youvision\ThinkPrinter\Service::class,
];
```

## 发布配置

将配置文件复制到应用的 `config/printer.php`：

```bash
cp vendor/youvision/think-printer/config/printer.php config/printer.php
```

然后在 `config/printer.php` 中填写厂商凭证。配置文件中的每个参数均有中文说明。

## 基本用法

### 门面

```php
use Youvision\ThinkPrinter\facade\Printer;
use Youvision\ThinkPrinter\Request;

$result = Printer::client()->print(
    Request::create()
        ->sn('打印机编号')
        ->content('订单内容')
        ->copies(1)
        ->orderId('order-001')
);
```

### 依赖注入

```php
use Youvision\ThinkPrinter\Printer;
use Youvision\ThinkPrinter\Request;

public function submit(Printer $printer)
{
    $client = $printer->client('feieyun');

    return $client->print(
        Request::create()->sn('打印机编号')->content('订单内容')
    );
}
```

### 指定连接

```php
use Youvision\ThinkPrinter\facade\Printer;

Printer::client('xpyun')->queryPrinterStatus(['sn' => '打印机编号']);
```

`client()` 不传参数时使用配置中的 `default` 连接。

### 同一厂商多账号

`clients` 的键名是连接名，`type` 才是驱动类型，因此同一个厂商可以配置多套账号：

```php
'clients' => [
    'feieyun_shop_a' => [
        'type' => 'feieyun',
        'user' => '账号A',
        'ukey' => '密钥A',
    ],
    'feieyun_shop_b' => [
        'type' => 'feieyun',
        'user' => '账号B',
        'ukey' => '密钥B',
    ],
],
```

```php
Printer::client('feieyun_shop_a')->print($requestA);
Printer::client('feieyun_shop_b')->print($requestB);
```

## 统一打印协议

所有厂商都实现 `print(Request $request)`，参数说明：

| 参数 | 类型 | 说明 |
| --- | --- | --- |
| `sn` | string | 打印机编号 |
| `content` | string | 打印内容 |
| `copies` | int | 打印份数，默认 1 |
| `orderId` | string | 订单号 |
| `type` | string | 打印类型，各厂商取值不同 |
| `templateId` | string | 模板 ID |
| `imageUrl` | string | 图片地址 |
| `htmlUrl` | string | HTML 地址 |
| `extra` | array | 厂商私有扩展字段 |

各厂商 `type` 取值：

| 厂商 | type 值 | 说明 |
| --- | --- | --- |
| 飞鹅云 | `label` | 标签打印，默认文本打印 |
| 芯烨云 | `label` | 标签打印，默认文本打印 |
| 易联云 | `picture` / `express` | 图片打印 / 面单打印，默认文本打印 |
| 映美云 | `html_url`、`html_code`、`html_to_pic`、`html_to_gray_pic`、`template`、`point_text`、`express`、`rich_html_code`、`esc`、`file`、`file_by_url`、`label`、`invoice` | 13 种打印模式 |
| 其余厂商 | 默认 | 文本打印 |

### 示例

```php
use Youvision\ThinkPrinter\facade\Printer;
use Youvision\ThinkPrinter\Request;

Printer::client('feieyun')->print(
    Request::create()->sn('99999999')->content('标签内容')->type('label')
);

Printer::client('jolimark')->print(
    Request::create()->sn('99999999')->htmlUrl('https://example.com/order.html')->type('html_url')
);

Printer::client('yilianyun')->print(
    Request::create()->sn('99999999')->imageUrl('https://example.com/image.png')->type('picture')
);
```

## 调用驱动原生方法

除统一打印协议外，`cloud-print` 的厂商客户端方法均可直接调用：

```php
use Youvision\ThinkPrinter\facade\Printer;

Printer::openPrintMsg([
    'sn' => '99999999',
    'content' => '订单内容',
]);

Printer::client('xpyun')->queryOrderState(['sn' => '99999999', 'orderid' => 'order-001']);
```

## 自定义驱动

驱动类型可以填写实现了 `Pkg6\CloudPrint\Contracts\ClientInterface` 的完整类名：

```php
'clients' => [
    'custom' => [
        'type' => \App\Printer\MyClient::class,
        'token' => 'xxx',
    ],
],
```

也可以运行时注册：

```php
use Youvision\ThinkPrinter\facade\Printer;

Printer::extend('custom', function (array $config, \think\App $app) {
    return new \App\Printer\MyClient($config);
});

Printer::client('custom')->print($request);
```

## 缓存与日志

- `cache.enabled` 为 `true` 时，包会把 ThinkPHP 缓存注入云打印驱动，易联云和映美云的 `access_token` 会自动复用。
- `cache.store` 可指定 `config/cache.php` 中的 store 名称，`null` 表示使用默认缓存。
- `log.enabled` 为 `true` 时，包会通过 `think\Log` 记录每次 HTTP 请求，便于排查厂商接口问题。

## 支持的厂商

- [飞鹅云](http://help.feieyun.com/document.php)
- [芯烨云](https://www.xpyun.net/open/index.html)
- [易联云](https://www.yilianyun.net/)
- [快递100](https://api.kuaidi100.com/document/)
- [映美云](http://open.jolimark.com/)
- [佳博云](https://dev.poscom.cn/)
- [中午云](http://www.zhongwu.co/)
- [优声云](https://www.ushengyun.com/)

## 测试

```bash
composer test
```

## 许可协议

[MIT](LICENSE)