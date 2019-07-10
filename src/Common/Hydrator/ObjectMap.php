<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common\Hydrator;

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
        foreach ($this->map as $keyFrom => $keyTo) {
            if (isset($inputObject->{$keyFrom})) {
                $targetObject->{$keyTo} = $inputObject->{$keyFrom};
            }
        }

        return $targetObject;
    }
}
