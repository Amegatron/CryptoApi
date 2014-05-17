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

Add the following to composer.json of your Laravel project: ???

After that run `composer update` in console and ensure the packaged has been downloaded.

After the package has been successfully downloaded, you should register its Service Provider. To do so, add the following element in your "providers" section of `app/config/app.php`: `'Amegatron\Cryptoapi\CryptoapiServiceProvider',`
