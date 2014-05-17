<?php namespace Amegatron\Cryptoapi;

use Amegatron\Cryptoapi\Commands\GenerateKeyPairCommand;
use Amegatron\Cryptoapi\Commands\TestCommand;
use Amegatron\Cryptoapi\Cryptography\DecryptedInput;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;
use Whoops\Example\Exception;

class CryptoapiServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('amegatron/cryptoapi');
	}

    /**
     * Register the service provider.
     *
     * @throws \InvalidArgumentException
     * @return void
     */
	public function register()
	{
        $this->registerKeyGenerators();

        $this->registerCommands();

        $this->registerCryptography();
	}

    protected function registerKeyGenerators() {
        \App::bind(
            'cryptoapi.generator.php',
            'Amegatron\\Cryptoapi\\KeyGenerators\\PhpKeyGenerator'
        );

        \App::bind(
            'cryptoapi.generator.openssl',
            'Amegatron\\Cryptoapi\\KeyGenerators\\OpenSslKeyGenerator'
        );
    }

    protected function registerCommands() {
        $this->app['cryptoapi.generatekeys'] = $this->app->share(function($app) {
            return new GenerateKeyPairCommand();
        });

        $this->commands('cryptoapi.generatekeys');
    }

    protected function registerCryptography() {
        $this->app['CryptographyInterface'] = $this->app->share(function() {
           return new Cryptography\RsaAesCryptography;
        });

        $this->app['decryptedinput'] = $this->app->share(function() {
            return new DecryptedInput();
        });

        AliasLoader::getInstance()->alias('DecryptedInput', 'Amegatron\Cryptoapi\Facades\DecryptedInput');
    }

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
            'cryptoapi.generator.php',
            'cryptoapi.generator.openssl',
            'cryptoapi.generatekeys',
            'CryptographyInterface',
            'decryptedinput',
        );
	}

}
