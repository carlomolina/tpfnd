<?php

namespace Tpfnd\TpfndUserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="tpfnd_users")
 * @ORM\Entity(repositoryClass="Tpfnd\TpfndUserBundle\Entity\TpfndUserRepository")
 * @UniqueEntity(fields="email", message="Email already taken")
 */
class TpfndUser implements UserInterface, \Serializable {

  /**
   * @ORM\Column(type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=100)
   * @Assert\NotBlank()
   */
  private $firstname;

  /**
   * @ORM\Column(type="string", length=100)
   * @Assert\NotBlank()
   */
  private $lastname;

  /**
   * @ORM\Column(type="string", length=25, unique=false)
   */
  private $username;

  /**
   * @ORM\Column(type="string", length=255)
   * @Assert\NotBlank()
   * @Assert\Length(min=6, max=4096)
   */
  private $password;

  /**
   * @ORM\Column(type="string", length=255)
   */
  private $salt;

  /**
   * @ORM\Column(type="string", length=255, unique=true)
   * @Assert\NotBlank()
   * @Assert\Email()
   */
  private $email;

  /**
   * @ORM\Column(name="is_active", type="boolean")
   */
  private $isActive;

  public function __construct() {
	$this->isActive = false;
	#$this->salt = mcrypt_create_iv(12);
  }

  public function getUsername() {
	return $this->username;
  }

  public function getSalt() {
	return $this->salt;
  }

  public function getPassword() {
	return $this->password;
  }

  public function getRoles() {
	return array('ROLE_USER');
  }

  public function eraseCredentials() {
	
  }

  public function serialize() {
	return serialize(array(
		$this->id,
		$this->username,
		$this->password,
		$this->email,
		$this->salt,
	));
  }

  public function unserialize($serialized) {
	list (
			$this->id,
			$this->username,
			$this->password,
			$this->email,
			$this->salt
			) = unserialize($serialized);
  }

  /**
   * Get id
   *
   * @return integer 
   */
  public function getId() {
	return $this->id;
  }

  /**
   * Set firstname
   *
   * @param string $firstname
   * @return TpfndUser
   */
  public function setFirstname($firstname) {
	$this->firstname = $firstname;

	return $this;
  }

  /**
   * Get firstname
   *
   * @return string 
   */
  public function getFirstname() {
	return $this->firstname;
  }

  /**
   * Set lastname
   *
   * @param string $lastname
   * @return TpfndUser
   */
  public function setLastname($lastname) {
	$this->lastname = $lastname;

	return $this;
  }

  /**
   * Get lastname
   *
   * @return string 
   */
  public function getLastname() {
	return $this->lastname;
  }

  /**
   * Set password
   *
   * @param string $password
   * @return TpfndUser
   */
  public function setPassword($password) {
	$this->password = $password;

	return $this;
  }

  /**
   * Set email
   *
   * @param string $email
   * @return TpfndUser
   */
  public function setEmail($email) {
	$this->email = $email;

	return $this;
  }

  /**
   * Get email
   *
   * @return string 
   */
  public function getEmail() {
	return $this->email;
  }

  /**
   * Set isActive
   *
   * @param boolean $isActive
   * @return TpfndUser
   */
  public function setIsActive($isActive) {
	$this->isActive = $isActive;

	return $this;
  }

  /**
   * Get isActive
   *
   * @return boolean 
   */
  public function getIsActive() {
	return $this->isActive;
  }

  /**
   * Set username
   *
   * @param string $username
   * @return TpfndUser
   */
  public function setUsername($username) {
	$this->username = $username;

	return $this;
  }

  /**
   * Set salt
   *
   * @param string $salt
   * @return TpfndUser
   */
  public function setSalt($salt) {
	$this->salt = $salt;

	return $this;
  }

}
