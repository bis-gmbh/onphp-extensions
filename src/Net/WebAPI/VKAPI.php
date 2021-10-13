<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net\WebAPI;

use \Onphp\Extensions\Net\CurlTrait;

/**
 * Class VKAPI
 */
class VKAPI
{
	use CurlTrait;

	const VERSION = '5.131';

	public $userAgent = 'Opera/9.80 (X11; Linux i686; U; ru) Presto/2.8.131 Version/11.10';

	protected $accessToken = null;

	/**
	 * @param string|null $accessToken
	 */
	public function __construct($accessToken = null)
	{
		if ($accessToken) {
			$this->setAccessToken($accessToken);
		}
	}

	public function setAccessToken($accessToken)
	{
		$this->accessToken = $accessToken;
	}

	/**
	 * @param string $method
	 * @param array $params
	 * @return bool|mixed
	 */
	public function invoke($method, $params)
	{
		$params['v'] = self::VERSION;
		$params['access_token'] = $this->accessToken;
		$url = 'https://api.vk.com/method/' . $method;

		$curlOptions = [
			CURLOPT_HEADER          => 0,
			CURLOPT_URL             => $url,
			CURLOPT_CONNECTTIMEOUT  => 5,
			CURLOPT_FRESH_CONNECT   => 1,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_FORBID_REUSE    => 1,
			CURLOPT_TIMEOUT         => 5,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_POST            => true,
			CURLOPT_POSTFIELDS      => http_build_query($params),
			CURLOPT_USERAGENT       => $this->userAgent,
		];

		return $this->curlRequest($curlOptions);
	}

	/**
	 * @param $uploadUrl
	 * @param array $imagePaths with elements of image file path
	 * @return array
	 */
	public function uploadPhotos($uploadUrl, $imagePaths)
	{
		$photos = [];
		$fileIndex = 1;
		foreach ($imagePaths as $imagePath) {
			$photos['file' . $fileIndex] =
				(class_exists('CURLFile', false)) ?
					new \CURLFile(realpath($imagePath)) :
					'@' . realpath($imagePath);
			$fileIndex++;
		}

		$curlOptions = [
			CURLOPT_HEADER          => 0,
			CURLOPT_URL             => $uploadUrl,
			CURLOPT_USERAGENT       => $this->userAgent,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_POST            => true,
			CURLOPT_POSTFIELDS      => $photos,
		];

		return $this->curlRequest($curlOptions);
	}

	/**
	 * @param array $curlOptions
	 * @return array
	 */
	protected function curlRequest($curlOptions)
	{
		$ch = curl_init();
		curl_setopt_array($ch, $this->getCurlOptions($curlOptions));
		$response = curl_exec($ch);
		$info     = curl_getinfo($ch);
		$response = json_decode($response, true);
		if (empty($response)) {
			$response = [
				'error' => [
					'error_msg' => 'Unknown error, HTTP code: ' . $info['http_code'] . '.',
					'error_code' => $info['http_code']
				]
			];
		}
		return $response;
	}
}
