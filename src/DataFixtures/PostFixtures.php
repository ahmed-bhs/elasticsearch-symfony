<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class PostFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i< 5 ; $i ++)
        {
            $category = (new  Category())->setName('bala'.$i);
            $manager->persist($category);
        }
        for ($i = 1; $i < 5; $i++) {

            $category = (new  Category())->setName('category'.$i);
            $product = (new Post())->setDescription(sprintf('test%s', $i))->setTitle(sprintf('test%s', $i))->setCategory($category);

           if($i % 2 == 0) $product2 = (new Post())->setDescription(sprintf('test2%s', $i))->setTitle(sprintf('test2%s', $i))->setCategory($category);


            $manager->persist($category);
            $manager->persist($product);
            if($i % 2 == 0) $manager->persist($product2);

        }
        $manager->flush();
    }
}
