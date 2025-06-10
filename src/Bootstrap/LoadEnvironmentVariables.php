<?php

declare(strict_types=1);

namespace Laravel\Lumen\Bootstrap;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidFileException;
use Illuminate\Support\Env;
use Symfony\Component\Console\Output\ConsoleOutput;

class LoadEnvironmentVariables
{
  /**
   * Create a new loads environment variables instance.
   *
   * @param  string  $filePath
   * @param  string|null  $fileName
   * @return void
   */
  public function __construct(
    /**
     * The directory containing the environment file.
     */
    protected $filePath,
    /**
     * The name of the environment file.
     */
    protected $fileName = null
  ) {}

  /**
   * Setup the environment variables.
   *
   * If no environment file exists, we continue silently.
   */
  public function bootstrap(): void
  {
    try {
      $this->createDotenv()->safeLoad();
    } catch (InvalidFileException $e) {
      $this->writeErrorAndDie([
        'The environment file is invalid!',
        $e->getMessage(),
      ]);
    }
  }

  /**
   * Create a Dotenv instance.
   *
   * @return \Dotenv\Dotenv
   */
  protected function createDotenv()
  {
    return Dotenv::create(
      Env::getRepository(),
      $this->filePath,
      $this->fileName
    );
  }

  /**
   * Write the error information to the screen and exit.
   *
   * @param  string[]  $errors
   * @return void
   */
  protected function writeErrorAndDie(array $errors)
  {
    $output = (new ConsoleOutput)->getErrorOutput();

    foreach ($errors as $error) {
      $output->writeln($error);
    }

    exit(1);
  }
}
