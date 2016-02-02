<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Net;

/**
 * Class CurlTrait
 */
trait CurlTrait
{
	protected $curlOptions = [];

	/**
	 * @param $options
	 * @return $this
	 */
	public function curlOptions($options)
	{
		// http://php.net/manual/ru/function.array-merge.php - Example #2 - numeric keys!
		$this->curlOptions = $options + $this->curlOptions;

		return $this;
	}

	/**
	 * @param $defaultOptions
	 * @return array
	 */
	public function getCurlOptions($defaultOptions)
	{
		return $this->curlOptions + $defaultOptions;
	}
}
