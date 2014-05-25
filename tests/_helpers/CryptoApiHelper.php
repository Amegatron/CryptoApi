<?php
namespace Codeception\Module;

// here you can define custom functions for CryptoApiGuy
function base64_url_encode($string) {
    return str_replace("+", "-", str_replace("/", "_", base64_encode($string)));
}

function base64_url_decode($string) {
    return base64_decode(str_replace(array('_','-'), array('/','+'), $string));
}

class CryptoApiHelper extends \Codeception\Module
{
    /**
     * @var \Crypt_AES
     */
    protected $aes;
    public $aesKey;
    public $aesIV;

    /**
     * @var \Crypt_RSA
     */
    protected $rsa;

    public function usePublicCertificate($publicKeyFile) {
        $this->initRsa($publicKeyFile);
    }

    public function useAESKey($key, $iv = null, $keySize = 256) {
        $this->initAes($key, $iv, $keySize);
    }

    public function seeValidSignature() {
        $response = $this->getModule('REST')->response;
        $response = json_decode($response);
        $sign = base64_url_decode($response->sign);
        $this->rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
        $this->assertTrue($this->rsa->verify($response->data, $sign));
    }

    public function seeMessageInResponse($message) {
        $response = $this->getModule('REST')->response;
        $response = json_decode($response);
        $data = base64_url_decode($response->data);
        $data = $this->aes->decrypt($data);
        file_put_contents('./dump.txt', $data);
        $data = json_decode($data);
        $this->assertEquals($message, $data->message);
    }

    public function enableCookies() {
        $cookiePlugin = new \Guzzle\Plugin\Cookie\CookiePlugin(new \Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar());
        $this->getModule('PhpBrowser')->guzzle->addSubscriber($cookiePlugin);
    }

    /**
     * @return \Crypt_AES
     */
    public function getAes()
    {
        return $this->aes;
    }

    /**
     * @return \Crypt_RSA
     */
    public function getRsa()
    {
        return $this->rsa;
    }

    protected function initRsa($publicKeyFile) {
        if (!file_exists($publicKeyFile) || !is_readable($publicKeyFile)) {
            throw new \Exception('Public key file does not exist or is not readable.');
        }
        $public_key = file_get_contents($publicKeyFile);

        $this->rsa = new \Crypt_RSA();
        $x509 = new \File_X509();
        $x509->loadX509($public_key);
        $this->rsa->loadKey($x509->getPublicKey());
        $this->rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        $this->rsa->setHash('sha1');
    }

    protected function initAes($key, $iv, $keySize) {
        $this->aes = new \Crypt_AES();
        $this->aes->setKeyLength($keySize);

        $this->aesKey = $key;
        $this->aesIV = $iv;

        $this->aes->setKey($this->aesKey);
        $this->aes->setIV($this->aesIV);
    }

    /**
     * @return mixed
     */
    public function getAesIV()
    {
        return $this->aesIV;
    }

    /**
     * @return mixed
     */
    public function getAesKey()
    {
        return $this->aesKey;
    }

}
