<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Entity;

class User extends \stdClass
{
    const SEX_MALE = 'male';
    const SEX_FEMALE = 'female';

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
    protected $sex;

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

    /**
     * @return string|null
     */
    public function getSex(): ?string
    {
        return $this->sex;
    }

    /**
     * @param string $sex
     */
    public function setSex(string $sex): void
    {
        if ($sex !== self::SEX_MALE && $sex !== self::SEX_FEMALE) {
            throw new \InvalidArgumentException('Argument $sex is not valid');
        }

        $this->sex = $sex;
    }
}
