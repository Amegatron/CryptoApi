<?php namespace Amegatron\Cryptoapi\Filters;

use Amegatron\Cryptoapi\Helpers\Base64;

/**
 * Class OutgoingCryptFilter
 * Encrypts and signs the response
 *
 */
class OutgoingCryptFilter {

    private $crypt;

    public function __construct() {
        $this->crypt = \App::make('CryptographyInterface');
    }

    /**
     * @param $route
     * @param $request
     * @param $response
     */
    public function filter($route, $request, $response) {
        $content = $response->getOriginalContent();
        if (!is_string($content)) {
            $content = json_encode($content);
        }

        $content = Base64::UrlEncode($this->crypt->symmetricEncrypt($content));
        $sign = Base64::UrlEncode($this->crypt->sign($content));

        $response->setContent(['data' => $content, 'sign' => $sign]);
    }
}
