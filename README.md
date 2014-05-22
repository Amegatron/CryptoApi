CryptoApi
=========

Provides classes for implementing encrypted interaction with client applications

Imagine you have a client application, written in C# e.g., which needs secure (encrypted) interaction with server API.

This is where CryptoApi can help.

# Cryptography basics #

I'll start from saying that I'm not good at cryptography, but I understand the basics which helped me in writing this package.

How should encrypted interaction be implemented?

The algorithm is based on two cryptography algorithms: symmetric and asymmetric. In this case AES is used for symmetric, and RSA is used for asymmetric. If you don't know what does that mean - you should Google :) There are a lot of articles on this topic over Internet.

1. First, the server has its private key. The client has its corresponding public key (or certificate)
2. The client generates a random session key for symmmetric algorithm.
3. Then the client encrypts this key with asymmetric algo and sends to the server.
4. The server receives the key, and responds to client with either OK or ERROR, telling that it succeedeed or not in receiving the key. The server also stores this key in session for future communication with client.
5. Later, any data sent by client to server, is encrypted with symmetric algo using that generated key.
6. All responses from server to client is also encrypted with symmetric algo, but also is signed using server private key, so that client could verify, that data actually came from the intended server.

# Server-side implementation #

## Package installation ##

Add the following to composer.json of your Laravel project in the "require" section:
`"amegatron/cryptoapi": "dev-master"`

After that run `composer update` in console and ensure the packaged has been downloaded.

After the package has been successfully downloaded, you should register its Service Provider. To do so, add the following element in your "providers" section of `app/config/app.php`: `'Amegatron\Cryptoapi\CryptoapiServiceProvider',`

Also you must "regster" an outgoing encryption filter, provided by the package. Add the following in `app/filters.php`:

`Route::filter('cryptOut', 'Amegatron\Cryptoapi\Filters\OutgoingCryptFilter');`

## Generating key-pair ##

Next, you need to generate a key-pair for asymmetric encryption. To do so, run the following command in console:
`php artisan cryptoapi:generatekeys`

By default, two files will be created in `app/keys` folder: `private.key` and `public.crt`. `public.crt` is only needed by the client, so it may be deleted after you've embedded it in your client software. But `private.key` should be kept secure. As your server is pointing to `public` folder of your web-site, this file is not accessible over internet. And it should not be. never. Keep this file a secret. Nobody should now this key except your server.

By defalt, key size is 1024, but you can override the default value by specifying `--keySize=XXXX` option to the above artisan command. But 1024 is pretty enough for most cases. Also, I didn't manage to generate 2048-size key -- it took too long time for me and I cancelled the process :)

## Implementing server-side API ##

Now everything is ready for writing your API.

For simplicity, I'll sugest all server API logic will be within a single controller, called ApiController. So, create `ApiController.php` in `app/controllers` and create a route for it in `app/routes.php`:

`Route::controller('api', 'ApiController');`

As you may have guessed, all our encrypted interaction will be directed to `http://youdomain.com/api`.

Now, let's start implementing the controller.

### Initialization ###

First, we will need to implemtnt points 3 and 4 from the alogithm, listed at the beginning. The key for AES algorithm consists of two parts: `key` and `iv`. Both should be sent to `/api/init` using POST for example.

For this I suggest the following code:

```
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
```

What should be noted here? First, creation of `$crpyt` object through Laravel IoC. Currently, it creates `Amegatron\Cryptoapi\Cryptography\RsaAesCryptography` object which implements an interface `Amegatron\Cryptoapi\Cryptography\CryptographyIterface`. This object contains all methods we will need for encryption, decryption and signing.

Second, is the initialization of symmetric "driver" (`$crypt->initSymmetric(...);`). It will store the AES key in the session for further use.

Also note that responses from `/api/init` are not encrypted (for the case if this initialization fails). Also, response messages are not "speaking". The client should know what does e.g. "ERROR 1" means. This is done for not telling the possible hacker the internals of the API.

And lastly, if the following code suites you, you may just use pre-made trait for this, `Amegatron\Cryptoapi\Traits\RsaAesControllerTrait` which already has this `postInit` method.

```
use Amegatron\Cryptoapi\Traits\RsaAesControllerTrait;
```

Or if you are too lazy or your IDE does not insert the full name of the Trait automatically, you can just
```
use RsaAesControllerTrait;
```
The alias for the trait is registered in the package Service Provider.


### Application-specific API ###

For demonstrating purposes, let's assume that purpose of our server application is to validate client's licenses: the client sends a license key to server, and the server responses, telling, whether this license key is valid and further use of client software is allowed.

Before we continue coding, I should remind, that all data comming from the client is encrypted (using AES). For the purpose of convinient decrypting incoming data, I implemented `DecryptedInput` Facade, which is almost the same as Laravel's `Input` Facade except that it allows to get decrypted values from the request. You do not have to worry about adding an alias to `DecryptedInput` - it is added automatically in the package Server Provider for you.

Lets create a method for `api/checklicense` route:

```
public function postChecklicense() {

    $licenseKey = DecryptedInput::get('licenseKey');

    // Perform some logic to determine whether the received license key is valid or has not expired for example
    // Most probably retreiving this info from database.
    $licenseIsValid     = true;
    $licenseExpiresAt   = '2014-12-31 23:59:59';

    $response = array(
        'isValid'   => $licenseIsValid,
        'liceseExpiresAt'   => $licenseExpiresAt;
    );

    return $response;
}
```

Note that `postChecklicense` does not bother on encrypting outgoing data. For this we will use premade `cryptOut` filter, mentioned earlier. For this, let's create a constructor for our api controller:

```
public function __construct() {
    $this->afterFilter(
        'cryptOut',
        array(
            'except'   => array('postInit'),
        )
    );
}
```

`cryptOut` filter does two things: first, it encrypts the outgoing data; secondly, it signs the data. Summarizing, it sends ot the client JSON-encoded object with two fields:
1. data - encrypted data
2. sign - the signature of encrypted data

Note also, that this filter is applied to all controller methods except `postInit` as I stated earlier - the output of this method should not be encrypted.

See `Amegatron\Cryptoapi\Filters\OutgoingCryptFilter.php` if you are curious about how it does encryption and signing.

# Client-side example #

Here is a C# example of client: https://github.com/Amegatron/CryptoApiExample
