<?php

namespace App\Controller;

use App\Repository\CityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    /**
     * @Route("/demo", name="demo")
     */
    public function index(): Response
    {
        return $this->render('demo/index.html.twig', []);
    }

    /**
     * @Route("/demo/villes/recherche", name="demo_city_search")
     */
    public function citySearch(CityRepository $cityRepository, Request $request): Response
    {
        $keyword = $request->query->get('keyword');
        $results = $cityRepository->searchCity($keyword);
        return $this->render("demo/ajax_cities.html.twig", [
            "cities" => $results
        ]);
    }
}
