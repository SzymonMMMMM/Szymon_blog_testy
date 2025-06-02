<?php
/**
 * TodoItem fixtures.
 */

namespace App\DataFixtures;

use App\Entity\TodoItem;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class TodoItemFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class TodoItemFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        $this->createMany(100, 'todoitem', function () {
            $todoItem = new TodoItem();
            $todoItem->setTitle($this->faker->sentence);
            $todoItem->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $todoItem->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $completedPercentage = $this->faker->boolean(70);
            $todoItem->setCompleted($completedPercentage);

            /** @var User $author */
            $author = $this->getRandomReference('users');
            $todoItem->setAuthor($author);

            return $todoItem;
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
