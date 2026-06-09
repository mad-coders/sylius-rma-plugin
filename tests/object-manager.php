<?php

declare(strict_types=1);

use Tests\Madcoders\SyliusRmaPlugin\Application\Kernel;

require __DIR__ . '/Application/config/bootstrap.php';

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'test', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
