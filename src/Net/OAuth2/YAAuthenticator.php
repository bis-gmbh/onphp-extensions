<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\OAuth2;

use \Onphp\Extensions\Net\CurlTrait;
use \WrongArgumentException;
use \Assert;

/**
 * Class YAAuthenticator
 */
class YAAuthenticator implements OAuth2Interface
{
	use CurlTrait;

	private $yandexId;

	private $yandexPassword;

	private $redirectUri = '';

	/**
	 * $params = [
	 *     'yandexId'       => 'YANDEX_ID',       // required
	 *     'yandexPassword' => 'YANDEX_PASSWORD', // required
	 *     'redirectUri'    => '/auth/endpoint',  // optional
	 * ];
	 * 
	 * @param array $params
	 */
	public function __construct(array $params)
	{
		$this->yandexId = $params['yandexId'];
		$this->yandexPassword = $params['yandexPassword'];
	}

	/**
	 * @param $uri
	 * @return $this
	 * @throws WrongArgumentException
	 */
	public function setRedirectUri($uri)
	{
		Assert::isString($uri);
		$this->redirectUri = $uri;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAuthDialogUrl()
	{
		return
			'https://oauth.yandex.ru/authorize?'
				. 'response_type=code'
				. '&client_id=' . $this->yandexId;
	}

	/**
	 * @param $code
	 * @return mixed|null
	 */
	public function getToken($code)
	{
		$url      = 'https://oauth.yandex.ru/token';
		$postData = 'grant_type=authorization_code'
			. '&code=' . $code
			. '&client_id=' . $this->yandexId
			. '&client_secret=' . $this->yandexPassword;
		$headers  = array(
			'POST /token HTTP/1.1',
			'Host: oauth.yandex.ru',
			'Content-type: application/x-www-form-urlencoded',
			'Content-Length: ' . strlen($postData),
		);
		$curlOptions = array(
			CURLOPT_POST            => 1,
			CURLOPT_HEADER          => 0,
			CURLOPT_URL             => $url,
			CURLOPT_CONNECTTIMEOUT  => 1,
			CURLOPT_FRESH_CONNECT   => 1,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_FORBID_REUSE    => 1,
			CURLOPT_TIMEOUT         => 5,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_POSTFIELDS      => $postData,
			CURLOPT_HTTPHEADER      => $headers
		);
		$ch = curl_init();
		curl_setopt_array($ch, $this->getCurlOptions($curlOptions));
		$result = curl_exec($ch);
		$info   = curl_getinfo($ch);
		if ($info['http_code'] === 200) {
			return json_decode($result);
		} else {
			$error = json_decode($result);
			if ($error) {
				return $error;
			}
		}
		return null;
	}
}
