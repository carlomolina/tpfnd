<?php

namespace Tpfnd\TpfndUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="Tpfnd\TpfndUserBundle\Entity\TpfndUserRepository")
 */
class TokenLink
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, unique=true)
     * @Assert\NotBlank()
     */
    private $token;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="TpfndUser")
     */
    private $tpfndUser;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $created;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $isValid;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return TokenLink
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return TokenLink
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return TokenLink
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set tpfndUser
     *
     * @param TpfndUser $tpfndUser
     * @return TokenLink
     */
    public function setTpfndUser(TpfndUser $tpfndUser = null)
    {
        $this->tpfndUser = $tpfndUser;

        return $this;
    }

    /**
     * Get tpfndUser
     *
     * @return TpfndUser
     */
    public function getTpfndUser()
    {
        return $this->tpfndUser;
    }

    /**
     * Set isValid
     *
     * @param boolean $isValid
     * @return TokenLink
     */
    public function setIsValid($isValid)
    {
        $this->isValid = $isValid;

        return $this;
    }

    /**
     * Get isValid
     *
     * @return boolean 
     */
    public function getIsValid()
    {
        return $this->isValid;
    }
}
