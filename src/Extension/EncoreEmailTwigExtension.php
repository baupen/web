<?php

/*
 * This file is part of the mangel.io project.
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
    private $container;
    private $publicDir;
    private $environment;

    public function __construct(ContainerInterface $container, string $publicDir, string $environment)
    {
        $this->container = $container;
        $this->publicDir = $publicDir;
        $this->environment = $environment;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_css_source', [$this, 'getEncoreEntryCssSource']),
        ];
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        $files = $this->container
            ->get(EntrypointLookupInterface::class)
            ->getCssFiles($entryName);

        $source = '';
        foreach ($files as $file) {
            if (0 === strpos($file, 'https://localhost:8080/')) {
                $file = substr($file, strlen('https://localhost:8080/'));
            }

            $source .= file_get_contents($this->publicDir.'/'.$file);
        }

        return $source;
    }

    public static function getSubscribedServices()
    {
        return [
            EntrypointLookupInterface::class,
        ];
    }
}
