<?php namespace Amegatron\Cryptoapi\Traits;

trait RsaAesControllerTrait {
    public function postInit() {
        if (!(\Input::has('key') && \Input::has('iv'))) {
            return 'ERROR 1';
        }

        $crypt = \App::make('CryptographyInterface');

        extract(\Input::only('key', 'iv'));
        try {
            $key = $crypt->asymmetricDecrypt($key);
            $iv = $crypt->asymmetricDecrypt($iv);
        } catch (\Exception $e) {
            $key = null;
            $iv = null;
        }

        if (!($key && $iv)) {
            return 'ERROR 2';
        }

        $crypt->initSymmetric(array(
            'key'   => $key,
            'iv'    => $iv,
        ));

        return 'OK';
    }
}
