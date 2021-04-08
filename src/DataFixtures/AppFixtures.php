<?php

/**
 *
 * Pour installer le bundle de fixture ET faker
 * composer require orm-fixtures --dev
 * composer require fakerphp/faker
 *
 * Pour exécuter ces fixtures :
 * php bin/console doctrine:fixtures:load
 *
 */


namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    //on se fait passer l'encodeur de mot de passe dans le contructeur
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    //cette méthode est appelée quand on exécute la commande php bin/console d:f:l
    //on reçoit un entitymanager en argument \o/
    public function load(ObjectManager $manager)
    {
        //charge un fichier sql et exécute les requêtes qui s'y trouvent
        $sql = file_get_contents(__DIR__  . '/city.sql');
        $stmt = $manager->getConnection()->prepare($sql);
        $stmt->execute();
        //nécessaire sinon ça pète une erreur
        $stmt->closeCursor();

        //nous permet de générer des données bidons
        //voir ici pour tout ce qu'on peut générer :
        //https://fakerphp.github.io/formatters/numbers-and-strings/
        $faker = \Faker\Factory::create("fr_FR");

        //on crée un user normal fixe
        $user = new User();
        $user->setEmail('yo@yo.com');
        $user->setPassword($this->encoder->encodePassword($user, 'yoyoyo'));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);

        //on crée un admin
        $user = new User();
        $user->setEmail('admin@yo.com');
        $user->setPassword($this->encoder->encodePassword($user, 'admin'));
        $user->setRoles(['ROLE_ADMIN']);
        $manager->persist($user);

        //on sauvegarde en bdd tout de suite
        $manager->flush();

        //on récupère tous les users pour pouvoir les associer en tant qu'organisateur d'événement ci-dessous
        $allUsers = $manager->getRepository(User::class)->findAll();

        //plein de création d'événements
        for($i = 0; $i < 100; $i++) {

            $event = new Event();
            $event->setName( $faker->sentence );
            $event->setDescription( $faker->realText(1000) );
            $event->setMaxRegistrations(mt_rand(10, 100));
            $event->setStartDate( $faker->dateTimeBetween('- 6 months', 'now') );

            //un utilisateur au hasard en tant qu'organisateur
            $event->setOrganizer( $faker->randomElement($allUsers) );

            //on sauvegarde dans la boucle
            $manager->persist($event);
        }

        //et on flush après la boucle (plus rapide)
        $manager->flush();

    }
}
