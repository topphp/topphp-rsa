<?php
/**
 * 凯拓软件 [临渊羡鱼不如退而结网,凯拓与你一同成长]
 * Project: topphp-rsa
 * Date: 2020/2/14 20:00
 * Author: bai <344147805@qq.com>
 */
declare(strict_types=1);

namespace Topphp\Test;

use PHPUnit\Framework\TestCase;
use Topphp\TopphpRsa\RSA2;

class RSA2Test extends TestCase
{
    /**
     * 测试创建公私钥文件
     * @throws \Exception
     * @author bai
     */
    public function testCreateSecretKey()
    {
        $rsaObj = new RSA2();
        //$opensslConfDir = "I:/phpstudy_pro/Extensions/php/php7.3.4nts/extras/ssl/openssl.cnf";// 此为win下phpStudy openssl配置文件
        $opensslConfDir = "/usr/lib/ssl/openssl.cnf";// 此为一般情况下Linux的openssl配置目录
        //$keyDir = $rsaObj->RootDir() . DIRECTORY_SEPARATOR . "pem";// 此为骨架项目根目录下的pem文件夹内（需要在服务环境下跑）
        $keyDir          = dirname(__FILE__) . DIRECTORY_SEPARATOR . "pem";// 单元测试先默认在组件测试文件夹下创建
        $publicFilename  = 'public_key.pem';// 设置公钥文件名（不允许包含路径，建议默认）默认 public_key.pem
        $privateFilename = 'private_key.pem';// 设置私钥文件名（不允许包含路径，建议默认）默认 private_key.pem
        $res             = $rsaObj->SetOpensslConfDir($opensslConfDir)
            ->SetPubFileName($publicFilename)
            ->SetPriFileName($privateFilename)
            ->SetGenerateKeyConfig()
            ->SetKeyDir($keyDir)
            ->CreateSecretKey();
        $this->assertTrue($res !== false);
    }

    /**
     * 测试创建CA证书文件
     * @throws \Exception
     * @author bai
     */
    public function testCreateCertificate()
    {
        $rsaObj = new RSA2();
        //$opensslConfDir = "I:/phpstudy_pro/Extensions/php/php7.3.4nts/extras/ssl/openssl.cnf";// 此为win下phpStudy openssl配置文件
        $opensslConfDir = "/usr/lib/ssl/openssl.cnf";// 此为一般情况下Linux的openssl配置目录
        //$keyDir = $rsaObj->RootDir() . DIRECTORY_SEPARATOR . "pem";// 此为骨架项目根目录下的pem文件夹内（需要在服务环境下跑）
        $keyDir          = dirname(__FILE__) . DIRECTORY_SEPARATOR . "pem";// 单元测试先默认在组件测试文件夹下创建
        $priKeyPass      = "secret";// 私钥密钥（可自定义，默认secret）
        $expireDay       = 365;// 有效天数 默认 365天
        $certPubFilename = 'csr_public.cert';// 公钥文件名（建议默认）
        $certPriFilename = 'csr_private.pfx';// 私钥文件名（建议默认）
        // DN --  Distinguished Name,证书持有人的唯一标识符。
        $dn  = [
            "countryName"            => "CN",
            // 所在国家 CN 中国 HK香港 TW台湾 MO澳门 UK 英国 GB 英国 US 美国 JP 日本
            "stateOrProvinceName"    => "Tianjin",
            // 所在省份
            "localityName"           => "Tianjin",
            // 所在城市
            "organizationName"       => "The Kaituo",
            // 注册人姓名
            "organizationalUnitName" => "PHP Topphp Team",
            // 组织名称
            "commonName"             => "Topphp",
            // 公共名称
            "emailAddress"           => "Topphp@example.com"
            // 邮箱
        ];
        $res = $rsaObj->SetOpensslConfDir($opensslConfDir)
            ->SetGenerateKeyConfig("sha256", 2048, $dn)
            ->SetKeyDir($keyDir)
            ->CreateCertificate($priKeyPass, $expireDay, $certPubFilename, $certPriFilename);
        $this->assertTrue($res !== false);
    }

