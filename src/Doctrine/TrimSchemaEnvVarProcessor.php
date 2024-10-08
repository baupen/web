<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Doctrine;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

class TrimSchemaEnvVarProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, \Closure $getEnv): string
    {
        $env = $getEnv($name);

        return substr($env, strpos($env, '://') + 3);
    }

    public static function getProvidedTypes(): array
    {
        return [
            'stripschema' => 'string',
        ];
    }
}
