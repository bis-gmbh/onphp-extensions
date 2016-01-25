<?php
/**
 * textreporter.ru (https://textreporter.ru/)
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2015 Barzmann Internet Solutions GmbH
 */

/**
 * Class VKServerAuthStrategy
 */
class VKServerAuthStrategy implements VKAuthStrategyInterface
{
	/**
	 * @param VKAuthenticator $authObj
	 * @return string
	 */
	public function getAuthDialogUrl(VKAuthenticator $authObj)
	{
		return 'https://oauth.vk.com/authorize?'
			. 'client_id=' . $authObj->getAppId()
			. '&scope=' . $authObj->getPermissions()
			. '&redirect_uri=' . $authObj->getRedirectUri()
			. '&response_type=code'
			. '&v=' . VKAuthenticator::API_VERSION
			. '&state=' . $authObj->getSessionState();
	}

	/**
	 * @param VKAuthenticator $authObj
	 * @param $code
	 * @return mixed|null
	 */
	public function getToken(VKAuthenticator $authObj, $code)
	{
		$url = 'https://oauth.vk.com/access_token?'
			. 'client_id=' . $authObj->getAppId()
			. '&client_secret=' . $authObj->getAppSecret()
			. '&code=' . $code
			. '&redirect_uri=' . $authObj->getRedirectUri();

		$curlOptions = [
			CURLOPT_HEADER          => 0,
			CURLOPT_URL             => $url,
			CURLOPT_CONNECTTIMEOUT  => 1,
			CURLOPT_FRESH_CONNECT   => 1,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_FORBID_REUSE    => 1,
			CURLOPT_TIMEOUT         => 5,
			CURLOPT_SSL_VERIFYPEER  => false,
		];
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
}