    /**
     * 测试【公钥加密---私钥解密】
     * @throws \Exception
     * @author bai
     */
    public function testCryptCode()
    {
        //$keyDir = $rsaObj->RootDir() . DIRECTORY_SEPARATOR . "pem";// 此为骨架项目根目录下的pem文件夹内（需要在服务环境下跑）
        $keyDir      = dirname(__FILE__) . DIRECTORY_SEPARATOR . "pem";// 单元测试密钥目录
        $publicFile  = $keyDir . DIRECTORY_SEPARATOR . 'public_key.pem';// 公钥文件地址
        $privateFile = $keyDir . DIRECTORY_SEPARATOR . 'private_key.pem';// 私钥文件地址
        $rsaObj      = new RSA2($publicFile, $privateFile);
        $data        = "要加密的数据";
        $eData       = $rsaObj->CryptCode($data, "E");// E 加密
        $dData       = $rsaObj->CryptCode($eData, "D");// D 解密
        $this->assertTrue($dData == $data);
    }

    /**
     * 测试【私钥加密---公钥解密】
     * @throws \Exception
     * @author bai
     */
    public function testCryptReCode()
    {
        //$keyDir = $rsaObj->RootDir() . DIRECTORY_SEPARATOR . "pem";// 此为骨架项目根目录下的pem文件夹内（需要在服务环境下跑）
        $keyDir      = dirname(__FILE__) . DIRECTORY_SEPARATOR . "pem";// 单元测试密钥目录
        $publicFile  = $keyDir . DIRECTORY_SEPARATOR . 'public_key.pem';// 公钥文件地址
        $privateFile = $keyDir . DIRECTORY_SEPARATOR . 'private_key.pem';// 私钥文件地址
        $rsaObj      = new RSA2($publicFile, $privateFile);
        $data        = "要加密的数据";
        $eData       = $rsaObj->CryptReCode($data, "E");// E 加密
        $dData       = $rsaObj->CryptReCode($eData, "D");// D 解密
        $this->assertTrue($dData == $data);
    }

    /**
     * 测试【签名---验签】
     * @throws \Exception
     * @author bai
     */
    public function testSignature()
    {
        //$keyDir = $rsaObj->RootDir() . DIRECTORY_SEPARATOR . "pem";// 此为骨架项目根目录下的pem文件夹内（需要在服务环境下跑）
        $keyDir      = dirname(__FILE__) . DIRECTORY_SEPARATOR . "pem";// 单元测试密钥目录
        $publicFile  = $keyDir . DIRECTORY_SEPARATOR . 'public_key.pem';// 公钥文件地址
        $privateFile = $keyDir . DIRECTORY_SEPARATOR . 'private_key.pem';// 私钥文件地址
        $rsaObj      = new RSA2($publicFile, $privateFile);
        $string      = "要签名的字符串";
        $sign        = $rsaObj->GetSign($string);// 签名
        $verify      = $rsaObj->CheckSign($sign, $string);// 验签（param1 签名 param2 待签名的字符串）
        $this->assertTrue($verify);
    }

    /**
     * 测试【CA证书公钥加密---私钥解密】
     * @throws \Exception
     * @author bai
     */
    public function testCACert()
    {
        //$keyDir = $rsaObj->RootDir() . DIRECTORY_SEPARATOR . "pem";// 此为骨架项目根目录下的pem文件夹内（需要在服务环境下跑）
        $keyDir       = dirname(__FILE__) . DIRECTORY_SEPARATOR . "pem";// 单元测试密钥目录
        $certPubFile  = $keyDir . DIRECTORY_SEPARATOR . 'csr_public.cert';// CA公钥文件地址
        $certPriFile  = $keyDir . DIRECTORY_SEPARATOR . 'csr_private.pfx';// CA私钥文件地址
        $rsaObj       = new RSA2();
        $data         = "要加密的数据";
        $caEncodeData = $rsaObj->CertEncrypt($data, $certPubFile);// CA证书公钥加密（param1 要加密的字符串 param2 CA公钥文件地址）
        $priKeyPass   = 'secret';// 创建CA证书时使用的私钥密钥
        $caDecodeData = $rsaObj->CertDecrypt($caEncodeData, $certPriFile,
            $priKeyPass);// CA证书私钥解密（param1 要解密的字符串 param2 CA私钥文件地址 param3 私钥密钥）
        $this->assertTrue($caDecodeData == $data);
    }
}
