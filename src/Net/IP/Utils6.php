<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2017 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\IP;

class Utils6
{
    const REGEXP_PREFIX_LENGTH = '/^([0-9]|[1-9][0-9]|1[0-1][0-9]|1[0-9][0-8])$/i'; // 0-128

    /**
     * @param mixed $value
     * @return bool
     */
    public static function isNumeric($value)
    {
        return is_numeric($value) && ($value >= 0);
    }

    /**
     * @param mixed $addr
     * @return bool
     */
    public static function isTextual($addr)
    {
        return filter_var($addr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    /**
     * @param mixed $cidr
     * @return bool
     */
    public static function isCIDR($cidr)
    {
        if (false === is_string($cidr)) {
            return false;
        }

        $cidrParts = explode('/', $cidr);

        if (
            count($cidrParts) === 2
            && self::isTextual($cidrParts[0])
            && preg_match(self::REGEXP_PREFIX_LENGTH, $cidrParts[1])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $addr
     * @return string
     */
    public static function detectFormat($addr)
    {
        if (self::isNumeric($addr)) {
            return 'numeric';
        }
        if (self::isTextual($addr)) {
            return 'textual';
        }
        if (self::isCIDR($addr)) {
            return 'cidr';
        }

        return 'unknown';
    }
}
