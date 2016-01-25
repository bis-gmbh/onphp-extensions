<?php
/**
 * textreporter.ru (https://textreporter.ru/)
 * 
 * @author Dmitry Nezhelskoy <dmitry@nezhelskoy.pro>
 * @copyright 2014-2015 Barzmann Internet Solutions GmbH
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
