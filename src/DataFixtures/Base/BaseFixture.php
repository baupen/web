<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\DataFixtures\Base;

use App\Entity\Traits\AddressTrait;
use App\Entity\Traits\CommunicationTrait;
use App\Entity\Traits\PersonTrait;
use App\Entity\Traits\ThingTrait;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
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
        $obj->setStreetNr($faker->numberBetween(0, 300));
        if ($faker->numberBetween(0, 10) > 8) {
            $obj->setAddressLine($faker->streetAddress);
        }
        $obj->setPostalCode($faker->numberBetween(0, 9999));
        $obj->setLocality($faker->city);
        $obj->setCountry($faker->countryCode);
    }

    /**
     * @return \Faker\Generator
     */
    protected function getFaker()
    {
        return Factory::create('de_CH');
    }

    /**
     * @param CommunicationTrait $obj
     */
    protected function fillRandomCommunication($obj)
    {
        $faker = $this->getFaker();
        $obj->setEmail($faker->email);
        if ($faker->numberBetween(0, 10) > 5) {
            $obj->setPhone($faker->phoneNumber);
        }
        if ($faker->numberBetween(0, 10) > 8) {
            $obj->setWebpage($faker->url);
        }
    }

    /**
     * @param ThingTrait $obj
     */
    protected function fillRandomThing($obj)
    {
        $faker = $this->getFaker();
        $obj->setName($faker->text(50));
        if ($faker->numberBetween(0, 10) > 5) {
            $obj->setDescription($faker->text(200));
        }
    }

    /**
     * @param PersonTrait $obj
     */
    protected function fillRandomPerson($obj)
    {
        $faker = $this->getFaker();
        $obj->setGivenName($faker->firstName);
        $obj->setFamilyName($faker->lastName);
    }

    /**
     * create random instances.
     *
     * @param $count
     * @param ObjectManager $manager
     * @return array
     */
    protected function loadSomeRandoms(ObjectManager $manager, $count = 5)
    {
        $res = [];
        for ($i = 0; $i < $count; ++$i) {
            $instance = $this->getAllRandomInstance();
            $res[] = $instance;
            $manager->persist($instance);
        }
        return $res;
    }

    /**
     * create an instance with all random values.
     *
     * @return mixed
     */
    abstract protected function getAllRandomInstance();
}
