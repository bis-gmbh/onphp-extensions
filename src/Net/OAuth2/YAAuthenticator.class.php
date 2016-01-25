<?php
/***************************************************************************
 *   Copyright (C) 2014 - 2015 by Barzmann Internet Solutions              *
 *   Author Dmitry Nezhelskoy <dmitry@nezhelskoy.ru>                       *
 ***************************************************************************/

/**
 * Class YAAuthenticator
 */
class YAAuthenticator implements OAuth2Interface
{
	private $redirectUri = '';

	/**
	 * @param array $params
	 */
	public function __construct(array $params = [])
	{
		if (isset($params['redirectUri'])) {
			$this->setRedirectUri($params['redirectUri']);
		}
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
				. '&client_id=' . YANDEX_ID;
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
			. '&client_id=' . YANDEX_ID
			. '&client_secret=' . YANDEX_PASSWORD;
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
		curl_setopt_array($ch, $curlOptions);
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

	/**
	 * @param Member $member
	 * @return TokenStorage|null
	 */
	public static function getExistNotExpiredToken(Member $member)
	{
		$token = TokenStorage::dao()->findToken($member, new WebServiceType(WebServiceType::YA));
		if ($token) {
			$expiredDays = $token->getExpiresIn()/86400;
			$days = Date::dayDifference(
				Timestamp::makeNow(),
				Timestamp::create($token->getCreated()->toStamp())
			);
			if ($days < $expiredDays) {
				return $token;
			}
		}
		return null;
	}
}
