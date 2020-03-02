<?php
/**
 * 凯拓软件 [临渊羡鱼不如退而结网,凯拓与你一同成长]
 * Project: topphp-rsa
 * Date: 2020/2/14 20:00
 * Author: bai <344147805@qq.com>
 */

/**
 * Description - RsaHelper.php
 *
 * RSA 助手类
 */


namespace Topphp\TopphpRsa;


class RsaHelper
{
    /**
     * 公私钥配置【建议默认】
     * @var array
     */
    private static $secretOption = [
        "openssl_conf_dir" => "/usr/lib/ssl/openssl.cnf", // 此为一般情况下Linux的openssl配置目录
        "save_dir"         => "", // 生成文件的保存目录，为空默认根目录下pem/
        "pub_name"         => "public_key.pem", // 公钥文件名（不允许包含路径，建议默认）
        "pri_name"         => "private_key.pem", // 私钥文件名（不允许包含路径，建议默认）
    ];

    /**
     * CA证书配置【建议默认】
     * @var array
     */
    private static $certOption = [
        "openssl_conf_dir" => "/usr/lib/ssl/openssl.cnf", // 此为一般情况下Linux的openssl配置目录
        "save_dir"         => "", // 生成文件的保存目录，为空默认根目录下pem/
        "pri_pass"         => "eEYaV8", // 私钥密钥
        "expire_day"       => 365, // 有效天数 默认 365天
        "cert_pub_name"    => "csr_public.cert", // 公钥文件名（不允许包含路径，建议默认）
        "cert_pri_name"    => "csr_private.pfx", // 私钥文件名（不允许包含路径，建议默认）
        // DN --  Distinguished Name,证书持有人的唯一标识符。
        "dn"               => [
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
        ],
    ];

    /**
     * @var RSA2
     */
    private static $handler;

    /**
     * 返回项目根目录
     * @return mixed
     * @author bai
     */
    private static function rootDir()
    {
        try {
            if (!isset($_SERVER['DOCUMENT_ROOT'])) {
                return root_path();
            }
            return dirname($_SERVER['DOCUMENT_ROOT']);
        } catch (\Exception $e) {
            return "";
        }
    }

    /**
     * 返回公私钥证书目录
     * @return string
     * @author bai
     */
    private static function pemDir()
    {
        return self::rootDir() . DIRECTORY_SEPARATOR . "pem";
    }

    /**
     * isJson
     * @param $str
     * @return bool
     * @author bai
     */
    private static function isJson($str)
    {
        return !is_null(json_decode($str));
    }

    /**
     * publicKey
     * @return string
     * @author bai
     */
    private static function publicKey()
    {
        return self::pemDir() . DIRECTORY_SEPARATOR . "public_key.pem";
    }

    /**
     * privateKey
     * @return string
     * @author bai
     */
    private static function privateKey()
    {
        return self::pemDir() . DIRECTORY_SEPARATOR . "private_key.pem";
    }

    /**
     * certPublicKey
     * @return string
     * @author bai
     */
    private static function certPublicKey()
    {
        return self::pemDir() . DIRECTORY_SEPARATOR . "csr_public.cert";
    }

    /**
     * certPrivateKey
     * @return string
     * @author bai
     */
    private static function certPrivateKey()
    {
        return self::pemDir() . DIRECTORY_SEPARATOR . "csr_private.pfx";
    }

    /**
     * 返回原始RSA2对象句柄
     * @param string $publicKeyFile
     * @param string $privateKeyFile
     * @return RSA2
     * @throws \Exception
     * @author bai
     */
    public static function handler(string $publicKeyFile = '', string $privateKeyFile = '')
    {
        self::$handler = new RSA2($publicKeyFile, $privateKeyFile);
        return self::$handler;
    }

    /**
     * 生成公私钥文件
     * @param array $option
     * @return bool
     * @throws \Exception
     * @author bai
     */
    public static function generateSecretKey(array $option = [])
    {
        $config = array_merge(self::$secretOption, $option);
        if (empty($config['save_dir'])) {
            if (empty(self::rootDir())) {
                return false;
            }
            $config['save_dir'] = self::pemDir();
        }
        $res = self::handler()->setOpensslConfDir($config['openssl_conf_dir'])
            ->setPubFileName($config['pub_name'])
            ->setPriFileName($config['pri_name'])
            ->setGenerateKeyConfig()
            ->setKeyDir($config['save_dir'])
            ->createSecretKey();
        if ($res === false) {
            return false;
        }
        return true;
    }

