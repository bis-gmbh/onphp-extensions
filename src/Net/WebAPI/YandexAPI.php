<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\WebAPI;
use \WrongArgumentException;
use \Assert;

/**
 * Class YandexAPI
 */
class YandexAPI
{
	/**
	 * @var string
	 */
	protected $accessToken;

	private static $serviceDocument = '';

	/**
	 * @param string $accessToken|null
	 */
	public function __construct($accessToken = null)
	{
		if ($accessToken) {
			$this->setAccessToken($accessToken);
		}
	}

	/**
	 * @param string $accessToken
	 * @return $this
	 * @throws WrongArgumentException
	 */
	public function setAccessToken($accessToken)
	{
		Assert::isString($accessToken);
		$this->accessToken = $accessToken;
		return $this;
	}

	protected function getServiceDocument()
	{
		if (empty(self::$serviceDocument)) {
			$url     = 'https://webmaster.yandex.ru/api/v2';
			$headers = array(
				'GET /api/v2 HTTP/1.1',
				'Host: webmaster.yandex.ru',
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
			);
			$ch = curl_init();
			curl_setopt_array($ch, $curlOptions);
			$result = curl_exec($ch);
			$info   = curl_getinfo($ch);
			if ($info['http_code'] === 200) {
				$service = new \SimpleXMLElement($result);
				self::$serviceDocument = $service->workspace->collection['href'];
			}
		}
		return self::$serviceDocument;
	}
}
