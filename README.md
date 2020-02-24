# topphp-rsa

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Total Downloads][ico-downloads]][link-downloads]


>这是一个RSA的非对称加密解密组件

## 包含方法

 - 创建公私钥文件
 - 创建CA证书文件
 - 公钥加密---私钥解密
 - 私钥加密---公钥解密
 - 签名---验签
 - CA证书公钥加密---私钥解密

## 组件结构


```
src/        
tests/
vendor/
```


## 安装

``` bash
    composer require topphp/topphp-rsa
```

## 用法

```php
    命名空间引用：use Topphp\TopphpRsa\RSA2;
    $rsaObj = new RSA2();
    $data = "要加密的数据";
    $eData = $rsaObj->cryptCode($data, "E");// E 加密
    $dData = $rsaObj->cryptCode($eData, "D");// D 解密
    
    组件还包含如下方法：
        createSecretKey() // 创建公私钥文件
        createCertificate() // 创建CA证书文件
        cryptReCode() // 私钥加密---公钥解密
        getSign() // 私钥生成签名
        checkSign() // 公钥验签
        certEncrypt() // CA证书公钥加密
        certDecrypt() // CA证书私钥解密
        
    更多详细使用方式参看单元测试文件
```

## 修改日志

有关最近更改的内容的详细信息，请参阅更改日志（[CHANGELOG](CHANGELOG.md)）。

## 测试

``` bash
    ./vendor/bin/phpunit tests/RSA2Test.php
```

## 贡献

详情请参阅贡献（[CONTRIBUTING](CONTRIBUTING.md)）和行为准则（[CODE_OF_CONDUCT](CODE_OF_CONDUCT.md)）。


## 安全

如果您发现任何与安全相关的问题，请发送电子邮件至sleep@kaitoocn.com，而不要使用问题跟踪器。

## 信用

- [topphp][link-author]
- [All Contributors][link-contributors]

## 许可证

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/topphp/component-builder.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/topphp/component-builder/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/topphp/component-builder.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/topphp/component-builder.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/topphp/component-builder.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/topphp/component-builder
[link-travis]: https://travis-ci.org/topphp/component-builder
[link-scrutinizer]: https://scrutinizer-ci.com/g/topphp/component-builder/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/topphp/component-builder
[link-downloads]: https://packagist.org/packages/topphp/component-builder
[link-author]: https://github.com/topphp
[link-contributors]: ../../contributors
