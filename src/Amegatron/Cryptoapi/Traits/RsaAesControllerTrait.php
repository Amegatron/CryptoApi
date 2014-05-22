<?php namespace Amegatron\Cryptoapi\Traits;

trait RsaAesControllerTrait {
    public function postInit() {
        if (!(Input::has('key') && Input::has('iv'))) {
            return 'ERROR 1';
        }

        $crypt = App::make('CryptographyInterface');

        extract(Input::only('key', 'iv'));
        $key = $crypt->asymmetricDecrypt($key);
        $iv = $crypt->asymmetricDecrypt($iv);

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
