<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Entity;

class User extends \stdClass
{
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';
    const GENDER_UNKNOWN = 'unknown';

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
     * One of the GENDER_-constants
     *
     * @var string
     */
    protected $gender = GENDER_UNKNOWN;

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
     * @return string
     */
    public function getGender(): string
    {
        return $this->gender;
    }

    /**
     * @param string $sex
     */
    public function setGender(string $gender): void
    {
        $genders = [
            GENDER_OTHER,
            GENDER_MALE,
            GENDER_FEMALE,
        ];
        if (! $gender in_array($genders)) {
            throw new \InvalidArgumentException('Argument $gender is not valid');
        }

        $this->gender = $gender;
    }
    
    /**
     * @deprecated use `getGender` instead
     */
    public function getSex() : string
    {
        return $this->getGender();
    }
    
    /**
     * @deprecated Use setGender instead
     */
    public function setSex(string $sex) : void
    {
        $this->setGender($sex);
    }
}
