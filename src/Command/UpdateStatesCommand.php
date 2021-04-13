<?php

/**
 * La base de cette commande a été générée grâce à php bin/console make:command (dans le terminal)
 * Elle sera exécutée automatiquement, à chaque minute de la vie, par un CRON job sur le serveur de prod
 * Ou par des tâches planifiées sur Windows
 * Son objectif est de tenir à jour les états des sorties
 */

namespace App\Command;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateStatesCommand extends Command
{
    //la commande à taper dans le terminal après php bin/console
    protected static $defaultName = 'app:update-states';

    //la description qui s'affiche quand on affiche la liste des commandes disponibles
    protected static $defaultDescription = "Met à jour l'état des sorties";

    /**
     * contient l'entity manager, qui permet de faire les requêtes à la bdd
     * @var EntityManagerInterface
     */
    private $entityManager;

    //on se fait injecter les autres services ici !
    public function __construct(string $name = null, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->entityManager = $entityManager;
    }

    //méthode obligatoire, qui permet de donner la description à la commande
    protected function configure()
    {
        $this->setDescription(self::$defaultDescription);
    }

    //méthode qui est appelée par Symfony quand on tape la commande dans le terminal
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        //ce $io permet d'afficher des choses dans le terminal, de poser des questions, etc...
        $io = new SymfonyStyle($input, $output);

        //récupère tous les événements
        $eventRepository = $this->entityManager->getRepository(Event::class);
        $allEvents = $eventRepository->findAll();

        //permet d'afficher une barre de progression
        $io->progressStart();

        //on boucle sur les événements pour les mettre à jour au besoin
        /** @var Event $event */
        foreach($allEvents as $event){
            if ($event->getStartDate() > new \DateTime()){
                $event->setState("CLOSED");
                //écrit une ligne dans le terminal, pour donner des infos...
                $io->writeln("État changé pour l'événement " . $event->getId());
            }
            //fait avancer la barre de progression
            $io->progressAdvance();
        }

        //on spécifie que la tâche est terminée pour la barre de progression
        $io->progressFinish();

        //on sauvegarde les événements
        $this->entityManager->flush();

        //affiche une ligne
        $io->writeln('coucou');

        //gros message vert
        $io->success("C'est bon c'est à jour !");

        //obligatoire à la fin
        return Command::SUCCESS;
    }
}
