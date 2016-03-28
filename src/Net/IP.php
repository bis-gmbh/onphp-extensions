<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net;

/**
 * Class IP
 */
class IP
{
    const CIDR_REGEXP = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\\/([0-9]|1[0-9]|2[0-9]|3[0-2])$/i';

    /**
     * Example:
     * 
     * $cidr = '192.168.0.1/24';
     * $returnArray = [
     *   '192.168.0.0',
     *   '192.168.0.255'
     * ];
     * 
     * @param string $cidr
     * @return array|bool
     */
    public static function getRangeByCIDR($cidr)
    {
        if ( ! preg_match(IP::CIDR_REGEXP, $cidr)) {
            return false;
        }
        list($ipAddr, $maskBits) = explode('/', $cidr);

        $ip   = ip2long($ipAddr);
        $mask = ~((1 << (32 - $maskBits)) - 1);

        $network   = $ip & $mask;
        $broadcast = $network + ~$mask;

        return [
            long2ip($network),
            long2ip($broadcast)
        ];
    }
}
