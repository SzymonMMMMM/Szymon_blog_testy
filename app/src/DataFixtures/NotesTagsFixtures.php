<?php
/**
 * Note_tags fixtures.
 */

namespace App\DataFixtures;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;

/**
 * Class NotesTagsFixtures.
 */
class NotesTagsFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load Data.
     */
    public function loadData(): void
    {
        if (null === $this->manager || null === $this->faker) {
            return;
        }
        $this->createMany(300, 'notes_tags', function () {
            $note = $this->getRandomReference('notes');
            $tag = $this->getRandomReference('tags');

            $note->addTag($tag);

            return $note;
        });
        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] Array of dependencies
     *
     * @psalm-return array{0: NoteFixtures::class, 1: TagFixtures::class}
     */
    public function getDependencies(): array
    {
        return [
            NoteFixtures::class,
            TagFixtures::class,
        ];
    }
}
