<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Hydrator;

/**
 * Class ObjectMap
 * @package SocialConnect\Common\Hydrator
 */
class ObjectMap
{
    /**
     * Hydration map
     *
     * @var array
     */
    protected $map;

    /**
     * @param array $map
     */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    /**
     * Hydrate $targetObject
     *
     * @param $targetObject
     * @param $inputObject
     * @return mixed
     */
    public function hydrate($targetObject, $inputObject)
    {
        foreach (get_object_vars($inputObject) as $key => $value) {
            if (isset($this->map[$key])) {
                $targetObject->{$this->map[$key]} = $value;
            } else {
                $targetObject->{$key} = $value;
            }
        }

        return $targetObject;
    }
}
