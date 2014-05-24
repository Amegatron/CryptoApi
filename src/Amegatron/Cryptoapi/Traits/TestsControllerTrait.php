<?php namespace Amegatron\Cryptoapi\Traits;

trait TestsControllerTrait {

    /*
     * This test just returns the message param passed to it
     */
    public function postTestEcho() {
        $message = \DecryptedInput::get('message');
        \Log::info('Got message from client: ' . $message);

        return json_encode(array('message'  => $message));
    }
}
