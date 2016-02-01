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
		$serviceDocument = $this->getServiceDocument();
		$url     = $serviceDocument;
		$host    = parse_url($serviceDocument, PHP_URL_HOST);
		$path    = parse_url($serviceDocument, PHP_URL_PATH);
		$headers = array(
			'GET ' . $path . ' HTTP/1.1',
			'Host: ' . $host,
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
			$hostlist = new \SimpleXMLElement($result);
			foreach ($hostlist->host as $host) {
				$state = $host->verification['state']->__toString();
				if ($state === 'VERIFIED') {
					$href     = $host['href']->__toString();
					$path     = parse_url($href, PHP_URL_PATH);
					$pathPart = explode('/', $path);
					if (parse_url($host->name->__toString(), PHP_URL_SCHEME)) {
						$name = parse_url($host->name->__toString(), PHP_URL_HOST);
					} else {
						$name = $host->name->__toString();
					}
					$refinedName = preg_replace('/^www\./', '', $name);
					$this->siteList[]  = array(
						'href' => $href,
						'name' => $refinedName,
						'id'   => array_pop($pathPart),
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
