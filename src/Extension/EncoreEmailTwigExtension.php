<?php

namespace App\Extension;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EncoreEmailTwigExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(private ContainerInterface $container, private string $publicDir)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_css_source', [$this, 'getEncoreEntryCssSource']),
        ];
    }

    public function getEncoreEntryCssSource(string $entryName): string
    {
        $entrypointLookup = $this->container->get(EntrypointLookupInterface::class);
        $entrypointLookup->reset();
        $files = $entrypointLookup->getCssFiles($entryName);

        $source = '';
        foreach ($files as $file) {
            if (str_starts_with($file, 'http://localhost:8080/')) {
                $file = substr($file, strlen('http://localhost:8080/'));
            }

            $source .= file_get_contents($this->publicDir . '/' . $file);
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
