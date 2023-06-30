<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Service\CartService;
use App\Repository\ChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    private $cartService;
    private $entityManager;

    public function __construct(CartService $cartService, EntityManagerInterface $entityManager)
    {
        $this->cartService = $cartService;
        $this->entityManager = $entityManager;
    }

    #[Route('/cart', name: 'app_cart')]
    public function index(ChambreRepository $repo, CartService $cs): Response
    {
        $cartWithdata = $cs->cartWithData();
        

        $montant = $cs->montant();
       
    

        foreach ($cartWithdata as &$item) {
            $chambre = $item['chambre'];
    
        }
    
        return $this->render('cart/index.html.twig', [
            'items' => $cartWithdata,
            'montant' => $montant
        ]);
    }

    #[Route('/cart/add/{id}', name:'cart_add')]
    public function add($id, CartService $cs)
    {
        $this->cartService->add($id);
        
        // dd($session->get('cart'));
        return $this->redirectToRoute('app_chambre');

       
    }

    #[Route('/cart/remove/{id}', name:'cart_remove')]
    public function remove($id, CartService $cs) : Response
    {
        $cs->remove($id);
        return $this->redirectToRoute('app_cart');
    }


    #[Route('/cart/validation', name: 'validation_commande')]
    public function cartCommande(EntityManagerInterface $manager, Request $request, Commande $commande = null, CartService $cs, ChambreRepository $repo): Response {
        $cartWithData = $cs->cartWithData();
        $total = $cs->montant();
        $errors = [];
    
        foreach ($cartWithData as $item) {
            $chambre = $repo->find($item['chambre']->getId());
            $quantiteCommandee = $item['quantity'];
    
    
            $montant = $chambre->getPrixJournalier() * $quantiteCommandee;
    
            $commande = new Commande();
            $commande
                ->setChambre($chambre)
                ->setQuantity($quantiteCommandee)
                ->setMontant($montant)
                ->setPrixTotal($montant)
                ->setDateArrivee(new \DateTime())
                ->setDateDepart(new \DateTime())
                ->setDateEnregistrement(new \DateTime());
    
            $manager->persist($chambre);
            $manager->persist($commande);
            }
    
            $manager->flush();
    
            $cs->clearCart();
    
            return $this->redirectToRoute('mon_compte_commandes');
        }

}