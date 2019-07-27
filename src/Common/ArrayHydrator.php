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
     * @param array $path
     * @param string|callable $keyToOrFn
     * @param array $input
     * @param object $targetObject
     * @return void
     */
    protected function referenceRecursiveHydration(array $path, $keyToOrFn, array $input, $targetObject): void
    {
        $keyFrom = array_shift($path);

        if (isset($input[$keyFrom])) {
            if (count($path) === 0) {
                self::hydrationValue($keyToOrFn, $input[$keyFrom], $targetObject);
            } else {
                $this->referenceRecursiveHydration($path, $keyToOrFn, $input[$keyFrom], $targetObject);
            }
        }
    }

    /**
     * @param string|callable $keyToOrFn
     * @param mixed $value
     * @param object $targetObject
     */
    protected static function hydrationValue($keyToOrFn, $value, $targetObject): void
    {
        if (is_callable($keyToOrFn)) {
            $keyToOrFn($value, $targetObject);
        } else {
            $targetObject->{$keyToOrFn} = $value;
        }
    }

    /**
     * Hydrate $targetObject
     *
     * @param object $targetObject
     * @param array $input
     * @return object
     */
    public function hydrate($targetObject, array $input)
    {
        foreach ($this->map as $keyFrom => $keyToOrFn) {
            if (strpos($keyFrom, '.')) {
                $this->referenceRecursiveHydration(
                    explode('.', $keyFrom),
                    $keyToOrFn,
                    $input,
                    $targetObject
                );
            } else if (isset($input[$keyFrom])) {
                self::hydrationValue($keyToOrFn, $input[$keyFrom], $targetObject);
            }
        }

        return $targetObject;
    }
}
