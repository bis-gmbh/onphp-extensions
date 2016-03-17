<?php
/**
 * onPHP extensions package
 *
 * PBKDF2 (Password-Based Key Derivation Function 2)
 *
 * @author Oleksandr Glushchenko <contact@fluder.co>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

namespace Onphp\Extensions\Security;

class Pbkdf2
{
	const HASH_ITERATIONS = 1000;

	const SALT_ITERATIONS = 10;

	const POMPOUS_SECRET = <<<TOKEN
vT@sw6b7,MD#orY8iQG%CbHLyzeziWFNWGnew=X]QuFfXtc(vP
TOKEN;

	/**
	 * Generate a random salt with plenty of entropy
	 *
	 * @static
	 * @param int $iterationCount Optional. The number of times to run operation (i.e. > 10000 times)
	 * @return string
	 */
	public static function generateRandomSalt($iterationCount = self::SALT_ITERATIONS)
	{
		if ($iterationCount < 10) {
			$iterationCount = 10;
		}

		$rand = array();

		for ($i = 0; $i < $iterationCount; ++$i) {
			$rand[] = rand(0, 2147483647);
		}

		return substr(hash('sha256', implode('', $rand)), 0, 8);
	}

	/**
	 * Does the password match a hash?
	 *
	 * @static
	 * @param string $password Plain-text password to hash using sha256
	 * @param string $hash The sha256 hash to compare to
	 * @param string $salt A consistent, secret random salt for the end-user
	 * @param int $iterationCount Optional. The number of times to run operation (i.e. > 10000 times)
	 * @return bool Matches.
	 */
	public static function isMatch($password, $hash, $salt,
		$iterationCount = self::HASH_ITERATIONS
	)
	{
		$hashExpected = self::hash($password, $salt, $iterationCount);

		return $hashExpected === $hash;
	}

	/**
	 * Hash a plain-text password, strengthening it to brute force
	 *
	 * @static
	 * @param string $password Plain-text password to hash using sha256
	 * @param string $salt A consistent, secret random salt for the end-user
	 * @param int $iterationCount Optional. The number of times to run the operation (i.e. > 10000 times)
	 * @param string $secret Optional. A secret, known only to the application. This helps to add entropy.
	 * @return string
	 */
	public static function hash($password, $salt, $iterationCount = self::HASH_ITERATIONS,
		$secret = self::POMPOUS_SECRET
	) {
		$hash = $password;

		for ($i = 0; $i < $iterationCount; ++$i) {
			$hash = substr(strtolower(hash('sha256', $secret . $hash . $salt)), 0, 32);
		}

		return $hash;
	}
}
