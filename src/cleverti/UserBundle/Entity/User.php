<?php

namespace cleverti\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="`user`")
 * @ExclusionPolicy("all")
 */
class User extends BaseUser implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Expose
     */
    protected $id;

    /**
     * One User has one single Oauth client;
     * @ORM\OneToOne(targetEntity="cleverti\OAuthBundle\Entity\Client", mappedBy="user", fetch="EAGER")
     */
    protected $oauthClient;

    /**
     * One User has many Access Tokens;
     * @ORM\OneToMany(targetEntity="cleverti\OAuthBundle\Entity\AccessToken", mappedBy="user", fetch="EAGER")
     */
    protected $accessToken;

    /**
     * One User has many Auth Codes;
     * @ORM\OneToMany(targetEntity="cleverti\OAuthBundle\Entity\AuthCode", mappedBy="user", fetch="EAGER")
     */
    protected $authCode;

    /**
    * One User has many Refresh Tokens;
    * @ORM\OneToMany(targetEntity="cleverti\OAuthBundle\Entity\RefreshToken", mappedBy="user", fetch="EAGER")
    */
    protected $refreshToken;

    /**
     * Creates a new User;
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns the id of the User;
     * @return integer The id of the User;
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the ID of the User;
     * @param integer $id The new ID of the User;
     * @return \User This User with the new id;
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }


    public function setRolesAsString($roles)
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Sets email and username to the specified email
     * @param string $email The email to set;
     * @return $this This User with updated email and username;
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
}
