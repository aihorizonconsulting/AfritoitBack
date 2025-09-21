<?php
namespace App\Logging;

use Illuminate\Support\Facades\Log;
use Monolog\Logger;

class CustomLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('database');

        // Niveau minimum configurable (par défaut : Logger::ERROR)
        $level = $config['level'] ?? Logger::ERROR;

        $logger->pushHandler(new DatabaseLoggerHandler($level));

        return $logger;
    }
}
