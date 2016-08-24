<?php
/**
 * The MIMEDecode class is used to decode mail/mime messages
 *
 * This class will parse a raw mime email and return
 * the structure. Returned structure is similar to
 * that returned by imap_fetchstructure().
 *
 *  +----------------------------- IMPORTANT ------------------------------+
 *  | Usage of this class compared to native php extensions such as        |
 *  | mailparse or imap, is slow and may be feature deficient. If available|
 *  | you are STRONGLY recommended to use the php extensions.              |
 *  +----------------------------------------------------------------------+
 *
 * Compatible with PHP versions 5 and 7
 *
 * LICENSE: This LICENSE is in the BSD license style.
 * Copyright (c) 2002-2003, Richard Heyes <richard@phpguru.org>
 * Copyright (c) 2003-2006, PEAR <pear-group@php.net>
 * Copyright (c) 2014-2016 Barzmann Internet Solutions GmbH
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or
 * without modification, are permitted provided that the following
 * conditions are met:
 *
 * - Redistributions of source code must retain the above copyright
 *   notice, this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 *   notice, this list of conditions and the following disclaimer in the
 *   documentation and/or other materials provided with the distribution.
 * - Neither the name of the authors, nor the names of its contributors 
 *   may be used to endorse or promote products derived from this 
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Onphp\Extensions\Mail
 * @author     Richard Heyes  <richard@phpguru.org>
 * @author     George Schlossnagle <george@omniti.com>
 * @author     Cipriano Groenendal <cipri@php.net>
 * @author     Sean Coates <sean@php.net>
 * @author     Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright  2003-2006 PEAR <pear-group@php.net>
 * @copyright  2014-2016 Barzmann Internet Solutions GmbH
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace Onphp\Extensions\Mail;

/**
 * The MIMEDecode class is used to decode mail/mime messages
 *
 * This class will parse a raw mime email and return the structure.
 * Returned structure is similar to that returned by imap_fetchstructure().
 *
 *  +----------------------------- IMPORTANT ------------------------------+
 *  | Usage of this class compared to native php extensions such as        |
 *  | mailparse or imap, is slow and may be feature deficient. If available|
 *  | you are STRONGLY recommended to use the php extensions.              |
 *  +----------------------------------------------------------------------+
 *
 * @package    Onphp\Extensions\Mail
 * @author     Richard Heyes  <richard@phpguru.org>
 * @author     George Schlossnagle <george@omniti.com>
 * @author     Cipriano Groenendal <cipri@php.net>
 * @author     Sean Coates <sean@php.net>
 * @author     Dmitry A. Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright  2003-2006 PEAR <pear-group@php.net>
 * @copyright  2014-2016 Barzmann Internet Solutions GmbH
 * @license    http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class MIMEDecode
{
    /**
     * The raw email to decode
     *
     * @var    string
     * @access private
     */
    private $_input;

    /**
     * The header part of the input
     *
     * @var    string
     * @access private
     */
    private $_header;

    /**
     * The body part of the input
     *
     * @var    string
     * @access private
     */
    private $_body;

    /**
     * If an error occurs, this is used to store the message
     *
     * @var    string
     * @access private
     */
    private $_error;

    /**
     * Flag to determine whether to include bodies in the
     * returned object.
     *
     * @var    boolean
     * @access private
     */
    private $_include_bodies;

    /**
     * Flag to determine whether to decode bodies
     *
     * @var    boolean
     * @access private
     */
    private $_decode_bodies;

    /**
     * Flag to determine whether to decode headers
     *
     * @var    boolean
     * @access private
     */
    private $_decode_headers;

    /**
     * Flag to determine whether to include attached messages
     * as body in the returned object. Depends on $_include_bodies
     *
     * @var    boolean
     * @access private
     */
    private $_rfc822_bodies;

    /**
     * Constructor.
     *
     * Sets up the object, initialise the variables, and splits and
     * stores the header and body of the input.
     *
     * @param string $input The input to decode
     * @access public
     */
    public function __construct($input)
    {
        list($header, $body)   = $this->_splitBodyHeader($input);

        $this->_input          = $input;
        $this->_header         = $header;
        $this->_body           = $body;
        $this->_decode_bodies  = false;
        $this->_include_bodies = true;
        $this->_rfc822_bodies  = false;
        $this->_error          = 'Unknown error';
    }

    /**
     * Begins the decoding process.
     *
     * @param array $params An array of various parameters that determine
     *              various things:
     *              include_bodies - Whether to include the body in the returned
     *                               object.
     *              decode_bodies  - Whether to decode the bodies
     *                               of the parts. (Transfer encoding)
     *              decode_headers - Whether to decode headers
     *              input          - If called statically, this will be treated
     *                               as the input
     * @return object Decoded results
     * @throws MailException
     * @access public
     */
    public function decode($params = null)
    {
        $this->_include_bodies = isset($params['include_bodies']) ?
                             $params['include_bodies'] : false;
        $this->_decode_bodies  = isset($params['decode_bodies']) ?
                             $params['decode_bodies']  : false;
        $this->_decode_headers = isset($params['decode_headers']) ?
                             $params['decode_headers'] : false;
        $this->_rfc822_bodies  = isset($params['rfc_822bodies']) ?
                             $params['rfc_822bodies']  : false;

        $structure = $this->_decode($this->_header, $this->_body);
        if ($structure === false) {
            throw new MailException($this->_error);
        }

        return $structure;
    }

    /**
     * Performs the decoding. Decodes the body string passed to it
     * If it finds certain content-types it will call itself in a
     * recursive fashion
     *
     * @param string $headers Header section
     * @param string $body Body section
     * @param string $default_ctype
     * @return object Results of decoding process
     * @throws MailException
     * @access private
     */
    private function _decode($headers, $body, $default_ctype = 'text/plain')
    {
        $return = new \stdClass;
        $return->headers = [];
        $headers = $this->_parseHeaders($headers);

        foreach ($headers as $value) {
            $value['value'] = $this->_decode_headers ? $this->_decodeHeader($value['value']) : $value['value'];
            if (isset($return->headers[strtolower($value['name'])]) AND !is_array($return->headers[strtolower($value['name'])])) {
                $return->headers[strtolower($value['name'])]   = [$return->headers[strtolower($value['name'])]];
                $return->headers[strtolower($value['name'])][] = $value['value'];

            } elseif (isset($return->headers[strtolower($value['name'])])) {
                $return->headers[strtolower($value['name'])][] = $value['value'];

            } else {
                $return->headers[strtolower($value['name'])] = $value['value'];
            }
        }


        foreach ($headers as $key => $value) {
            $headers[$key]['name'] = strtolower($headers[$key]['name']);
            switch ($headers[$key]['name']) {

                case 'content-type':
                    $content_type = $this->_parseHeaderValue($headers[$key]['value']);

                    if (preg_match('/([0-9a-z+.-]+)\/([0-9a-z+.-]+)/i', $content_type['value'], $regs)) {
                        $return->ctype_primary   = $regs[1];
                        $return->ctype_secondary = $regs[2];
                    }

                    if (isset($content_type['other'])) {
                        foreach($content_type['other'] as $p_name => $p_value) {
                            $return->ctype_parameters[$p_name] = $p_value;
                        }
                    }
                    break;

                case 'content-disposition':
                    $content_disposition = $this->_parseHeaderValue($headers[$key]['value']);
                    $return->disposition   = $content_disposition['value'];
                    if (isset($content_disposition['other'])) {
                        foreach($content_disposition['other'] as $p_name => $p_value) {
                            $return->d_parameters[$p_name] = $p_value;
                        }
                    }
                    break;

                case 'content-transfer-encoding':
                    $content_transfer_encoding = $this->_parseHeaderValue($headers[$key]['value']);
                    break;
            }
        }

        if (isset($content_type)) {
            switch (strtolower($content_type['value'])) {
                case 'text/plain':
                    $encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
                    $this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
                    break;

                case 'text/html':
                    $encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
                    $this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body) : null;
                    break;

                case 'multipart/parallel':
                case 'multipart/appledouble': // Appledouble mail
                case 'multipart/report': // RFC1892
                case 'multipart/signed': // PGP
                case 'multipart/digest':
                case 'multipart/alternative':
                case 'multipart/related':
                case 'multipart/mixed':
                case 'application/vnd.wap.multipart.related':
                    if(!isset($content_type['other']['boundary'])){
                        $this->_error = 'No boundary found for ' . $content_type['value'] . ' part';
                        return false;
                    }

                    $default_ctype = (strtolower($content_type['value']) === 'multipart/digest') ? 'message/rfc822' : 'text/plain';

                    $parts = $this->_boundarySplit($body, $content_type['other']['boundary']);
                    for ($i = 0; $i < count($parts); $i++) {
                        list($part_header, $part_body) = $this->_splitBodyHeader($parts[$i]);
                        $part = $this->_decode($part_header, $part_body, $default_ctype);
                        if ($part === false) {
                            throw new MailException($this->_error);
                        }
                        $return->parts[] = $part;
                    }
                    break;

                case 'message/rfc822':
					if ($this->_rfc822_bodies) {
						$encoding = isset($content_transfer_encoding) ? $content_transfer_encoding['value'] : '7bit';
						$return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $encoding) : $body);
					}
                    $obj = new MIMEDecode($body);
                    $return->parts[] = $obj->decode(['include_bodies' => $this->_include_bodies,
					                                      'decode_bodies'  => $this->_decode_bodies,
														  'decode_headers' => $this->_decode_headers]);
                    unset($obj);
                    break;

                default:
                    if(!isset($content_transfer_encoding['value']))
                        $content_transfer_encoding['value'] = '7bit';
                    $this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body, $content_transfer_encoding['value']) : $body) : null;
                    break;
            }

        } else {
            $ctype = explode('/', $default_ctype);
            $return->ctype_primary   = $ctype[0];
            $return->ctype_secondary = $ctype[1];
            $this->_include_bodies ? $return->body = ($this->_decode_bodies ? $this->_decodeBody($body) : $body) : null;
        }

        return $return;
    }

    /**
     * Given the output of the above function, this will return an
     * array of references to the parts, indexed by mime number.
     *
     * @param  object $structure   The structure to go through
     * @param  bool   $no_refs
     * @param  string $mime_number Internal use only.
     * @param  string $prepend
     * @return array               Mime numbers
     */
    public static function getMimeNumbers($structure, $no_refs = false, $mime_number = '', $prepend = '')
    {
        $return = [];
        if (!empty($structure->parts)) {
            if ($mime_number != '') {
                $structure->mime_id = $prepend . $mime_number;
                $return[$prepend . $mime_number] = &$structure;
            }
            for ($i = 0; $i < count($structure->parts); $i++) {

                if (!empty($structure->headers['content-type']) AND substr(strtolower($structure->headers['content-type']), 0, 8) == 'message/') {
                    $prepend      = $prepend . $mime_number . '.';
                    $_mime_number = '';
                } else {
                    $_mime_number = ($mime_number == '' ? $i + 1 : sprintf('%s.%s', $mime_number, $i + 1));
                }

                $arr = self::getMimeNumbers($structure->parts[$i], $no_refs, $_mime_number, $prepend);
                foreach ($arr as $key => $val) {
                    $no_refs ? $return[$key] = '' : $return[$key] = &$arr[$key];
                }
            }
        } else {
            if ($mime_number == '') {
                $mime_number = '1';
            }
            $structure->mime_id = $prepend . $mime_number;
            $no_refs ? $return[$prepend . $mime_number] = '' : $return[$prepend . $mime_number] = &$structure;
        }

        return $return;
    }

    /**
     * Given a string containing a header and body
     * section, this function will split them (at the first
     * blank line) and return them.
     *
     * @param string $input Input to split apart
     * @return array Contains header and body section
     * @access private
     */
    private function _splitBodyHeader($input)
    {
        if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $input, $match)) {
            return [$match[1], $match[2]];
        }
        // bug #17325 - empty bodies are allowed. - we just check that at least one line 
        // of headers exist..
        if (count(explode("\n",$input))) {
            return [$input, ''];
        }
        $this->_error = 'Could not split header and body';
        return false;
    }

    /**
     * Parse headers given in $input and return
     * as assoc array.
     *
     * @param string $input Headers to parse
     * @return array Contains parsed headers
     * @access private
     */
    private function _parseHeaders($input)
    {
        $return = [];

        if ($input !== '') {
            // Unfold the input
            $input   = preg_replace("/\r?\n/", "\r\n", $input);
            //#7065 - wrapping.. with encoded stuff.. - probably not needed,
            // wrapping space should only get removed if the trailing item on previous line is a 
            // encoded character
            $input   = preg_replace("/=\r\n(\t| )+/", '=', $input);
            $input   = preg_replace("/\r\n(\t| )+/", ' ', $input);

            $headers = explode("\r\n", trim($input));

            foreach ($headers as $value) {
                $hdr_name = substr($value, 0, $pos = strpos($value, ':'));
                $hdr_value = substr($value, $pos+1);
                if($hdr_value[0] == ' ')
                    $hdr_value = substr($hdr_value, 1);

                array_push($return, [
                    'name'  => $hdr_name,
                    'value' => $hdr_value
                ]);
            }
        }

        return $return;
    }

    /**
     * Function to parse a header value,
     * extract first part, and any secondary
     * parts (after ;) This function is not as
     * robust as it could be. Eg. header comments
     * in the wrong place will probably break it.
     *
     * @param string $input Header value to parse
     * @return array Contains parsed result
     * @access private
     */
    private function _parseHeaderValue($input)
    {

        if (($pos = strpos($input, ';')) === false) {
            $input = $this->_decode_headers ? $this->_decodeHeader($input) : $input;
            $return['value'] = trim($input);
            return $return;
        }

        $value = substr($input, 0, $pos);
        $value = $this->_decode_headers ? $this->_decodeHeader($value) : $value;
        $return['value'] = trim($value);
        $input = trim(substr($input, $pos+1));

        if (!strlen($input) > 0) {
            return $return;
        }
        // at this point input contains xxxx=".....";zzzz="...."
        // since we are dealing with quoted strings, we need to handle this properly..
        $i = 0;
        $l = strlen($input);
        $key = '';
        $val = false; // our string - including quotes..
        $q = false; // in quote..
        $lq = ''; // last quote..

        while ($i < $l) {

            $c = $input[$i];
            //var_dump(['i'=>$i,'c'=>$c,'q'=>$q, 'lq'=>$lq, 'key'=>$key, 'val' =>$val]);

            $escaped = false;
            if ($c == '\\') {
                $i++;
                if ($i == $l-1) { // end of string.
                    break;
                }
                $escaped = true;
                $c = $input[$i];
            }

            // state - in key..
            if ($val === false) {
                if (!$escaped && $c == '=') {
                    $val = '';
                    $key = trim($key);
                    $i++;
                    continue;
                }
                if (!$escaped && $c == ';') {
                    if ($key) { // a key without a value..
                        $key= trim($key);
                        $return['other'][$key] = '';
                        $return['other'][strtolower($key)] = '';
                    }
                    $key = '';
                }
                $key .= $c;
                $i++;
                continue;
            }

            // state - in value.. (as $val is set..)

            if ($q === false) {
                // not in quote yet.
                if ((!strlen($val) || $lq !== false) && $c == ' ' ||  $c == "\t") {
                    $i++;
                    continue; // skip leading spaces after '=' or after '"'
                }
                if (!$escaped && ($c == '"' || $c == "'")) {
                    // start quoted area..
                    $q = $c;
                    // in theory should not happen raw text in value part..
                    // but we will handle it as a merged part of the string..
                    $val = !strlen(trim($val)) ? '' : trim($val);
                    $i++;
                    continue;
                }
                // got end....
                if (!$escaped && $c == ';') {

                    $val = trim($val);
                    $added = false;
                    if (preg_match('/\*[0-9]+$/', $key)) {
                        // this is the extended aaa*0=...;aaa*1=.... code
                        // it assumes the pieces arrive in order, and are valid...
                        $key = preg_replace('/\*[0-9]+$/', '', $key);
                        if (isset($return['other'][$key])) {
                            $return['other'][$key] .= $val;
                            if (strtolower($key) != $key) {
                                $return['other'][strtolower($key)] .= $val;
                            }
                            $added = true;
                        }
                        // continue and use standard setters..
                    }
                    if (!$added) {
                        $return['other'][$key] = $val;
                        $return['other'][strtolower($key)] = $val;
                    }
                    $val = false;
                    $key = '';
                    $lq = false;
                    $i++;
                    continue;
                }

                $val .= $c;
                $i++;
                continue;
            }

            // state - in quote..
            if (!$escaped && $c == $q) {  // potential exit state..

                // end of quoted string..
                $lq = $q;
                $q = false;
                $i++;
                continue;
            }

            // normal char inside of quoted string..
            $val.= $c;
            $i++;
        }

        // do we have anything left..
        if (strlen(trim($key)) || $val !== false) {

            $val = trim($val);
            $added = false;
            if ($val !== false && preg_match('/\*[0-9]+$/', $key)) {
                // no dupes due to our crazy regexp.
                $key = preg_replace('/\*[0-9]+$/', '', $key);
                if (isset($return['other'][$key])) {
                    $return['other'][$key] .= $val;
                    if (strtolower($key) != $key) {
                        $return['other'][strtolower($key)] .= $val;
                    }
                    $added = true;
                }
                // continue and use standard setters..
            }
            if (!$added) {
                $return['other'][$key] = $val;
                $return['other'][strtolower($key)] = $val;
            }
        }
        // decode values.
        foreach($return['other'] as $key =>$val) {
            $return['other'][$key] = $this->_decode_headers ? $this->_decodeHeader($val) : $val;
        }

        return $return;
    }

    /**
     * This function splits the input based
     * on the given boundary
     *
     * @param string $input Input to parse
     * @param string $boundary
     * @return array Contains array of resulting mime parts
     * @access private
     */
    private function _boundarySplit($input, $boundary)
    {
        $parts = [];

        $bs_possible = substr($boundary, 2, -2);
        $bs_check = '\"' . $bs_possible . '\"';

        if ($boundary == $bs_check) {
            $boundary = $bs_possible;
        }
        $tmp = preg_split("/--".preg_quote($boundary, '/')."((?=\s)|--)/", $input);

        $len = count($tmp) -1;
        for ($i = 1; $i < $len; $i++) {
            if (strlen(trim($tmp[$i]))) {
                $parts[] = $tmp[$i];
            }
        }

        // add the last part on if it does not end with the 'closing indicator'
        if (!empty($tmp[$len]) && strlen(trim($tmp[$len])) && $tmp[$len][0] != '-') {
            $parts[] = $tmp[$len];
        }
        return $parts;
    }

    /**
     * Given a header, this function will decode it
     * according to RFC2047. Probably not *exactly*
     * conformant, but it does pass all the given
     * examples (in RFC2047).
     *
     * @param string $input Input header value to decode
     * @return string Decoded header value
     * @access private
     */
    private function _decodeHeader($input)
    {
        // Remove white space between encoded-words
        $input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);

        // For each encoded-word...
        while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {

            $encoded  = $matches[1];
            //$charset  = $matches[2];
            $encoding = $matches[3];
            $text     = $matches[4];

            switch (strtolower($encoding)) {
                case 'b':
                    $text = base64_decode($text);
                    break;

                case 'q':
                    $text = str_replace('_', ' ', $text);
                    preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
                    foreach($matches[1] as $value)
                        $text = str_replace('='.$value, chr(hexdec($value)), $text);
                    break;
            }

            $input = str_replace($encoded, $text, $input);
        }

        return $input;
    }

    /**
     * Given a body string and an encoding type,
     * this function will decode and return it.
     *
     * @param  string $input Input body to decode
     * @param  string $encoding Encoding type to use.
     * @return string Decoded body
     * @access private
     */
    private function _decodeBody($input, $encoding = '7bit')
    {
        switch (strtolower($encoding)) {
            case '7bit':
                return $input;
                break;

            case 'quoted-printable':
                return $this->_quotedPrintableDecode($input);
                break;

            case 'base64':
                return base64_decode($input);
                break;

            default:
                return $input;
        }
    }

    /**
     * Given a quoted-printable string, this
     * function will decode and return it.
     *
     * @param  string $input Input body to decode
     * @return string Decoded body
     * @access private
     */
    private function _quotedPrintableDecode($input)
    {
        // Remove soft line breaks
        $input = preg_replace("/=\r?\n/", '', $input);

        // Replace encoded characters
		$input = preg_replace('/=([a-f0-9]{2})/ie', "chr(hexdec('\\1'))", $input);

        return $input;
    }

    /**
     * Checks the input for uuencoded files and returns
     * an array of them. Can be called statically, eg:
     *
     * $files = MIMEDecode::uudecode($some_text);
     *
     * It will check for the begin 666 ... end syntax
     * however and won't just blindly decode whatever you
     * pass it.
     *
     * @param  string $input Input body to look for attahcments in
     * @return array  Decoded bodies, filenames and permissions
     * @access public
     * @author Unknown
     */
    public static function uudecode($input)
    {
        $files = [];

        // Find all uuencoded sections
        preg_match_all("/begin ([0-7]{3}) (.+)\r?\n(.+)\r?\nend/Us", $input, $matches);

        for ($j = 0; $j < count($matches[3]); $j++) {

            $str      = $matches[3][$j];
            $filename = $matches[2][$j];
            $fileperm = $matches[1][$j];

            $file = '';
            $str = preg_split("/\r?\n/", trim($str));
            $strlen = count($str);

            for ($i = 0; $i < $strlen; $i++) {
                $pos = 1;
                $d = 0;
                $len=(int)(((ord(substr($str[$i],0,1)) -32) - ' ') & 077);

                while (($d + 3 <= $len) AND ($pos + 4 <= strlen($str[$i]))) {
                    $c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
                    $c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
                    $c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
                    $c3 = (ord(substr($str[$i],$pos+3,1)) ^ 0x20);
                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

                    $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

                    $file .= chr(((($c2 - ' ') & 077) << 6) |  (($c3 - ' ') & 077));

                    $pos += 4;
                    $d += 3;
                }

                if (($d + 2 <= $len) && ($pos + 3 <= strlen($str[$i]))) {
                    $c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
                    $c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
                    $c2 = (ord(substr($str[$i],$pos+2,1)) ^ 0x20);
                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

                    $file .= chr(((($c1 - ' ') & 077) << 4) | ((($c2 - ' ') & 077) >> 2));

                    $pos += 3;
                    $d += 2;
                }

                if (($d + 1 <= $len) && ($pos + 2 <= strlen($str[$i]))) {
                    $c0 = (ord(substr($str[$i],$pos,1)) ^ 0x20);
                    $c1 = (ord(substr($str[$i],$pos+1,1)) ^ 0x20);
                    $file .= chr(((($c0 - ' ') & 077) << 2) | ((($c1 - ' ') & 077) >> 4));

                }
            }
            $files[] = ['filename' => $filename, 'fileperm' => $fileperm, 'filedata' => $file];
        }

        return $files;
    }

    /**
     * DEPRECATED (this method is related with "PEAR" library)
     *
     * getSendArray() returns the arguments required for Mail::send()
     * used to build the arguments for a mail::send() call 
     *
     * Usage:
     * $mailtext = Full email (for example generated by a template)
     * $decoder = new MIMEDecode($mailtext);
     * try {
     *     $parts =  $decoder->getSendArray();
     *     list($recipents,$headers,$body) = $parts;
     *     $mail = Mail::factory('smtp');
     *     $mail->send($recipents,$headers,$body);
     * } catch (MailException $e) {
     *     echo $e->getMessage();
     * }
     *
     * @return mixed   array of recipeint, headers,body or Pear_Error
     * @throws MailException
     * @access public
     * @author Alan Knowles <alan@akbkhome.com>
     */
    public function getSendArray()
    {
        $header = [];

        // prevent warning if this is not set
        $this->_decode_headers = false;
        $headerlist =$this->_parseHeaders($this->_header);
        $to = "";
        if (!$headerlist) {
            throw new MailException("Message did not contain headers");
        }
        foreach($headerlist as $item) {
            $header[$item['name']] = $item['value'];
            switch (strtolower($item['name'])) {
                case "to":
                case "cc":
                case "bcc":
                    $to .= "," . $item['value'];
                    break;
                default:
                    break;
            }
        }
        if ($to == "") {
            throw new MailException("Message did not contain any recipents");
        }
        $to = substr($to, 1);
        return [$to, $header, $this->_body];
    } 

    /**
     * Returns a xml copy of the output of
     * MIMEDecode::decode. Pass the output in as the
     * argument. This function can be called statically. Eg:
     *
     * $obj    = new MIMEDecode($strHeader);
     * $output = $obj->decode();
     * $xml    = $obj->getXML($output);
     *
     * The DTD used for this should have been in the package. Or
     * alternatively you can get it from cvs, or here:
     * http://www.phpguru.org/xmail/xmail.dtd.
     *
     * @param  object $input Input to convert to xml. This should be the
     *                output of the MIMEDecode::decode function
     * @return string XML version of input
     * @access public
     */
    public function getXML($input)
    {
        $crlf    =  "\r\n";
        $output  = '<?xml version=\'1.0\'?>' . $crlf .
                   '<!DOCTYPE email SYSTEM "http://www.phpguru.org/xmail/xmail.dtd">' . $crlf .
                   '<email>' . $crlf .
                   $this->_getXML($input) .
                   '</email>';

        return $output;
    }

    /**
     * Function that does the actual conversion to xml. Does a single
     * mimepart at a time.
     *
     * @param  object  $input Input to convert to xml. This is a mimepart object.
     *                 It may or may not contain subparts.
     * @param  integer $indent Number of tabs to indent
     * @return string  XML version of input
     * @access private
     */
    private function _getXML($input, $indent = 1)
    {
        $htab    =  "\t";
        $crlf    =  "\r\n";
        $output  =  '';
        $headers = @(array)$input->headers;

        foreach ($headers as $hdr_name => $hdr_value) {

            // Multiple headers with this name
            if (is_array($headers[$hdr_name])) {
                for ($i = 0; $i < count($hdr_value); $i++) {
                    $output .= $this->_getXML_helper($hdr_name, $hdr_value[$i], $indent);
                }

            // Only one header of this sort
            } else {
                $output .= $this->_getXML_helper($hdr_name, $hdr_value, $indent);
            }
        }

        if (!empty($input->parts)) {
            for ($i = 0; $i < count($input->parts); $i++) {
                $output .= $crlf . str_repeat($htab, $indent) . '<mimepart>' . $crlf .
                           $this->_getXML($input->parts[$i], $indent+1) .
                           str_repeat($htab, $indent) . '</mimepart>' . $crlf;
            }
        } elseif (isset($input->body)) {
            $output .= $crlf . str_repeat($htab, $indent) . '<body><![CDATA[' .
                       $input->body . ']]></body>' . $crlf;
        }

        return $output;
    }

    /**
     * Helper function to _getXML(). Returns xml of a header.
     *
     * @param  string  $hdr_name Name of header
     * @param  string  $hdr_value Value of header
     * @param  integer $indent Number of tabs to indent
     * @return string  XML version of input
     * @access private
     */
    private function _getXML_helper($hdr_name, $hdr_value, $indent)
    {
        $params = [];
        $htab   = "\t";
        $crlf   = "\r\n";

        $new_hdr_value = ($hdr_name != 'received') ? $this->_parseHeaderValue($hdr_value) : ['value' => $hdr_value];
        $new_hdr_name  = str_replace(' ', '-', ucwords(str_replace('-', ' ', $hdr_name)));

        // Sort out any parameters
        if (!empty($new_hdr_value['other'])) {
            foreach ($new_hdr_value['other'] as $paramname => $paramvalue) {
                $params[] = str_repeat($htab, $indent) . $htab . '<parameter>' . $crlf .
                            str_repeat($htab, $indent) . $htab . $htab . '<paramname>' . htmlspecialchars($paramname) . '</paramname>' . $crlf .
                            str_repeat($htab, $indent) . $htab . $htab . '<paramvalue>' . htmlspecialchars($paramvalue) . '</paramvalue>' . $crlf .
                            str_repeat($htab, $indent) . $htab . '</parameter>' . $crlf;
            }

            $params = implode('', $params);
        } else {
            $params = '';
        }

        return str_repeat($htab, $indent) . '<header>' . $crlf .
            str_repeat($htab, $indent) . $htab . '<headername>' . htmlspecialchars($new_hdr_name) . '</headername>' . $crlf .
            str_repeat($htab, $indent) . $htab . '<headervalue>' . htmlspecialchars($new_hdr_value['value']) . '</headervalue>' . $crlf .
            $params .
            str_repeat($htab, $indent) . '</header>' . $crlf;
    }

}
