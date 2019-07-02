<?php
/**
 * @author Patsura Dmitry https://github.com/ovr <talk@dmtry.me>
 */
declare(strict_types=1);

namespace SocialConnect\OpenIDConnect\Exception;

use Exception;

class UnsupportedSignatureAlgoritm extends \SocialConnect\Common\Exception
{
    public function __construct($message = 'Unsupported signature algorithm', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
