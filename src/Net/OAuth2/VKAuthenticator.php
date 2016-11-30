<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\OAuth2;

use \Assert;
use \WrongArgumentException;

/**
 * Class VKAuthenticator
 * 
 * @see https://vk.com/dev/auth_sites
 */
class VKAuthenticator implements OAuth2Interface
{
	const API_VERSION = 5.34;

	const TOKEN_REGEXP = '/^[a-z0-9]{32,255}$/i';

	private $appId;

	private $appSecret;

	private $redirectUri = '';

	/**
	 * @var string $permissions https://vk.com/dev/permissions
	 */
	private $permissions = '';

	private $sessionState = '';

	/**
	 * @var VKAuthStrategyInterface|null
	 */
	private $authStrategy = null;

	/**
	 * $params = [
	 *     'appId'        => 'VK_APP_ID',      // required
	 *     'appSecret'    => 'VK_APP_SECRET',  // required
	 *     'redirectUri'  => '/auth/endpoint', // optional
	 *     'permissions'  => 'wall',           // optional
	 *     'sessionState' => '',               // optional
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
		if (isset($params['sessionState'])) {
			$this->setSessionState($params['sessionState']);
		}
		if (isset($params['permissions'])) {
			$this->setPermissions($params['permissions']);
		}

		$this->setAuthStrategy(new VKServerAuthStrategy);
	}

	/**
	 * @return string
	 */
	public function getAppId()
	{
		return $this->appId;
	}

	/**
	 * @return string
	 */
	public function getAppSecret()
	{
		return $this->appSecret;
	}

	/**
	 * @return string
	 */
	public function getRedirectUri()
	{
		return $this->redirectUri;
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
	public function getSessionState()
	{
		return $this->sessionState;
	}

	/**
	 * @param VKAuthStrategyInterface $strategy
	 */
	public function setAuthStrategy(VKAuthStrategyInterface $strategy)
	{
		$this->authStrategy = $strategy;
	}

	/**
	 * @param $uri
	 * @return $this
	 * @throws WrongArgumentException
	 */
	public function setRedirectUri($uri)
	{
		Assert::isString($uri);
		$this->redirectUri = rawurlencode($uri);
		return $this;
	}

	/**
	 * @param $perms
	 * @return $this
	 * @throws WrongArgumentException
	 */
	public function setPermissions($perms)
	{
		Assert::isString($perms);
		$this->permissions = $perms;
		return $this;
	}

	/**
	 * @param $stateValue
	 * @return $this
	 * @throws WrongArgumentException
	 */
	public function setSessionState($stateValue)
	{
		Assert::isString($stateValue);
		$this->sessionState = $stateValue;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getAuthDialogUrl()
	{
		return $this->authStrategy->getAuthDialogUrl($this);
	}

	/**
	 * @param $code
	 * @return mixed|null
	 */
	public function getToken($code)
	{
		return $this->authStrategy->getToken($this, $code);
	}
}
