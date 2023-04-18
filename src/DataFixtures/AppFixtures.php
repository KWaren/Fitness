<?php

namespace App\DataFixtures;

use App\Entity\Abonnee;
use App\Entity\Agent;
use App\Entity\Author;
use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;
    
    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $listUser = []; 
        $faker = Factory::create('fr_FR');
     
        // Création d'un user "normal"
        for ($i = 0; $i < 10; $i++) {
               // Générer un entier aléatoire
        $randomInt = $faker->numberBetween($min = 7777, $max = 100000000);

         // Générer une adresse email aléatoire
         $randomEmail = $faker->email();

         // Générer une chaîne de caractères aléatoire
         $randomString = $faker->word();

          // Générer une date aléatoire
        $randomDate = $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now', $timezone = null);
            $user = new Agent();
            $user->setSurname($randomString);
            $user->setName($randomString);
            $user->setEmail($randomEmail);
            $user->setRoles(["ROLE_USER"]);
            $user->setPassword(password_hash('secret', PASSWORD_BCRYPT));
            $manager->persist($user);
            $listUser[] = $user;
        }
        
        // Création d'un user admin
        $userAdmin = new Agent();
        $userAdmin->setSurname("Admin");
        $userAdmin->setName("Admin");
        $userAdmin->setEmail("admin@bookapi.com");
        $userAdmin->setRoles(["ROLE_ADMIN"]);
        $userAdmin->setPassword($this->userPasswordHasher->hashPassword($userAdmin, "password"));
        $manager->persist($userAdmin);

		// Création des auteurs.
        $listAuthor = [];           
        for ($i = 0; $i < 10; $i++) {
            // Création de l'auteur lui même. 
            $author = new Author();
            $author->setFirstName("Prénom " . $i);
            $author->setLastName("Nom " . $i);
            $manager->persist($author);
            // On sauvegarde l'auteur créé dans un tableau. 
            $listAuthor[] = $author;
        }

        for ($i=0; $i < 20; $i++) { 
            $book = new Book();
            $book->setTitle("Titre " . $i);
            $book->setCoverText("Quatrième de couverture numéro : " . $i);
            $book->setAuthor($listAuthor[array_rand($listAuthor)]);
            $manager->persist($book);
        }
        for ($i=0; $i < 20; $i++) { 
       // Générer un entier aléatoire
        $randomInt = $faker->numberBetween($min = 7777, $max = 100000000);

        // Générer une adresse email aléatoire
        $randomEmail = $faker->email();

        // Générer une chaîne de caractères aléatoire
        $randomString = $faker->word();

         // Générer une date aléatoire
       $randomDate = $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now', $timezone = null);
            $Abonnee = new Abonnee();
            $Abonnee->setName($faker->word());
            $Abonnee->setSurname($faker->word());
            $Abonnee->setNum($randomInt);
            $Abonnee->setEmail($randomEmail);
            $Abonnee->setAgent($listUser[array_rand($listUser)]);
            $manager->persist($Abonnee);
        }
        $manager->flush();
    }
}
