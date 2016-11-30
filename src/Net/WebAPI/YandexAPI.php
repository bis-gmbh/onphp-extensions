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
use \Onphp\Extensions\Net\CurlTrait;

/**
 * Class YandexAPI
 */
class YandexAPI
{
	use CurlTrait;

	/**
	 * @var string
	 */
	protected $accessToken;

	private $clientId = '';

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

	public function getClientId()
	{
		if (empty($this->clientId)) {
			$url     = 'https://api.webmaster.yandex.net/v3/user/';

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
			);
			$ch = curl_init();
			curl_setopt_array($ch, $this->getCurlOptions($curlOptions));
			$result = curl_exec($ch);
			$info   = curl_getinfo($ch);

			if ($info['http_code'] === 200) {
				if ( ($data = json_decode($result, true)) && !empty($data['user_id']) ) {
					$this->clientId = $data['user_id'];
				}
			}
		}

		return $this->clientId;
	}
}
