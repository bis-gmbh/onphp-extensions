<?php
/***************************************************************************
 *   Copyright (C) 2014 - 2015 by Barzmann Internet Solutions              *
 *   Author Dmitry Nezhelskoy <dmitry@nezhelskoy.ru>                       *
 ***************************************************************************/

/**
 * Class YandexAPI
 */
class YandexAPI
{
	/**
	 * @var TokenStorage|null
	 */
	protected $token = null;

	private static $serviceDocument = '';

	/**
	 * @param TokenStorage $token
	 */
	public function __construct(TokenStorage $token = null)
	{
		if ($token) {
			$this->setToken($token);
		}
	}

	/**
	 * @param TokenStorage $token
	 * @return $this
	 * @throws WebAPIException
	 */
	public function setToken(TokenStorage $token)
	{
		$wsType = (int)$token->getWebServiceType()->getId();
		if ($wsType !== WebServiceType::YA) {
			throw new WebAPIException('Wrong token type. Expected to ' . WebServiceType::YA . ', but ' . $wsType . ' given.');
		}
		$this->token = $token;
		return $this;
	}

	protected function dropRevokedToken()
	{
		TokenStorage::dao()->drop($this->token);
		$this->token = null;
	}

	protected function getServiceDocument()
	{
		if (empty(self::$serviceDocument)) {
			$url     = 'https://webmaster.yandex.ru/api/v2';
			$headers = array(
				'GET /api/v2 HTTP/1.1',
				'Host: webmaster.yandex.ru',
				'Authorization: OAuth ' . $this->token->getAccessToken(),
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
				$service = new SimpleXMLElement($result);
				self::$serviceDocument = $service->workspace->collection['href'];
			}
		}
		return self::$serviceDocument;
	}
}
