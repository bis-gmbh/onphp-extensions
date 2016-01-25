<?php
/**
 * textreporter.ru (https://textreporter.ru/)
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2015 Barzmann Internet Solutions GmbH
 */

/**
 * Class VKClientAuthStrategy
 */
class VKClientAuthStrategy implements VKAuthStrategyInterface
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
			. '&redirect_uri=https://api.vk.com/blank.html'
			. '&response_type=token'
			. '&revoke=1'
			. '&display=popup'
			. '&v=' . VKAuthenticator::API_VERSION;
	}

	/**
	 * @param VKAuthenticator $authObj
	 * @param string $code URL-encoded string, e.g. https%3A%2F%2Fapi.vk.com%2Fblank.html%23access_token%3D0123456789abcdef0123456789abcdef%26expires_in%3D0%26user_id%3D1
	 *                     or directly access token value
	 * @return object
	 */
	public function getToken(VKAuthenticator $authObj, $code)
	{
		$result = [];

		if (preg_match(VKAuthenticator::TOKEN_REGEXP, $code)) {
			return (object)[
				'access_token' => $code,
				'expires_in' => 0,
			];
		}

		try {
			$parsedUrl = parse_url($code, PHP_URL_FRAGMENT);
			parse_str($parsedUrl, $result);

			$form = Form::create();
			$form->add(
				Primitive::string('access_token')->
					setAllowedPattern(VKAuthenticator::TOKEN_REGEXP)->
					optional()
			);
			$form->add(
				Primitive::integer('expires_in')->
					setMin(0)->
					optional()
			);
			$form->import($result);

			if ($form->getErrors()) {
				throw new Exception('Получены некорректные параметры');
			}

		} catch(Exception $e) {
			unset($result['access_token']);
			unset($result['expires_in']);

			$result['error'] = 'Ошибка получения токена';
			$result['error_description'] = $e->getMessage();
		}

		if ( ! isset($result['expires_in'])) {
			$result['expires_in'] = 0;
		}

		return (object)$result;
	}
}
