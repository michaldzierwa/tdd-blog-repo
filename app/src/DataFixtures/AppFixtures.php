<?php
/**
 * App fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * App Fixtures.
 */
class AppFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager Object Manager
     *
     * @return void Void
     */
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