    /**
     * 生成CA证书文件
     * @param array $option
     * @return bool
     * @throws \Exception
     * @author bai
     */
    public static function generateCertificate(array $option = [])
    {
        $config = array_merge(self::$certOption, $option);
        if (empty($config['save_dir'])) {
            if (empty(self::rootDir())) {
                return false;
            }
            $config['save_dir'] = self::pemDir();
        }
        $res = self::handler()->setOpensslConfDir($config['openssl_conf_dir'])
            ->setGenerateKeyConfig("sha256", 2048, $config['dn'])
            ->setKeyDir($config['save_dir'])
            ->createCertificate($config['pri_pass'], (int)$config['expire_day'], $config['cert_pub_name'],
                $config['cert_pri_name']);
        if ($res === false) {
            return false;
        }
        return true;
    }

    /**
     * 【公钥加密---私钥解密】 之 加密（常用于加密解密）
     * @param mixed $data 要加密的数据 支持数组
     * @return string|bool
     * @throws \Exception
     * @author bai
     */
    public static function foPubEncrypt($data)
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : (string)$data;
        return self::handler(self::publicKey(), self::privateKey())->cryptCode($data, "E");
    }

    /**
     * 【公钥加密---私钥解密】 之 解密（常用于加密解密）
     * @param string $pubEncStr 公钥加密的字符串
     * @return string|bool
     * @throws \Exception
     * @author bai
     */
    public static function foPriDecrypt(string $pubEncStr)
    {
        $res = self::handler(self::publicKey(), self::privateKey())->cryptCode($pubEncStr, "D");
        if ($res === false) {
            return false;
        }
        if (self::isJson($res)) {
            $res = json_decode($res, true);
        }
        return $res;
    }

    /**
     * 【私钥加密---公钥解密】 之 加密（常用于签名验签）
     * @param mixed $data 要加密的数据 支持数组
     * @return string|bool
     * @throws \Exception
     * @author bai
     */
    public static function rePriEncrypt($data)
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : (string)$data;
        return self::handler(self::publicKey(), self::privateKey())->cryptReCode($data, "E");
    }

    /**
     * 【私钥加密---公钥解密】 之 解密（常用于签名验签）
     * @param string $priEncStr 私钥加密的字符串
     * @return string|bool
     * @throws \Exception
     * @author bai
     */
    public static function rePubDecrypt(string $priEncStr)
    {
        $res = self::handler(self::publicKey(), self::privateKey())->cryptReCode($priEncStr, "D");
        if ($res === false) {
            return false;
        }
        if (self::isJson($res)) {
            $res = json_decode($res, true);
        }
        return $res;
    }

    /**
     * 生成签名
     * @param mixed $data 要签名的数据 支持数组
     * Tips：如果是数组将会对数组键值进行数据字典升序后把键值对通过 & 进行拼接并签名
     * @return bool|string
     * @throws \Exception
     * @author bai
     */
    public static function generateSignature($data)
    {
        if (is_array($data)) {
            $string = self::handler()->getSignString($data);
            if ($string === false) {
                return false;
            }
        } elseif (is_string($data)) {
            $string = $data;
        } else {
            return false;
        }
        return self::handler(self::publicKey(), self::privateKey())->getSign($string);
    }

    /**
     * 验证签名
     * @param string $signStr 签名字符串
     * @param mixed $data 待签名的数据
     * Tips：方法将会把待签名的数据按照规则进行生成签名后与$signStr签名字符串进行比对，并返回布尔值
     * @return bool
     * @throws \Exception
     * @author bai
     */
    public static function verifySignature(string $signStr, $data)
    {
        if (is_array($data)) {
            $string = self::handler()->getSignString($data);
            if ($string === false) {
                return false;
            }
        } elseif (is_string($data)) {
            $string = $data;
        } else {
            return false;
        }
        return self::handler(self::publicKey(), self::privateKey())->checkSign($signStr, $string);
    }

    /**
     * 【CA证书公钥加密---私钥解密】 之 加密
     * @param mixed $data 要加密的数据 支持数组
     * @return string
     * @throws \Exception
     * @author bai
     */
    public static function certEncrypt($data)
    {
        $data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : (string)$data;
        return self::handler()->certEncrypt($data, self::certPublicKey());
    }

    /**
     * 【CA证书公钥加密---私钥解密】 之 解密
     * @param string $certEncStr CA证书加密字符串
     * @param string $priPass 创建CA证书时使用的私钥密钥
     * @return bool|mixed
     * @throws \Exception
     * @author bai
     */
    public static function certDecrypt(string $certEncStr, string $priPass = "eEYaV8")
    {
        $res = self::handler()->certDecrypt($certEncStr, self::certPrivateKey(), $priPass);
        if ($res === false) {
            return false;
        }
        if (self::isJson($res)) {
            $res = json_decode($res, true);
        }
        return $res;
    }

    /**
     * 获取内部错误信息
     * @return string
     * @throws \Exception
     * @author bai
     */
    public static function getErrorMsg()
    {
        return self::$handler->errLog();
    }
}