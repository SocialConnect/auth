<?php
/**
 * SocialConnect project
 * @author: Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */

namespace SocialConnect\OAuth1;

class Util
{
    /**
     * @param mixed $input
     * @return array|string
     */
    public static function urlencodeRFC3986($input)
    {
        if (is_array($input)) {
            return array_map(array(
                __NAMESPACE__ . '\Util',
                'urlencodeRFC3986'
            ), $input);
        } elseif (is_scalar($input)) {
            return rawurlencode($input);
        } else {
            return '';
        }
    }

    /**
     * This decode function isn't taking into consideration the above
     * modifications to the encoding process. However, this method doesn't
     * seem to be used anywhere so leaving it as is.
     *
     * @param string $string
     * @return string
     */
    public static function urldecodeRFC3986($string)
    {
        return urldecode($string);
    }

    /**
     * @param array $params
     * @return string
     */
    public static function buildHttpQuery(array $params)
    {
        if (!$params) {
            return '';
        }

        // Urlencode both keys and values
        $keys   = self::urlencodeRFC3986(array_keys($params));
        $values = self::urlencodeRFC3986(array_values($params));
        $params = array_combine($keys, $values);

        // Parameters are sorted by name, using lexicographical byte value ordering.
        // Ref: Spec: 9.1.1 (1)
        uksort($params, 'strcmp');

        $pairs = [];

        foreach ($params as $parameter => $value) {
            if (is_array($value)) {
                // If two or more parameters share the same name, they are sorted by their value
                // Ref: Spec: 9.1.1 (1)
                // June 12th, 2010 - changed to sort because of issue 164 by hidetaka
                sort($value, SORT_STRING);
                foreach ($value as $duplicate_value) {
                    $pairs[] = $parameter . '=' . $duplicate_value;
                }
            } else {
                $pairs[] = $parameter . '=' . $value;
            }
        }

        // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
        // Each name-value pair is separated by an '&' character (ASCII code 38)
        return implode('&', $pairs);
    }
}
