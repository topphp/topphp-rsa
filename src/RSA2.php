<?php
/**
 * 凯拓软件 [临渊羡鱼不如退而结网,凯拓与你一同成长]
 * Project: topphp-rsa
 * Date: 2020/2/14 20:00
 * Author: bai <344147805@qq.com>
 */
declare(strict_types=1);

namespace Topphp\TopphpRsa;

class RSA2
{
    // 初始化属性【无需修改】
    private $publicKey = null;
    private $privateKey = null;
    private $rsaError = "";
    private $errorLog = "";
    private $keyDir = null;
    private $generateKeyConf = [];
    private $dn = [];

    // 基础配置【可手动修改，也可调用配置方法修改】
    private $priFileName = "private_key.pem";
    private $pubFileName = "public_key.pem";
//    private $opensslConfDir = "I:/phpstudy_pro/Extensions/php/php7.3.4nts/extras/ssl/openssl.cnf";// win 环境
    private $opensslConfDir = "/usr/lib/ssl/openssl.cnf";// linux 环境

    /**
     * 构造函数
     *
     * @param $publicKeyFile string 公钥文件（验签和加密时传入）
     * @param $privateKeyFile string 私钥文件（签名和解密时传入）
     * @throws \Exception
     * @author bai
     */
    public function __construct($publicKeyFile = '', $privateKeyFile = '')
    {
        if ($publicKeyFile) {
            $this->getPublicKey($publicKeyFile);
        }
        if ($privateKeyFile) {
            $this->getPrivateKey($privateKeyFile);
        }
        // 设置证书路径与默认配置
        $this->keyDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . "pem";
        if (empty($this->generateKeyConf)) {
            $this->setGenerateKeyConfig();
        }
        if (empty($this->dn)) {
            // DN --  Distinguished Name,证书持有人的唯一标识符。
            $this->dn = [
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
        }
    }

    /**
     * 自定义错误处理
     *
     * @param $errorMsg
     * @return string
     * @throws \Exception
     * @author bai
     */
    private function error($errorMsg)
    {
        $this->rsaError = 'RSA Error: ' . $errorMsg;
        throw new \Exception($this->rsaError);
    }

    /**
     * 获取公钥文件内容
     *
     * @param $file
     * @throws \Exception
     * @author bai
     */
    private function getPublicKey($file)
    {
        $keyContent = $this->readFile($file, "publicKey file");
        if ($keyContent) {
            //这个函数可用来判断公钥是否是可用的
            $this->publicKey = openssl_get_publickey($keyContent);
        }
    }

    /**
     * 获取私钥文件内容
     *
     * @param $file
     * @throws \Exception
     * @author bai
     */
    private function getPrivateKey($file)
    {
        $keyContent = $this->readFile($file, "privateKey file");
        if ($keyContent) {
            //这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
            $this->privateKey = openssl_get_privatekey($keyContent);
        }
    }

    /**
     * 读取文件内容
     *
     * @param $file
     * @param string $type
     * @return bool|false|string
     * @throws \Exception
     * @author bai
     */
    private function readFile($file, $type = "file")
    {
        $ret = false;
        if (!file_exists($file)) {
            $this->error("The {$type} {$file} is not exists");
        } else {
            $ret = file_get_contents($file);
        }
        return $ret;
    }


    //************************************ --- RSA公共方法 --- ****************************************//


    /**
     * 自定义错误日志【可用于外部获取错误信息】
     *
     * @return string
     * @author bai
     */
    public function errLog()
    {
        return $this->errorLog;
    }

    /**
     * 返回项目根目录
     *
     * @return mixed
     * @author bai
     */
    public function rootDir()
    {
        return dirname($_SERVER['DOCUMENT_ROOT']);
    }

    /**
     * 格式化私钥
     *
     * @param $priKey
     * @return string
     * @author bai
     */
    public function formatPriKey($priKey)
    {
        if (empty($priKey)) {
            return false;
        }
        $priKey = str_replace("-----BEGIN PRIVATE KEY-----", "", $priKey);
        $priKey = str_replace("-----END PRIVATE KEY-----", "", $priKey);
        $fKey   = "-----BEGIN PRIVATE KEY-----\n";
        $fKey   .= wordwrap(preg_replace('/[\r\n]/', '', $priKey), 64, "\n", true);
        $fKey   .= "\n-----END PRIVATE KEY-----";
        return $fKey;
    }

    /**
     * 格式化公钥
     *
     * @param $pubKey
     * @return string
     * @author bai
     */
    public function formatPubKey($pubKey)
    {
        if (empty($pubKey)) {
            return false;
        }
        $pubKey = str_replace("-----BEGIN PUBLIC KEY-----", "", $pubKey);
        $pubKey = str_replace("-----END PUBLIC KEY-----", "", $pubKey);
        $fKey   = "-----BEGIN PUBLIC KEY-----\n";
        $fKey   .= wordwrap(preg_replace('/[\r\n]/', '', $pubKey), 64, "\n", true);
        $fKey   .= "\n-----END PUBLIC KEY-----";
        return $fKey;
    }

    /**
     * 设置公私钥&证书目录
     *
     * @param null $dir
     * @return $this
     * @author bai
     */
    public function setKeyDir($dir = null)
    {
        if (!empty($dir)) {
            $this->keyDir = $dir;
        }
        !is_dir($this->keyDir) && @mkdir($this->keyDir, 0755, true);
        return $this;
    }

    /**
     * 设置私钥名
     *
     * @param string $privateFilename
     * @return $this
     * @author bai
     */
    public function setPriFileName($privateFilename = "private_key.pem")
    {
        $this->priFileName = $privateFilename;
        return $this;
    }

    /**
     * 设置公钥名
     *
     * @param string $publicFilename
     * @return $this
     * @author bai
     */
    public function setPubFileName($publicFilename = "public_key.pem")
    {
        $this->pubFileName = $publicFilename;
        return $this;
    }

    /**
     * 设置Openssl配置文件路径
     *
     * @param $opensslConfDir
     * @return $this
     * @author bai
     */
    public function setOpensslConfDir($opensslConfDir)
    {
        $this->opensslConfDir = $opensslConfDir;
        return $this;
    }

    /**
     * 设置生成公私钥&证书配置
     *
     * @param string $encryption // MD5 sha1 sha256 sha512
     * @param int $bitType // 1024 2048 4096
     * @param array $dn // Distinguished Name,证书持有人的唯一标识符
     * @return $this
     * @author bai
     */
    public function setGenerateKeyConfig($encryption = "sha256", $bitType = 2048, $dn = [])
    {
        $config                = [
            "digest_alg"       => $encryption,
            "private_key_bits" => $bitType,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
            "config"           => $this->opensslConfDir
        ];
        $this->generateKeyConf = $config;
        if (!empty($dn)) {
            $this->dn = $dn;
        }
        return $this;
    }

    /**
     * 创建公私钥【需要开启openssl扩展，并配置好openssl.cnf文件路径】
     *
     * @return $this|bool
     * @throws \Exception
     * @author bai
     */
    public function createSecretKey()
    {
        try {
            // 创建密钥对
            $res = openssl_pkey_new($this->generateKeyConf);
            // 生成私钥
            openssl_pkey_export($res, $priKey, null, $this->generateKeyConf);
            // 生成公钥
            $pubKey = openssl_pkey_get_details($res)['key'];
            // 写入文件
            !is_dir($this->keyDir) && @mkdir($this->keyDir, 0755, true);
            file_put_contents($this->keyDir . DIRECTORY_SEPARATOR . $this->priFileName, $priKey);
            file_put_contents($this->keyDir . DIRECTORY_SEPARATOR . $this->pubFileName, $pubKey);
            // 设置公私钥文件
            $this->getPrivateKey($this->keyDir . DIRECTORY_SEPARATOR . $this->priFileName);
            $this->getPublicKey($this->keyDir . DIRECTORY_SEPARATOR . $this->pubFileName);
            return $this;
        } catch (\Exception $e) {
            $this->errorLog = $e->getMessage();
            return false;
        }
    }

    /**
     * 创建CA证书
     *
     * @param string $priKeyPass // 私钥密码
     * @param int $expireDay //有效时长，单位为天
     * @param string $certPubFilename
     * @param string $certPriFilename
     * @return $this|bool
     * @author bai
     */
    public function createCertificate(
        $priKeyPass = 'secret',
        $expireDay = 365,
        $certPubFilename = 'csr_public.cert',
        $certPriFilename = 'csr_private.pfx'
    ) {
        try {
            // 配置
            $this->setGenerateKeyConfig("sha512", 4096);
            // 创建密钥对
            $res = openssl_pkey_new($this->generateKeyConf);
            // 获取证书
            $csr = openssl_csr_new($this->dn, $res, $this->generateKeyConf);
            // 证书签名
            $cert = openssl_csr_sign($csr, null, $res, $expireDay, $this->generateKeyConf);
            // 导出证书公钥
            openssl_x509_export_to_file($cert, $this->keyDir . DIRECTORY_SEPARATOR . $certPubFilename);
            // 导出证书私钥
            openssl_pkcs12_export_to_file($cert, $this->keyDir . DIRECTORY_SEPARATOR . $certPriFilename, $res,
                $priKeyPass);
            return $this;
        } catch (\Exception $e) {
            $this->errorLog = $e->getMessage();
            return false;
        }
    }

    /**
     * 公钥加密---私钥解密
     *
     * @param $string // 要处理的字符串
     * @param string $operation // 操作 E 加密 D 解密
     * @return string
     * @throws \Exception
     * @author bai
     */
    public function cryptCode($string, $operation = 'E')
    {
        try {
            $data = "";
            if ($operation == 'D') {
                if (empty($this->privateKey)) {
                    $this->error("The private_key certificate error");
                }
                openssl_private_decrypt(base64_decode($string), $data, $this->privateKey);// 私钥解密
            } else {
                if (empty($this->publicKey)) {
                    $this->error("The public_key certificate error");
                }
                openssl_public_encrypt($string, $data, $this->publicKey);// 公钥加密
                $data = base64_encode($data);
            }
            return $data;
        } catch (\Exception $e) {
            $this->errorLog = $e->getMessage();
            return false;
        }
    }

    /**
     * 私钥加密---公钥解密
     *
     * @param $string // 要处理的字符串
     * @param string $operation // 操作 E 加密 D 解密
     * @return string
     * @throws \Exception
     * @author bai
     */
    public function cryptReCode($string, $operation = 'E')
    {
        try {
            $data = "";
            if ($operation == 'D') {
                if (empty($this->publicKey)) {
                    $this->error("The public_key certificate error");
                }
                openssl_public_decrypt(base64_decode($string), $data, $this->publicKey);// 公钥解密
            } else {
                if (empty($this->privateKey)) {
                    $this->error("The private_key certificate error");
                }
                openssl_private_encrypt($string, $data, $this->privateKey);// 私钥加密
                $data = base64_encode($data);
            }
            return $data;
        } catch (\Exception $e) {
            $this->errorLog = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取待签名字符串（数据字典排序）
     *
     * @param $params // 参数数组
     * @return string
     * @author bai
     */
    public function getSignString($params)
    {
        try {
            unset($params['sign']);
            ksort($params);// 升序排序
            reset($params);// 移动指针到首位

            $pairs = array ();
            foreach ($params as $k => $v) {
                if (!empty($v)) {
                    $pairs[] = "$k=$v";
                }
            }
            return implode('&', $pairs);
        } catch (\Exception $e) {
            $this->errorLog = $e->getMessage();
            return false;
        }
    }

    /**
     * 私钥生成签名
     *
     * @param $string
     * @param int $signatureAlg // 参数列表
     *              OPENSSL_ALGO_SHA1【RSA】
     *              OPENSSL_ALGO_DSS1
     *              OPENSSL_ALGO_SHA224
     *              OPENSSL_ALGO_SHA256【RSA2】
     *              OPENSSL_ALGO_SHA384
     *              OPENSSL_ALGO_SHA512
     *              OPENSSL_ALGO_RMD160
     *              OPENSSL_ALGO_MD5
     *              OPENSSL_ALGO_MD4
     *              OPENSSL_ALGO_MD2
     * @return string
     * @throws \Exception
     * @author bai
     */
    public function getSign($string, $signatureAlg = OPENSSL_ALGO_SHA256)
    {
        try {
            if (empty($this->privateKey)) {
                $this->error("The private_key certificate error");
            }
            $signature = '';
            openssl_sign($string, $signature, $this->privateKey, $signatureAlg);
            openssl_free_key($this->privateKey);
            return base64_encode($signature);
        } catch (\Exception $e) {
            $this->errorLog = $e->getMessage();
            return false;
        }
    }

    /**
     * 公钥验签
     *
     * @param $sign // 签名
     * @param $toSign // 待签名字符串
     * @param int $signatureAlg
     * @return bool
     * @author bai
     */
    public function checkSign($sign, $toSign, $signatureAlg = OPENSSL_ALGO_SHA256)
    {
        try {
            if (empty($this->publicKey)) {
                $this->error("The public_key certificate error");
            }
            $result = openssl_verify($toSign, base64_decode($sign), $this->publicKey, $signatureAlg);
            openssl_free_key($this->publicKey);
            return $result === 1 ? true : false;
        } catch (\Exception $e) {
            $this->errorLog = $e->getMessage();
            return false;
        }
    }

    /**
     * CA证书公钥加密
     *
     * @param $data
     * @param $certPubFilepath
     * @return string
     * @author bai
     */
    public function certEncrypt($data, $certPubFilepath)
    {
        try {
            $pubKey = file_get_contents($certPubFilepath);
            $pubKey = openssl_x509_read($pubKey);
            openssl_public_encrypt($data, $crypted, $pubKey, OPENSSL_PKCS1_PADDING);
            return base64_encode($crypted);
        } catch (\Exception $e) {
            $this->errorLog = $e->getMessage();
            return false;
        }
    }

    /**
     * CA证书私钥解密
     *
     * @param $data
     * @param $certPriFilepath
     * @param string $priKeyPass
     * @return mixed
     * @author bai
     */
    public function certDecrypt($data, $certPriFilepath, $priKeyPass = 'secret')
    {
        try {
            $data = base64_decode($data);
            openssl_pkcs12_read(file_get_contents($certPriFilepath), $priKey, $priKeyPass);
            openssl_private_decrypt($data, $decrypt, $priKey['pkey'], OPENSSL_PKCS1_PADDING);
            return $decrypt;
        } catch (\Exception $e) {
            $this->errorLog = $e->getMessage();
            return false;
        }
    }
}
