<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;

include __DIR__ . "/../config/bootstrap.php";

exec('php ../bin/console doctrine:migrations:migrate -q');
exec('php ../bin/console doctrine:fixtures:load -n -q');
