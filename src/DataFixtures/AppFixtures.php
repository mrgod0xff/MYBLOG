<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $blogPost = new BlogPost();
        $blogPost->setTitle('Mon premier post');
        $blogPost->setPublished(new \DateTime('2020-03-16 10:15:21'));
        $blogPost->setContent('ceci est un test');
        $blogPost->setAuthor('Auguinard kouame');
        $blogPost->setSlug('mon-premier-post');

        $manager->persist($blogPost);

        $blogPost = new BlogPost();
        $blogPost->setTitle('Mon deuxième post');
        $blogPost->setPublished(new \DateTime('2020-03-16 10:18:10'));
        $blogPost->setContent('ceci est un deuxième test');
        $blogPost->setAuthor('Shelly Aka');
        $blogPost->setSlug('mon-deuxieme-post');

        $manager->persist($blogPost);

        $manager->flush();
    }
}
