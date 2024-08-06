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

use App\Entity\ConstructionManager;
use App\Enum\BooleanType;
use App\Helper\DateTimeFormatter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class MyTwigExtension extends AbstractExtension
{
    private TranslatorInterface $translator;
    private RequestStack $requestStack;
    private HttpKernelInterface $httpKernel;

    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, HttpKernelInterface $httpKernel)
    {
        $this->translator = $translator;
        $this->requestStack = $requestStack;
        $this->httpKernel = $httpKernel;
    }

    /**
     * makes the filters available to twig.
     *
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('dateFormat', [$this, 'dateFormatFilter']),
            new TwigFilter('dateFormatShort', [$this, 'dateFormatShortFilter']),
            new TwigFilter('dateTimeFormat', [$this, 'dateTimeFormatFilter']),
            new TwigFilter('booleanFormat', [$this, 'booleanFilter']),
            new TwigFilter('camelCaseToUnderscore', [$this, 'camelCaseToUnderscoreFilter']),
            new TwigFilter('truncate', [$this, 'truncateFilter'], ['needs_environment' => true]),
            new TwigFilter('iOSLoginLink', [$this, 'iOSLoginLinkFilter']),
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

    public function iOSLoginLinkFilter(ConstructionManager $constructionManager): string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();

        // same payload also in app.js
        $payload = ['token' => $constructionManager->getAuthenticationToken(), 'origin' => $currentRequest->getSchemeAndHttpHost()];
        $data = json_encode($payload);

        return 'mangelio://login?payload='.base64_encode($data);
    }

    public function camelCaseToUnderscoreFilter(string $propertyName): string
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $propertyName));
    }

    public function dateFormatFilter(?\DateTime $date): string
    {
        if ($date instanceof \DateTime) {
            return $this->prependDayName($date).', '.$date->format(DateTimeFormatter::DATE_FORMAT);
        }

        return '-';
    }

    public function dateFormatShortFilter(?\DateTime $date): string
    {
        if ($date instanceof \DateTime) {
            return $date->format(DateTimeFormatter::DATE_FORMAT);
        }

        return '-';
    }

    public function dateTimeFormatFilter(?\DateTime $date): string
    {
        if ($date instanceof \DateTime) {
            return $this->prependDayName($date).', '.$date->format(DateTimeFormatter::DATE_TIME_FORMAT);
        }

        return '-';
    }

    public function booleanFilter(bool $value): string
    {
        if ($value) {
            return BooleanType::getTranslationForValue(BooleanType::YES, $this->translator);
        }

        return BooleanType::getTranslationForValue(BooleanType::NO, $this->translator);
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

            return rtrim(mb_substr($value, 0, $length, $env->getCharset())).$separator;
        }

        return $value;
    }

    private function prependDayName(\DateTime $date): string
    {
        return $this->translator->trans('date_time.'.$date->format('D'), [], 'framework');
    }
}
