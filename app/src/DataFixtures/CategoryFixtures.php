<?php

/**
 * Category fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class CategoryFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class CategoryFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }

        $this->createMany(100, 'categories', function () {
            $category = new Category();
            $category->setTitle($this->faker->unique()->word);

            /** @var User $author */
            $author = $this->getRandomReference('users');
            $category->setAuthor($author);

            return $category;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
