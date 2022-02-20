<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;

use App\Entity\Panier;

use App\Entity\ContenuPanier;
use App\Form\ContenuPanierType;
use App\Repository\ContenuPanierRepository;
use App\Repository\PanierRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

#[Route('/')]
class ProduitController extends AbstractController
{
    #[Route('/', name: 'produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('produit/new', name: 'produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photo) {
               
                $newFilename = uniqid().'.'.$photo->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photo->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('danger',"impossible d'uploader l'image");
                    return $this->redirectToRoute('produit_new');
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $produit->setPhoto($newFilename);
            }
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }





    #[Route('produit/{id}', name: 'produit_show', methods: ['GET','POST'])]
    public function show(int $id,Request $request,EntityManagerInterface $entityManager,Produit $produit, ProduitRepository $produitRepository,ContenuPanierRepository $ContenuPanierRepository,PanierRepository $panierRepository): Response
    {

        $user= $this->getUser();
        //rechercher l'utilisateur et l'etat dans le panier 
        $valide= $panierRepository->findOneBy([
            'etat' => '0',
            'utilisateur' => $user,]);
            //si il n'existe pas 
            if (($valide == null)){
                //creation du panier 
                $panier = new Panier();
                $panier->setDateAchat(new \DateTime()); 
                $panier->setUtilisateur($this->getUser());
                $panier->setEtat('0'); 
                $entityManager->persist($panier);
                $entityManager->flush();
            }

        //
        //recuperation id du produit  de la route 
        $post=$produitRepository->find($id);
        $contenuPanier = new ContenuPanier();
        $contenuPanier->setDate(new \DateTime()); 
        $contenuPanier->setQuantite('20');  
        $contenuPanier->setProduit($post);
        $post2= $panierRepository->findOneBy(['etat' => '0','utilisateur' => $user]);
        $contenuPanier->setPanier($post2);
        

            
        $form = $this->createForm(ContenuPanierType::class, $contenuPanier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contenuPanier);
            $entityManager->flush();
            
            return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/show.html.twig', [
            'contenu_panier' => $contenuPanier,
            'form' => $form,
            'produit' => $produit,
        ]);
       
       
        
    }






    #[Route('produit/{id}/edit', name: 'produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager,): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }



    #[Route('/delete/{id}', name: 'produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('produit_index', [], Response::HTTP_SEE_OTHER);
    }
}
