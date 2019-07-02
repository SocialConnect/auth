<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Entity;

/**
 * Class User
 * @package SocialConnect\Common\Entity
 */
class User extends \stdClass
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $email;

    /**
     * @var bool
     */
    public $emailVerified = false;

    /**
     * @var string|null
     */
    public $birthday;

    /**
     * @var string|null
     */
    public $username;

    /**
     * Should be female or male
     *
     * @var string|null
     */
    public $sex;

    /**
     * @var string|null
     */
    public $fullname;

    /**
     * @var string|null
     */
    public $pictureURL;
}
