<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net;

use \Onphp\WrongArgumentException;

/**
 * Class DNS
 */
class DNS
{
    /**
     * @param $ip string
     * @param $options array
     * $options[
     *   'dns' => [
     *     '8.8.8.8',
     *     '8.8.4.4'
     *   ],
     *   'timeout' => 3, // sec
     * ]
     * @return string
     * @throws \Exception
     * @throws WrongArgumentException
     */
    public static function gethostbyaddr($ip, $options = [])
    {
        $defaultDNS = [
            '8.8.8.8',
            '8.8.4.4',
        ];

        $long = ip2long($ip);
        if ($long === false) {
            throw new WrongArgumentException(sprintf('wrong ip given %s', $ip));
        }

        if (isset($options['dns'])) {
            if (is_array($options['dns'])) {
                $dns = $options['dns'][mt_rand(0, count($options['dns']) - 1)];
            } else {
                $dns = $options['dns'];
            }
        } else {
            $dns = $defaultDNS[mt_rand(0, count($defaultDNS) - 1)];
        }

        if (isset($options['timeout']) && is_integer($options['timeout'])) {
            $timeout = $options['timeout'];
        } else {
            $timeout = 3;
        }

        // http://www.ietf.org/rfc/rfc1035.txt 4.1.1. Header section format
        $data = pack("n", mt_rand(0, 0xFFFF));
        $data .= "\1\0\0\1\0\0\0\0\0\0";

        $octet1 = $long & 0xFF;
        $octet2 = $long >> 8 & 0xFF;
        $octet3 = $long >> 16 & 0xFF;
        $octet4 = $long >> 24 & 0xFF;
        $data .= pack(
            "Ca*Ca*Ca*Ca*",
            strlen($octet1), $octet1,
            strlen($octet2), $octet2,
            strlen($octet3), $octet3,
            strlen($octet4), $octet4
        );
        $data .= "\7in-addr\4arpa\0\0\x0C\0\1";

        $handle = fsockopen("udp://$dns", 53);
        stream_set_timeout($handle, $timeout);
        $requestsize = fwrite($handle, $data);
        $response = fread($handle, 1000);
        fclose($handle);

        $rawData = substr($response, $requestsize + 2);
        if ($rawData === false) {
            return $ip;
        }

        $type = @unpack("s", $rawData);
        if ($type[1] == 0x0C00) {
            // TODO: check case 72.52.91.14 -> php-web2.php.net
            $host = '';
            $position = $requestsize + 12;
            do {
                $rawSegment = substr($response, $position);
                if ($rawSegment === false) {
                    break;
                }
                // get segment size
                $len = unpack("c", $rawSegment);
                // null terminated string, so length 0 = finished
                if ($len[1] == 0) {
                    // return the hostname, without the trailing .
                    return substr($host, 0, strlen($host) - 1);
                }
                $host .= substr($response, $position + 1, $len[1]) . ".";
                $position += $len[1] + 1;
            } while ($len != 0);

            return $ip;
        }

        return $ip;
    }
}
