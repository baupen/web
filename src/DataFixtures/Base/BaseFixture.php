<?php

/*
 * This file is part of the mangel.io project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Base;

use App\Entity\Traits\AddressTrait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseFixture extends Fixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /* @var ContainerInterface $container */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param AddressTrait $obj
     */
    protected function fillRandomAddress($obj)
    {
        $faker = $this->getFaker();
        $obj->setStreetAddress($faker->streetAddress);
        $obj->setPostalCode($faker->numberBetween(0, 9999));
        $obj->setLocality($faker->city);
        $obj->setCountry($faker->countryCode);
    }

    /**
     * @return Generator
     */
    protected function getFaker()
    {
        return Factory::create('de_CH');
    }

    /**
     * create random instances.
     *
     * @param ObjectManager $manager
     * @param callable $loader
     * @param int $count
     *
     * @return array
     */
    protected function loadSomeRandoms(ObjectManager $manager, $loader, $count = 5)
    {
        $res = [];
        for ($i = 0; $i < $count; ++$i) {
            $instance = $loader();
            $res[] = $instance;
            $manager->persist($instance);
        }

        return $res;
    }
}
