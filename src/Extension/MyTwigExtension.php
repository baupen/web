<?php

namespace App\Extension;

use App\Entity\ConstructionManager;
use App\Helper\DateTimeFormatter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MyTwigExtension extends AbstractExtension
{
    public function __construct(private readonly RequestStack $requestStack, private readonly HttpKernelInterface $httpKernel)
    {
    }

    /**
     * makes the filters available to twig.
     *
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('format_date', [$this, 'formatDateFilter']),
            new TwigFilter('camelcase_to_snakecase', [$this, 'camelCaseToSnakeCaseFilter']),
            new TwigFilter('truncate', [$this, 'truncateFilter'], ['needs_environment' => true]),
            new TwigFilter('login_link', [$this, 'loginLinkFilter']),
            new TwigFilter('repeat', [$this, 'repeatFilter']),
        ];
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('apiSubRequest', [$this, 'apiSubRequestFunction']),
        ];
    }

    public function repeatFilter(string $entry, int $count): string
    {
        return str_repeat($entry, $count);
    }

    public function apiSubRequestFunction(string $url): false|string
    {
        $request = Request::create($url, Request::METHOD_GET, [], [], [], ['HTTP_ACCEPT' => null]);
        $request->setSession($this->requestStack->getSession());
        $response = $this->httpKernel->handle(
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        return $response->getContent();
    }

    public function loginLinkFilter(ConstructionManager $constructionManager): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        // same payload also in app.js
        $payload = ['token' => $constructionManager->getAuthenticationToken(), 'origin' => $currentRequest->getSchemeAndHttpHost()];
        $data = json_encode($payload);

        return 'mangelio://login?payload=' . base64_encode($data);
    }

    public function camelCaseToSnakeCaseFilter(string $propertyName): string
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $propertyName));
    }

    public function formatDateFilter(null|\DateTime|\DateTimeImmutable $date): string
    {
        if ($date) {
            return $date->format(DateTimeFormatter::DATE_FORMAT);
        }

        return '-';
    }

    /**
     * @source https://github.com/twigphp/Twig-extensions/blob/master/src/TextExtension.php
     */
    public function truncateFilter(Environment $env, string $value, int $length = 30, bool $preserve = false, string $separator = '...'): string
    {
        if (mb_strlen($value, $env->getCharset()) > $length) {
            if ($preserve) {
                // If breakpoint is on the last word, return the value without separator.
                if (false === ($breakpoint = mb_strpos($value, ' ', $length, $env->getCharset()))) {
                    return $value;
                }

                $length = $breakpoint;
            }

            return rtrim(mb_substr($value, 0, $length, $env->getCharset())) . $separator;
        }

        return $value;
    }
}
