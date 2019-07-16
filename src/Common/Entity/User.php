<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Entity;

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
     * @var \DateTime|null
     */
    protected $birthday;

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

    /**
     * @return \DateTime|null
     */
    public function getBirthday(): ?\DateTime
    {
        return $this->birthday;
    }

    /**
     * @param \DateTime|null $birthday
     */
    public function setBirthday(?\DateTime $birthday): void
    {
        $this->birthday = $birthday;
    }
}
