<?php

/*
 * This file is part of the nodika project.
 *
 * (c) Florian Moser <git@famoser.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait CommunicationTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $phone;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(type="text")
     */
    private $email;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Url()
     */
    private $webpage;

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     *
     * @return static
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return static
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWebpage()
    {
        return $this->webpage;
    }

    /**
     * @param mixed $webpage
     */
    public function setWebpage($webpage)
    {
        $this->webpage = $webpage;
    }

    /**
     * get non-empty communication lines.
     *
     * @return string[]
     */
    public function getCommunicationLines()
    {
        $res = [];
        if (mb_strlen($this->getPhone()) > 0) {
            $res[] = $this->getPhone();
        }
        if (mb_strlen($this->getEmail()) > 0) {
            $res[] = $this->getEmail();
        }
        if (mb_strlen($this->getWebpage()) > 0) {
            $res[] = $this->getWebpage();
        }

        return $res;
    }
}
