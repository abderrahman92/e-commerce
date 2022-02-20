<?php

namespace App\Controller;
use App\Entity\Panier;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use App\Form\PanierType;
use App\Entity\ContenuPanier;
use App\Form\ContenuPanierType;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/contenu/panier')]
class ContenuPanierController extends AbstractController
{
    #[Route('/', name: 'contenu_panier_index', methods: ['GET'])]
    public function index(ContenuPanierRepository $contenuPanierRepository,PanierRepository $panierRepository,EntityManagerInterface $entityManager): Response{
        $user= $this->getUser();
       
       
        $panier= $panierRepository->findBy(['etat' => '0','utilisateur' => $user]);

        return $this->render('contenu_panier/index.html.twig', [
            'contenu_paniers' => $contenuPanierRepository->findBy(['panier'=>$panier],),
            'id' => $contenuPanierRepository->findOneBy(['panier'=>$panier],),
        ]);
    }

    #[Route('/{id}', name: 'contenu_panier_show', methods: ['GET', 'POST'])]
    public function show(Request $request,ContenuPanier $contenuPanier,EntityManagerInterface $entityManager): Response
    {
        
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('contenu_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contenu_panier/edit.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'contenu_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ContenuPanier $contenuPanier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('contenu_panier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('contenu_panier/edit.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'contenu_panier_delete', methods: ['POST'])]
    public function delete(Request $request, ContenuPanier $contenuPanier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contenuPanier->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contenuPanier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('contenu_panier_index', [], Response::HTTP_SEE_OTHER);
    }
}
