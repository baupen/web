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

use App\Entity\ConstructionManager;
use App\Enum\BooleanType;
use App\Helper\DateTimeFormatter;
use DateTime;
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
    private $translator;
    private $request;
    private $httpKernel;

    public function __construct(TranslatorInterface $translator, RequestStack $requestStack, HttpKernelInterface $httpKernel)
    {
        $this->translator = $translator;
        $this->request = $requestStack->getCurrentRequest();
        $this->httpKernel = $httpKernel;
    }

    /**
     * makes the filters available to twig.
     *
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('dateFormat', [$this, 'dateFormatFilter']),
            new TwigFilter('dateTimeFormat', [$this, 'dateTimeFormatFilter']),
            new TwigFilter('booleanFormat', [$this, 'booleanFilter']),
            new TwigFilter('camelCaseToUnderscore', [$this, 'camelCaseToUnderscoreFilter']),
            new TwigFilter('truncate', [$this, 'truncateFilter'], ['needs_environment' => true]),
            new TwigFilter('iOSLoginLink', [$this, 'iOSLoginLinkFilter']),
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('apiSubRequest', [$this, 'apiSubRequestFunction']),
        ];
    }

    public function apiSubRequestFunction(string $url)
    {
        $request = Request::create($url, 'GET', [], [], [], ['HTTP_ACCEPT' => null]);
        $response = $this->httpKernel->handle(
            $request,
            HttpKernelInterface::SUB_REQUEST
        );

        return $response->getContent();
    }

    public function iOSLoginLinkFilter(ConstructionManager $constructionManager): string
    {
        $username = 'username='.urlencode($constructionManager->getEmail());
        $domain = $this->request ? 'domain='.urlencode($this->request->getHttpHost()) : null;

        $arguments = array_filter([$username, $domain]);
        $url = implode('&', $arguments);

        return 'mangel.io://login?'.$url;
    }

    public function camelCaseToUnderscoreFilter(string $propertyName): string
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $propertyName));
    }

    public function dateFormatFilter(?DateTime $date): string
    {
        if ($date instanceof DateTime) {
            return $this->prependDayName($date).', '.$date->format(DateTimeFormatter::DATE_FORMAT);
        }

        return '-';
    }

    public function dateTimeFormatFilter(?DateTime $date): string
    {
        if ($date instanceof DateTime) {
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
    public function truncateFilter(Environment $env, $value, $length = 30, $preserve = false, $separator = '...')
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

    private function prependDayName(DateTime $date): string
    {
        return $this->translator->trans('date_time.'.$date->format('D'), [], 'framework');
    }
}
