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

        $longRange = self::getLongRangeByCIDR(ip2long($ipAddr), $maskBits);

        return [
            long2ip($longRange[0]),
            long2ip($longRange[1])
        ];
    }

    /**
     * @param integer $long
     * @param integer $maskBits
     * @return integer[]
     */
    public static function getLongRangeByCIDR($long, $maskBits)
    {
        $mask = ~((1 << (32 - $maskBits)) - 1);

        $network   = $long & $mask;
        $broadcast = $network + ~$mask;

        return [
            $network,
            $broadcast
        ];
    }

    /**
     * Example:
     * 
     * $start  = '192.168.0.0';
     * $finish = '192.168.0.255';
     * $return = '192.168.0.0/24';
     * 
     * @param string $start
     * @param string $finish
     * @return string|bool
     */
    public static function getCIDRByRange($start, $finish)
    {
        $numberOfBits = function($x) {
            if ($x == 0) {
                return 0;
            }
            $n = 1;
            while ($x != 1) {
                $x >>= 1;
                $n++;
            }
            return $n;
        };
        $maskBits = function($n) use ($numberOfBits) {
            return 32 - $numberOfBits($n);
        };

        $x = ip2long($start);
        $y = ip2long($finish);

        if ($x === false || $y === false || $x > $y) {
            return false;
        }

        $nb = $maskBits($y - $x);

        $r1 = self::getLongRangeByCIDR($x, $nb);
        $r2 = self::getLongRangeByCIDR($y, $nb);

        if ($r1[0] == $r2[0] && $r1[1] == $r2[1]) { // calculated cidr include range
            return sprintf("%s/%d", long2ip($r1[0]), $nb);
        }

        return false;
    }
}
