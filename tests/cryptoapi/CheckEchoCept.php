<?php

$I = new CryptoApiGuy($scenario);
$I->wantTo('test crypto api echo');
$I->usePublicCertificate('../../../app/keys/public.crt');

$key = md5(mt_rand());
$iv = substr(md5(mt_rand()), 0, 16);
$I->useAESKey($key, $iv, 256);

$I->enableCookies();
$I->sendPOST('init', [
    'key'   => base64_encode($I->getRsa()->encrypt(base64_encode($key))),
    'iv'    => base64_encode($I->getRsa()->encrypt(base64_encode($iv))),
]);
$I->seeResponseCodeIs(200);
$I->seeResponseContains('OK');

$message = "Hello World!";
$I->sendPOST('test-echo', [
    'message'   => base64_encode($I->getAes()->encrypt($message)),
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeValidSignature();
$I->seeMessageInResponse($message);

