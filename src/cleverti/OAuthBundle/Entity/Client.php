<?php

namespace cleverti\OAuthBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use cleverti\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * One Oauth client has one single User;
     * @ORM\OneToOne(targetEntity="cleverti\UserBundle\Entity\User", inversedBy="oauthClient", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $user;


    public function __construct()
    {
        parent::__construct();
        // your own logic
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getRedirectUris()
    {
        return $this->redirectUris;
    }

    public function getAllowedGrantTypes()
    {
        return $this->allowedGrantTypes;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setUser(User $user = null)
    {
        $this->user = $user;
    }
}
