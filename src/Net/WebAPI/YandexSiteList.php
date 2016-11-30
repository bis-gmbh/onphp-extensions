<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\WebAPI;

/**
 * Class YandexSiteList
 */
final class YandexSiteList extends YandexAPI
{
	private $siteList = array();

	/**
	 * @return YandexSiteList
	 */
	public static function create()
	{
		return new self;
	}

	/**
	 * @return array|bool
	 */
	public function getSiteList()
	{
		$clientId = $this->getClientId();
		$url     = 'https://api.webmaster.yandex.net/v3/user/' . $clientId . '/hosts/';

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

		if (
			$info['http_code'] === 200
			&& ($data = json_decode($result, true))
			&& (array_key_exists('hosts', $data))
		) {
			foreach ($data['hosts'] as $host) {
				if ($host['verified']) {
					$href = $host['unicode_host_url'];

					if (parse_url($href, PHP_URL_SCHEME)) {
						$name = parse_url($href, PHP_URL_HOST);
					} else {
						$name = $href;
					}

					$refinedName = preg_replace('/^www\./', '', $name);

					$this->siteList[]  = array(
						'href' => $href,
						'name' => $refinedName,
						'id'   => $host['host_id'],
					);
				}
			}
		} elseif (
			$info['http_code'] === 401/* || $info['http_code'] === 403*/
		) {
			return false;
		}

		return $this->siteList;
	}

	/**
	 * @param array $siteList return value of YandexSiteList::getSiteList()
	 * @param string $siteName
	 * @return bool
	 */
	public static function match($siteList, $siteName)
	{
		$refinedSiteName = preg_replace('/^www\./', '', $siteName);
		if (is_array($siteList)) {
			foreach ($siteList as $site) {
				if (
					isset($site['name'])
					&& strtolower($site['name']) === $refinedSiteName
				) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param array $siteList
	 * @param string $siteName
	 * @return integer|null
	 */
	public static function getSiteIdByName($siteList, $siteName)
	{
		$siteName = preg_replace('/^www\./', '', $siteName);
		if (is_array($siteList)) {
			foreach ($siteList as $site) {
				if (
					isset($site['name'])
					&& strtolower($site['name']) === $siteName
				) {
					return $site['id'];
				}
			}
		}
		return null;
	}
}
