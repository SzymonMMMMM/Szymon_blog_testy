<?php
/**
 * Post_tags fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class PostsTagsFixtures.
 */
class PostsTagsFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load Data.
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(300, 'posts_tags', function () {
            $post = $this->getRandomReference('posts');
            $tag = $this->getRandomReference('tags');

            $post->addTag($tag);

            return $post;
        });
        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] Array of dependencies
     *
     * @psalm-return array{0: PostFixtures::class, 1: TagFixtures::class}
     */
    public function getDependencies(): array
    {
        return [
            PostFixtures::class,
            TagFixtures::class,
        ];
    }
}
