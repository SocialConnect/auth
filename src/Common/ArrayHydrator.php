<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry @ovr <talk@dmtry.me>
 */

namespace SocialConnect\Common;

final class ArrayHydrator
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
     * @param object $targetObject
     * @param array $inputObject
     * @return object
     */
    public function hydrate($targetObject, array $inputObject)
    {
        foreach ($this->map as $keyFrom => $keyToOrFn) {
            if (isset($inputObject[$keyFrom])) {
                if (is_callable($keyToOrFn)) {
                    $keyToOrFn($inputObject[$keyFrom], $targetObject);
                } else {
                    $targetObject->{$keyToOrFn} = $inputObject[$keyFrom];
                }
            }
        }

        return $targetObject;
    }
}
