<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontController extends AbstractController
{


    /**
     * @Route("/front", name="front")
     */
    public function index(): Response
    {
        return $this->render('front/index.html.twig', [
            'controller_name' => 'FrontController',
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        //Donnée a utiliser en Assert une fois le soucis réglé
        $package = new Package(new EmptyVersionStrategy());
        $img = $package->getUrl('img/img2.jpg');
        $css = $package->getUrl('img/img2.jpg');

        return $this->render('front/home.html.twig', ['img' => $img, 'css' => $css]);
    }
}
