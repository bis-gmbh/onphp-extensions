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
    public function testGenerateRandomSalt()
    {
        $isEqualsSalt = Pbkdf2::generateRandomSalt() == Pbkdf2::generateRandomSalt();

        $this->assertEquals(false, $isEqualsSalt);
    }
}
