<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\WebAPI;

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
		$clientId = $this->getClientId();
		$url     = 'https://api.webmaster.yandex.net/v3/user/' . $clientId . '/hosts/' . $siteId . '/original-texts/';

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
		curl_setopt_array($ch, $this->getCurlOptions($curlOptions));
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
	 * @throws WebAPIException
	 */
	public function add($siteId, $text)
	{
//		if ( ! self::isValid($text)) {
//			return false;
//		}
		$clientId = $this->getClientId();
		$url     = 'https://api.webmaster.yandex.net/v3/user/' . $clientId . '/hosts/' . $siteId . '/original-texts/';

		$headers = array(
			'Authorization: OAuth ' . $this->accessToken,
			'Content-Type: application/json',
		);

		$postData = json_encode(
			array(
				'content' => $text,
			),
			JSON_UNESCAPED_UNICODE
		);

		$curlOptions = array(
			CURLOPT_URL				 => $url,
			CURLOPT_CONNECTTIMEOUT	 => 10,
			CURLOPT_FRESH_CONNECT	 => 1,
			CURLOPT_RETURNTRANSFER	 => 1,
			CURLOPT_FORBID_REUSE	 => 1,
			CURLOPT_TIMEOUT			 => 5,
			CURLOPT_SSL_VERIFYPEER	 => false,
			CURLOPT_HTTPHEADER		 => $headers,
			CURLOPT_POST			 => 1,
			CURLOPT_POSTFIELDS		 => $postData,
		);

		$ch = curl_init();
		curl_setopt_array($ch, $this->getCurlOptions($curlOptions));
		$result = curl_exec($ch);
		$info   = curl_getinfo($ch);

		if ($info['http_code'] === 201) {
			return true;
		} else {
			/** @see https://tech.yandex.ru/webmaster/doc/dg/reference/errors-docpage/ */
			$code    = (int)$info['http_code'];
			$message = 'Unknown error';

			if ( 
				($data = json_decode($result, true))
				&& array_key_exists('error_code', $data)
				&& array_key_exists('error_message', $data)
			) {
				if ($data['error_code'] == 'TEXT_ALREADY_ADDED') {
					return true;
				}

				$message = $data['error_code'] . ': ' . $data['error_message'];
			}

			throw new WebAPIException($message, $code);
		}
	}

	/**
	 * Docs https://tech.yandex.ru/webmaster/doc/dg/reference/host-original-texts-delete-docpage/
	 * 
	 * @param string $siteId
	 * @param string $textId
	 * @return bool
	 */
	public function delete($siteId, $textId)
	{
		$clientId = $this->getClientId();
		$url     = 'https://api.webmaster.yandex.net/v3/user/' . $clientId . '/hosts/' . $siteId . '/original-texts/' . $textId . '/';

		$headers = array(
			'Authorization: OAuth ' . $this->accessToken,
		);

		$curlOptions = array(
			CURLOPT_URL             => $url,
			CURLOPT_CONNECTTIMEOUT  => 5,
			CURLOPT_FRESH_CONNECT   => 1,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_FORBID_REUSE    => 1,
			CURLOPT_TIMEOUT         => 5,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_HTTPHEADER      => $headers,
			CURLOPT_CUSTOMREQUEST   => 'DELETE',
		);

		$ch = curl_init();
		curl_setopt_array($ch, $this->getCurlOptions($curlOptions));
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
		$text = html_entity_decode($text);
		$text = strip_tags($text);

		return $text;
	}
}
