<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\OAuth2;

use \Facebook\Facebook;
use \Assert;
use \WrongArgumentException;
use \Facebook\Authentication\AccessToken;
use \Facebook\Exceptions\FacebookSDKException;

/**
 * Class FBAuthenticator
 * 
 * @see https://developers.facebook.com/docs/php/gettingstarted/5.0.0
 */
class FBAuthenticator implements OAuth2Interface
{
	const API_VERSION = 'v2.10';

	private $appId;

	private $appSecret;

	private $redirectUri = '';

	/**
	 * ['manage_pages', 'publish_pages', 'user_photos', 'publish_actions']
	 * @see https://developers.facebook.com/docs/facebook-login/permissions/v2.4
	 * 
	 * @var array
	 */
	private $permissions = [];

	private $fb = null;

	/**
	 * $params = [
	 *     'appId'       => 'FB_APP_ID',       // required
	 *     'appSecret'   => 'FB_APP_SECRET',   // required
	 *     'redirectUri' => '/auth/endpoint',  // optional
	 *     'permissions' => ['publish_pages'], // optional
	 * ];
	 * 
	 * @param array $params
	 */
	public function __construct(array $params)
	{
		$this->appId = $params['appId'];
		$this->appSecret = $params['appSecret'];

		if (isset($params['redirectUri'])) {
			$this->setRedirectUri($params['redirectUri']);
		}
		if (isset($params['permissions'])) {
			$this->setPermissions($params['permissions']);
		}
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
		$this->fb = new Facebook([
			'app_id' => $this->appId,
			'app_secret' => $this->appSecret,
			'default_graph_version' => self::API_VERSION,
			'persistent_data_handler' => 'session',
		]);
	}

	/**
	 * @return string
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}

	/**
	 * @return string
	 */
	public function getRedirectUri()
	{
		return $this->redirectUri;
	}

	/**
	 * @param array $perms
	 * @return $this
	 * @throws WrongArgumentException
	 */
	public function setPermissions($perms)
	{
		Assert::isArray($perms);
		$this->permissions = $perms;
		return $this;
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
		$helper = $this->fb->getRedirectLoginHelper();
		return $helper->getLoginUrl(
			$this->getRedirectUri(),
			$this->permissions
		);
	}

	/**
	 * @return string
	 */
	public function getRefreshTokenUrl()
	{
		$helper = $this->fb->getRedirectLoginHelper();
		return $helper->getReRequestUrl(
			$this->getRedirectUri(),
			$this->permissions
		);
	}

	/**
	 * @see https://developers.facebook.com/docs/php/FacebookRedirectLoginHelper/5.0.0
	 * @param $code
	 * @return mixed|null
	 */
	public function getToken($code)
	{
		$response = new \StdClass();

		/** @var AccessToken|null $accessToken */
		$accessToken = null;

		$helper = $this->fb->getRedirectLoginHelper();
		try {
			$accessToken = $helper->getAccessToken();
		} catch (FacebookSDKException $e) {
			// When Graph returns an error or validation fails or other local issues
			$response->error = $e->getCode();
			$response->error_description = $e->getMessage();
			return $response;
		}
		if (isset($accessToken)) {
			// Logged in!
			$expiresAt = $accessToken->getExpiresAt();
			if ($expiresAt instanceof \DateTime) {
				$response->access_token = $accessToken->getValue();
				$response->expires_in = $expiresAt->getTimestamp();
			} else {
				$response->error = 0;
				$response->error_description = 'Expiration date was not originally provided';
			}
			return $response;
		} else if ($helper->getError()) {
			// The user denied the request
			$response->error = $helper->getError();
			$response->error_description = $helper->getErrorDescription();
			return $response;
		}
		return null;
	}
}
