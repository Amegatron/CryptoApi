<?php namespace Amegatron\Cryptoapi\Facades;

use Illuminate\Support\Facades\Facade;

class DecryptedInput extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'decryptedinput';
    }

}
