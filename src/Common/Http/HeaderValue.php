<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\Common\Http;

class HeaderValue
{
    /**
     * @var string[]
     */
    protected $values = [];

    /**
     * @param string $value
     */
    public function __construct(string $header)
    {
        $map = array_map(
            'trim',
            explode(',', strtolower($header))
        );

        foreach ($map as $value) {
            $parts = explode('=', $value, 2);
            if (count($parts) == 2) {
                $this->values[$parts[0]] = $parts[1];
            } else {
                $this->values[$value] = true;
            }
        }
    }

    public function has(string $key): bool
    {
        return isset($this->values[$key]);
    }

    public function get(string $key): string
    {
        return $this->values[$key];
    }
}
