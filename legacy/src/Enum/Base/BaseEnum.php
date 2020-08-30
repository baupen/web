<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Enum\Base;

use ReflectionClass;
use ReflectionException;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class BaseEnum
{
    /**
     * returns an array fit to be used by the ChoiceType.
     *
     * @return array
     */
    public static function getChoicesForBuilder()
    {
        $elem = new static();

        return $elem->getChoicesForBuilderInternal();
    }

    /**
     * returns a translation string for the passed enum value.
     *
     * @param $enumValue
     *
     * @return string
     */
    public static function getTranslationForValue($enumValue, TranslatorInterface $translator)
    {
        $elem = new static();

        return $elem->getTranslationForValueInternal($enumValue, $translator);
    }

    /**
     * makes from camelCase => camel_case.
     *
     * @param $camelCase
     *
     * @return string
     */
    private static function camelCaseToTranslation($camelCase)
    {
        return mb_strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $camelCase));
    }

    /**
     * generates an array to be used in form fields.
     *
     * @return array
     */
    private function getChoicesForBuilderInternal()
    {
        $res = [];
        try {
            $reflection = new ReflectionClass(static::class);
            $choices = $reflection->getConstants();

            foreach ($choices as $name => $value) {
                $res[mb_strtolower($name)] = $value;
            }

            return ['choices' => $res, 'choice_translation_domain' => 'enum_'.$this->camelCaseToTranslation($reflection->getShortName())];
        } catch (ReflectionException $e) {
            //this never happens due to ReflectionClass is passed the class of the $this object (always valid)
        }

        return $res;
    }

    /**
     * returns a translation string for the passed enum value.
     *
     * @param $enumValue
     *
     * @return bool|string
     */
    private function getTranslationForValueInternal($enumValue, TranslatorInterface $translator)
    {
        try {
            $reflection = new ReflectionClass(static::class);
            $choices = $reflection->getConstants();

            foreach ($choices as $name => $value) {
                if ($value === $enumValue) {
                    return $translator->trans(mb_strtolower($name), [], 'enum_'.$this->camelCaseToTranslation($reflection->getShortName()));
                }
            }
        } catch (ReflectionException $e) {
            //this never happens due to ReflectionClass is passed the class of the $this object (always valid)
        }

        return '';
    }
}
