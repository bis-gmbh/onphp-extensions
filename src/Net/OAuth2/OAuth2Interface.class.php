<?php
/**
 * Onphp Extensions Package
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

/**
 * Interface OAuth2Interface
 */
interface OAuth2Interface
{
	public function setRedirectUri($uri);
	public function getAuthDialogUrl();
	public function getToken($code);
}
