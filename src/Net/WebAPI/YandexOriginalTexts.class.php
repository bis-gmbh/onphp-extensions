<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

/**
 * Class YandexOriginalTexts
 */
final class YandexOriginalTexts extends YandexAPI
{
	const TEXTS_PER_DAY = 100;

	const MIN_LENGTH = 500;
	const MAX_LENGTH = 32000;

	/**
	 * @return YandexOriginalTexts
	 */
	public static function create()
	{
		return new self;
	}

	/**
	 * Docs https://tech.yandex.ru/webmaster/doc/dg/reference/host-original-texts-docpage/
	 * 
	 * @param $siteId
	 * @return array|bool
	 */
	public function getList($siteId)
	{
		$serviceDocument = $this->getServiceDocument();
		$url     = $serviceDocument . '/' . $siteId . '/original-texts/';
		$host    = parse_url($url, PHP_URL_HOST);
		$path    = parse_url($url, PHP_URL_PATH);
		$headers = array(
			'GET ' . $path . ' HTTP/1.1',
			'Host: ' . $host,
			'Authorization: OAuth ' . $this->accessToken,
		);

		$curlOptions = array(
			CURLOPT_URL             => $url,
			CURLOPT_CONNECTTIMEOUT  => 10,
			CURLOPT_FRESH_CONNECT   => 1,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_FORBID_REUSE    => 1,
			CURLOPT_TIMEOUT         => 5,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_HTTPHEADER      => $headers,
		);

		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$result = curl_exec($ch);
		$info   = curl_getinfo($ch);

		if ($info['http_code'] === 200) {
			// TODO: build data array with founded texts and return it
			return true;
		}
		return false;
	}

	/**
	 * Docs https://tech.yandex.ru/webmaster/doc/dg/reference/host-original-texts-add-docpage/
	 * 
	 * @param string $siteId
	 * @param string $text
	 * @return bool
	 */
	public function add($siteId, $text)
	{
		if ( ! self::isValid($text)) {
			return false;
		}
		$serviceDocument = $this->getServiceDocument();
		$url     = $serviceDocument . '/' . $siteId . '/original-texts/';
		$host    = parse_url($serviceDocument, PHP_URL_HOST);
		$path    = parse_url($serviceDocument, PHP_URL_PATH);
		$headers = array(
			'POST ' . $path . ' HTTP/1.1',
			'Host: ' . $host,
			'Authorization: OAuth ' . $this->accessToken,
			'Content-Length: ' . strlen($text),
		);

		$curlOptions = array(
			CURLOPT_URL             => $url,
			CURLOPT_CONNECTTIMEOUT  => 10,
			CURLOPT_FRESH_CONNECT   => 1,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_FORBID_REUSE    => 1,
			CURLOPT_TIMEOUT         => 5,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_HTTPHEADER      => $headers,
			CURLOPT_POSTFIELDS      => $text,
		);

		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		$result = curl_exec($ch);
		$info   = curl_getinfo($ch);

		if ($info['http_code'] === 201) {
			return true;
		}
		return false;
	}

	/**
	 * Docs https://tech.yandex.ru/webmaster/doc/dg/reference/host-original-texts-delete-docpage/
	 * 
	 * @param string $href Адрес операции original-texts определяется ссылкой вида <link href=" ... " rel="original-texts"/>, содержащейся в ответе сервиса на запрос информации о сайте
	 * @return bool
	 */
	public function delete($href)
	{
		$url     = $href;
		$host    = parse_url($url, PHP_URL_HOST);
		$path    = parse_url($url, PHP_URL_PATH);
		$headers = array(
			'DELETE ' . $path . ' HTTP/1.1',
			'Host: ' . $host,
			'Authorization: OAuth ' . $this->accessToken,
		);

		$curlOptions = array(
			CURLOPT_URL             => $url,
			CURLOPT_CONNECTTIMEOUT  => 1,
			CURLOPT_FRESH_CONNECT   => 1,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_FORBID_REUSE    => 1,
			CURLOPT_TIMEOUT         => 5,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_HTTPHEADER      => $headers,
			CURLOPT_CUSTOMREQUEST   => 'DELETE',
		);

		$ch = curl_init();
		curl_setopt_array($ch, $curlOptions);
		curl_exec($ch);
		$info   = curl_getinfo($ch);

		if ($info['http_code'] === 204) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $text
	 * @return bool
	 */
	public static function isValid($text)
	{
		$textLength = strlen($text);
		if (
			$textLength >= self::MIN_LENGTH
			&& $textLength <= self::MAX_LENGTH
		) {
			return true;
		}
		return false;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public static function prepare($text)
	{
		$text = strip_tags($text);
		$text = htmlspecialchars($text);
		$text = '<original-text><content>' . $text . '</content></original-text>';
		$text = urlencode($text);
		return $text;
	}
}
