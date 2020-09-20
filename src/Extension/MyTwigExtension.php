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

use App\Enum\BooleanType;
use App\Helper\DateTimeFormatter;
use DateTime;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MyTwigExtension extends AbstractExtension
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
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
        ];
    }

    /**
     * @param string $propertyName
     *
     * @return string
     */
    public function camelCaseToUnderscoreFilter($propertyName)
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $propertyName));
    }

    /**
     * @param DateTime|null $date
     *
     * @return string
     */
    public function dateFormatFilter($date)
    {
        if ($date instanceof DateTime) {
            return $this->prependDayName($date).', '.$date->format(DateTimeFormatter::DATE_FORMAT);
        }

        return '-';
    }

    /**
     * @param DateTime|null $date
     *
     * @return string
     */
    public function dateTimeFormatFilter($date)
    {
        if ($date instanceof DateTime) {
            return $this->prependDayName($date).', '.$date->format(DateTimeFormatter::DATE_TIME_FORMAT);
        }

        return '-';
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function booleanFilter($value)
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

    /**
     * translates the day of the week.
     *
     * @return string
     */
    private function prependDayName(DateTime $date)
    {
        return $this->translator->trans('date_time.'.$date->format('D'), [], 'framework');
    }
}
