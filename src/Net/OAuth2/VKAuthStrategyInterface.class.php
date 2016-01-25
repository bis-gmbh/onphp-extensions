<?php
/**
 * textreporter.ru (https://textreporter.ru/)
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2015 Barzmann Internet Solutions GmbH
 */

/**
 * Interface VKAuthStrategyInterface
 */
interface VKAuthStrategyInterface
{
	public function getAuthDialogUrl(VKAuthenticator $authObj);
	public function getToken(VKAuthenticator $authObj, $code);
}
