<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Repository\ChambreRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Form\ChambreType;

class AppController extends AbstractController
{

    private $commandeRepository;

    public function __construct(CommandeRepository $commandeRepository)
    {
        $this->commandeRepository = $commandeRepository;
    }

    #[Route('/mon-compte/commandes', name: 'mon_compte_commandes')]
    public function commandes(CommandeRepository $commandeRepository): Response
    {
        $commandes = $commandeRepository->findAll();
    
        return $this->render('cart/commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }


    #[Route('/about', name: 'about')]
    public function about(): Response
    {
        return $this->render('app/about.html.twig');
    }


    #[Route('/', name: 'home')]
    public function index(): Response
    {
        return $this->render('app/index.html.twig');
    }

    #[Route('/chambre', name: 'app_chambre')]
    public function produit(ChambreRepository $repo): Response
    {
        $chambres = $repo->findAll();
        return $this->render('app/chambre.html.twig', [
            'lesChambres' => $chambres
        ]);
    }

    #[Route("/chambre/show/{id}", name: "chambre_show")]
    public function show(Chambre $chambre =null) :Response
    {
        if($chambre == null)
        {
            return $this->redirectToRoute('home');
        }
        return $this->render('app/chambre_show.html.twig', [
            'chambre' => $chambre
        ]);
    }

    #[Route('/chambre/add', name: 'chambre_add')]
    public function ajouter(EntityManagerInterface $manager, Request $request, SluggerInterface $slugger): Response
    {
        $chambre = new Chambre;

        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('photo_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                  
                }

                
                $chambre->setPhoto($newFilename);
            } 
                $manager->persist($chambre);
                $manager->flush();
                return $this->redirectToRoute('app_chambre');
           
       
        }

        return $this->render('app/chambre.html.twig', [
            'form' => $form,
        ]);
    }

}
