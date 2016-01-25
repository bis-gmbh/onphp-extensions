<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

/**
 * Interface VKAuthStrategyInterface
 */
interface VKAuthStrategyInterface
{
	public function getAuthDialogUrl(VKAuthenticator $authObj);
	public function getToken(VKAuthenticator $authObj, $code);
}
