<?php namespace Amegatron\Cryptoapi\KeyGenerators;

class PhpKeyGenerator implements KeyGeneratorInterface {

    public function generateKeyPair($keyPath, $keySize = 1024)
    {
        $privKey = new \Crypt_RSA();
        extract($privKey->createKey($keySize));
        $privKey->loadKey($privatekey);

        $pubKey = new \Crypt_RSA();
        $pubKey->loadKey($publickey);
        $pubKey->setPublicKey();

        $subject = new \File_X509();
        $subject->setDNProp('id-of-organization', 'phpseclib demo cert');
        $subject->setPublicKey($pubKey);

        $issuer = new \File_X509();
        $issuer->setPrivateKey($privKey);
        $issuer->setDN($subject->getDN());

        $x509 = new \File_X509();

        $result = $x509->sign($issuer, $subject);
        file_put_contents($keyPath . '/private.key', $privKey->getPrivateKey());
        file_put_contents($keyPath . '/public.crt', $x509->saveX509($result));
    }
}
