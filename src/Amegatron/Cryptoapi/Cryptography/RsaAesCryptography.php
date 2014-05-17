<?php namespace Amegatron\Cryptoapi\Cryptography;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Amegatron\Cryptoapi\Helpers\Base64;

class RsaAesCryptography implements CryptographyInterface {
    /**
     * RSA instance
     * @var \Crypt_RSA
     */
    protected $rsa;

    /**
     * RSA private key
     * @var string
     */
    protected $rsaPrivateKey;

    /**
     * AES instance
     * @var \Crypt_AES
     */
    protected $aes;

    /**
     * Whether RSA instance is initialized
     * @var bool
     */
    private $isRsaInitialized = false;

    /**
     * Whether AES instance is initialized
     * @var bool
     */
    private $isAesInitialized = false;

    /**
     * Initializes AES instance using either provided $options or session values
     * @param array $options Array of options, containing 'key' and 'iv' values
     * @return mixed|void
     * @throws Exception
     */
    public function initSymmetric($options = array()) {
        if (empty($options) && Session::has('aes_key') && Session::has('aes_iv')) {
            $options = array(
                'key'   => Session::get('aes_key'),
                'iv'    => Session::get('aes_iv'),
            );
        }

        if (!(isset($options['key']) && isset($options['iv']))) {
            \Log::error("Either key or iv not set");
            throw new \Exception("Either key or iv not set");
        }

        Session::put('aes_key', $options['key']);
        Session::put('aes_iv', $options['iv']);

        $aes = new \Crypt_AES(CRYPT_AES_MODE_CBC);
        $aes->setKeyLength(256);
        $aes->setKey(Base64::UrlDecode($options['key']));
        $aes->setIV(Base64::UrlDecode($options['iv']));
        $aes->enablePadding();

        $this->aes = $aes;
        $this->isAesInitialized = true;
    }

    /**
     * Initializes the RSA instance using either provided private key file or default value
     * @param String $privateKeyFile Path to private key file
     * @return mixed|void
     * @throws Exception
     */
    public function initAsymmetric($privateKeyFile = '') {
        if (!$privateKeyFile) {
            $privateKeyFile = app_path() . '/keys/private.key';
        }
        if (!\File::exists($privateKeyFile)) {
            \Log::error("Error reading private key file.");
            throw new \Exception("Error reading private key file.");
        }

        $this->rsaPrivateKey = \File::get($privateKeyFile);

        $rsa = new \Crypt_RSA();
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        $rsa->loadKey($this->rsaPrivateKey);

        $this->rsa = $rsa;
        $this->isRsaInitialized = true;
    }

    /**
     * Decrypts RSA-encrypted data
     * @param String $data Data to decrypt
     * @return String
     */
    public function asymmetricDecrypt($data) {
        if (!$this->isRsaInitialized) {
            $this->initAsymmetric();
        }

        return $this->rsa->decrypt(Base64::UrlDecode($data));
    }

    /**
     * Encrypts data using RSA
     * @param String $data Data to encrypt
     * @return String
     */
    public function asymmetricEncrypt($data) {
        if (!$this->isRsaInitialized) {
            $this->initAsymmetric();
        }

        return Base64::UrlEncode($this->rsa->encrypt($data));
    }

    /**
     * Signs provided data
     * @param String $data Data to sign
     * @throws \Exception
     * @return string Signed data
     */
    public function sign($data) {
        if (!$this->isRsaInitialized) {
            $this->initAsymmetric();
        }

        if (!function_exists('openssl_sign')) {
            throw new \Exception("OpenSSL is not enabled.");
        }

        $signature = '';
        $keyId = openssl_get_privatekey($this->rsaPrivateKey);
        openssl_sign($data, $signature, $keyId);
        openssl_free_key($keyId);

        return $signature;
    }

    /**
     * Encrypts data using AES
     * @param String $data Data to encrypt
     * @return String
     */
    public function symmetricEncrypt($data) {
        if (!$this->isAesInitialized) {
            $this->initSymmetric();
        }

        return $this->aes->encrypt($data);
    }

    /**
     * Decrypts AES encrypted data
     * @param String $data Data to decrypt
     * @return String
     */
    public function symmetricDecrypt($data) {
        if (!$this->isAesInitialized) {
            $this->initSymmetric();
        }

        return $this->aes->decrypt($data);
    }
}
