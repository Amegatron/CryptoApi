<?php namespace Amegatron\Cryptoapi\Cryptography;

interface CryptographyInterface {

    /**
     * Initializes symmetric algorithm
     * @param $options
     * @return mixed
     */
    public function initSymmetric($options);

    /**
     * Initializes assymetric algorithm
     * @param $privateKeyFile
     * @return mixed
     */
    public function initAsymmetric($privateKeyFile);

    /**
     * Performs symmetric encryption
     * @param $data
     * @return mixed
     */
    public function symmetricEncrypt($data);

    /**
     * Performs symmetric decryption
     * @param $data
     * @return mixed
     */
    public function symmetricDecrypt($data);

    /**
     * Performs asymmetric ecryption
     * @param $data
     * @return mixed
     */
    public function asymmetricEncrypt($data);

    /**
     * Performs asymmetric descryption
     * @param $data
     * @return mixed
     */
    public function asymmetricDecrypt($data);

    /**
     * Performs data signing
     * @param $data
     * @return mixed
     */
    public function sign($data);
}
