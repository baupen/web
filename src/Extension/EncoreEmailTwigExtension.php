<?php

/*
 * This file is part of the baupen project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Extension;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EncoreEmailTwigExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private ContainerInterface $container;
    private string $publicDir;

    public function __construct(ContainerInterface $container, string $publicDir)
    {
        $this->container = $container;
        $this->publicDir = $publicDir;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_css_source', [$this, 'getEncoreEntryCssSource']),
        ];
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        // TODO: try to inject directly
        $files = $this->container
            ->get(EntrypointLookupInterface::class)
            ->getCssFiles($entryName);

        $source = '';
        foreach ($files as $file) {
            if (str_starts_with($file, 'http://localhost:8080/')) {
                $file = substr($file, strlen('http://localhost:8080/'));
            }

            $source .= file_get_contents($this->publicDir.'/'.$file);
        }

        return $source;
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntrypointLookupInterface::class,
        ];
    }
}
