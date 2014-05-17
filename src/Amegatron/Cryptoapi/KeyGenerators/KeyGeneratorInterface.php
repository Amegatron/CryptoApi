<?php namespace Amegatron\Cryptoapi\KeyGenerators;

interface KeyGeneratorInterface {
    public function generateKeyPair($keyPath, $keySize);
}
