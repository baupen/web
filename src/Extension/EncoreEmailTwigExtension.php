<?php

namespace App\Extension;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EncoreEmailTwigExtension extends AbstractExtension
{
    public function __construct(private readonly EntrypointLookupInterface $entrypointLookup, private readonly string $publicDir)
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
        $this->entrypointLookup->reset();
        $files = $this->entrypointLookup->getCssFiles($entryName);

        $source = '';
        foreach ($files as $file) {
            if (str_starts_with($file, 'http://localhost:8080/')) {
                $file = substr($file, strlen('http://localhost:8080/'));
            }

            $source .= file_get_contents($this->publicDir . '/' . $file);
        }

        return $source;
    }
}
