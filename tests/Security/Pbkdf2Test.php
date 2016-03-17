<?php
/**
 * onPHP extensions package
 *
 * @author Oleksandr Glushchenko <contact@fluder.co>
 * @copyright 2014-2016 Barzmann Internet Solutions GmbH
 */

use \Onphp\Extensions\Security\Pbkdf2;

class Pbkdf2Test extends PHPUnit_Framework_TestCase
{
    const PASSWORD = '321456';
    const SECRET = 'secret';
    const SALT = 'salt';
    const ITERATIONS = 1000;

    public function testGenerateRandomSalt()
    {
        $isEqualsSalt = Pbkdf2::generateRandomSalt() == Pbkdf2::generateRandomSalt();

        $this->assertEquals(false, $isEqualsSalt);
    }

    public function testIsMatch()
    {
        $isMatch = Pbkdf2::isMatch(
            self::PASSWORD,
            'd6c71a7d24bf0fb232f0d1404e25136e',
            self::SALT,
            self::ITERATIONS,
            self::SECRET
        );

        $this->assertEquals(true, $isMatch);
    }

    public function testHash()
    {
        $hash = Pbkdf2::hash(self::PASSWORD, self::SALT, self::ITERATIONS, self::SECRET);

        $this->assertEquals('d6c71a7d24bf0fb232f0d1404e25136e', $hash);
    }
}
