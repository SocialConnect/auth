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
class CloneObjectMap
{
    /**
     * Hydration map
     *
     * @var array
     */
    protected $map;

    /**
     * @var object
     */
    protected $instance;

    /**
     * @param array $map
     * @param object $instance
     */
    public function __construct(array $map, $instance)
    {
        $this->map = $map;
        $this->instance = $instance;
    }

    /**
     * Hydrate $targetObject
     *
     * @param $inputObject
     * @return object
     */
    public function hydrate($inputObject)
    {
        $result = clone $this->instance;

        foreach (get_object_vars($inputObject) as $key => $value) {
            if (isset($this->map[$key])) {
                $result->{$this->map[$key]} = $value;
            } else {
                $result->{$key} = $value;
            }
        }

        return $result;
    }
}
