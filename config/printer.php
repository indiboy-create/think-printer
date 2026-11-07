<?php

declare(strict_types=1);

/*
 * ThinkPHP 云打印配置
 *
 * 应用可将本文件复制到 config/printer.php 后按需修改。
 * 未提供的配置项会自动使用本文件的默认值。
 */

return [
    /*
     * 默认连接名称。
     * 取值必须是 clients 中的键名（连接名），不是驱动类型。
     */
    'default' => 'feieyun',

    /*
     * 云打印连接列表。
     *
     * 每个连接支持以下配置：
     * - type：驱动类型，可填内置厂商标识（feieyun、jolimark、kuaidi100、poscom、
     *   ushengyun、xpyun、yilianyun、zhongwuyun），也可填实现了
     *   Pkg6\CloudPrint\Contracts\ClientInterface 的完整类名。
     * - 其余键为该厂商的凭证与可选地址，原样传递给 cloud-print 驱动。
     *
     * 同一个厂商可以配置多个连接，例如 feieyun_shop_a、feieyun_shop_b，
     * 只需分别填写各自的 type 和账号凭证，便于按商户或门店隔离打印账号。
     */
    'clients' => [
        /*
         * 飞鹅云：https://help.feieyun.com/document.php
         * user：飞鹅云后台注册的账号（邮箱或手机号）。
         * ukey：飞鹅云后台“我的账号”中的 UKEY 密钥。
         * host：接口地址，默认 http://api.feieyun.cn/Api/Open/，一般无需修改。
         */
        'feieyun' => [
            'type' => 'feieyun',
            'user' => '',
            'ukey' => '',
        ],

        /*
         * 映美云：http://open.jolimark.com/
         * app_id：映美云开放平台的应用 AppID。
         * app_key：映美云开放平台的应用密钥。
         * host：接口地址，默认 http://mcp.jolimark.com/，一般无需修改。
         */
        'jolimark' => [
            'type' => 'jolimark',
            'app_id' => '',
            'app_key' => '',
        ],

        /*
         * 快递100：https://api.kuaidi100.com/document/
         * key：快递100 云打印的客户授权 key。
         * secret：快递100 云打印的客户授权 secret。
         * host：快递100 没有内置默认地址，使用前必须填写接口地址，
         *       例如 https://poll.kuaidi100.com/printapi/printtask.do，
         *       也可在运行时调用 setRequestUrl() 设置。
         */
        'kuaidi100' => [
            'type' => 'kuaidi100',
            'key' => '',
            'secret' => '',
            'host' => '',
        ],

        /*
         * 佳博云：https://dev.poscom.cn/
         * memberCode：佳博云开放平台的商户编号。
         * apiKey：佳博云开放平台的 API 密钥。
         * host：接口地址，默认 https://api.poscom.cn/，一般无需修改。
         */
        'poscom' => [
            'type' => 'poscom',
            'memberCode' => '',
            'apiKey' => '',
        ],

        /*
         * 优声云：https://www.ushengyun.com/
         * appId：优声云开放平台的应用 ID。
         * appSecret：优声云开放平台的应用密钥。
         * host：接口地址，默认 https://open-api.ushengyun.com/printer，一般无需修改。
         */
        'ushengyun' => [
            'type' => 'ushengyun',
            'appId' => '',
            'appSecret' => '',
        ],

        /*
         * 芯烨云：https://www.xpyun.net/open/index.html
         * user：芯烨云开放平台的账号。
         * userKey：芯烨云开放平台的用户密钥。
         * host：接口地址，默认 https://open.xpyun.net/api/openapi/xprinter，一般无需修改。
         */
        'xpyun' => [
            'type' => 'xpyun',
            'user' => '',
            'userKey' => '',
        ],

        /*
         * 易联云：https://www.yilianyun.net/
         * client_id：易联云开放平台的客户端 ID。
         * client_secret：易联云开放平台的客户端密钥。
         * host：接口地址，默认 https://open-api.10ss.net/，一般无需修改。
         *
         * 易联云需要 access_token，建议保持 cache.enabled 为 true。
         */
        'yilianyun' => [
            'type' => 'yilianyun',
            'client_id' => '',
            'client_secret' => '',
        ],

        /*
         * 中午云：http://www.zhongwu.co/
         * appid：中午云开放平台的应用 ID。
         * appsecret：中午云开放平台的应用密钥。
         * host：接口地址，默认 http://api.zhongwuyun.com，一般无需修改。
         */
        'zhongwuyun' => [
            'type' => 'zhongwuyun',
            'appid' => '',
            'appsecret' => '',
        ],
    ],

    /*
     * 缓存配置。
     * 易联云、映美云需要缓存 access_token；关闭后将无法复用令牌，
     * 每次创建客户端都会重新申请，可能导致接口调用变慢。
     */
    'cache' => [
        /*
         * 是否把 ThinkPHP 缓存注入到云打印驱动。
         * true：启用；false：关闭。
         */
        'enabled' => true,

        /*
         * 使用的缓存 store 名称，对应 config/cache.php 中 stores 的键名。
         * null：使用 cache.default 指定的默认缓存。
         */
        'store' => null,
    ],

    /*
     * 日志配置。
     * 开启后通过 cloud-print 内置的 Guzzle 日志中间件，把每次 HTTP 请求
     * 写入 ThinkPHP 日志（think\Log），便于排查厂商接口问题。
     */
    'log' => [
        /*
         * 是否记录云打印 HTTP 请求日志。
         * true：启用；false：关闭。
         */
        'enabled' => false,
    ],
];