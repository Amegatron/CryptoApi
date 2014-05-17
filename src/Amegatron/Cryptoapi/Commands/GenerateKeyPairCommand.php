<?php namespace Amegatron\Cryptoapi\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateKeyPairCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'cryptoapi:generatekeys';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generates a key pair';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$keySize = $this->option('keySize');
        $method = $this->option('method');

        switch($method) {
            case 'php':
                $generator = \App::make('cryptoapi.generator.php');
                break;
            case 'openssl':
                $generator = \App::make('cryptoapi.generator.openssl');
                break;
            default:
                $this->error('Unknown key generation method specified.');
                $this->error('Supported methods are: php, openssl');
                exit;
                break;
        }

        $path = 'app/keys';
        if (!is_dir($path)) {
            $this->info('Creating directory ' . $path);
            mkdir($path);
        }

        $this->info('Generating keypair using ' . $keySize . ' key size');
        $generator->generateKeyPair($path, $keySize);
        $this->info("Keys were successfully generated in " . $path);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
        return array();
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
        return array(
            array('keySize', null, InputOption::VALUE_OPTIONAL, 'Key size', 1024),
            array('method', null, InputOption::VALUE_OPTIONAL, 'Key generation method', 'openssl'),
        );
    }

}
