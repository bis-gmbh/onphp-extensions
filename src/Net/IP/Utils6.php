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
        try {
            $result = inet_pton($addr);
            return $result !== false;
        } catch (\Exception $e) {
            // nop
        }

        return false;
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
            && $cidrParts[1] >= 0 && $cidrParts[1] <= 128
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
