<?php

namespace App\Controller;

use App\Entity\Noticia;
use App\Form\NoticiaType;
use App\Repository\NoticiaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/noticia')]
class NoticiaController extends AbstractController
{
    #[Route('/', name: 'app_noticia_index', methods: ['GET'])]
    public function index(NoticiaRepository $noticiaRepository): Response
    {
        return $this->render('noticia/index.html.twig', [
            'noticias' => $noticiaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_noticia_new', methods: ['GET', 'POST'])]
    public function new(SluggerInterface $slugger,Request $request, NoticiaRepository $noticiaRepository): Response
    {
        $noticium = new Noticia();
        $form = $this->createForm(NoticiaType::class, $noticium);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {
            $noticium->setAutor($this->getUser());
            $FotoFile = $form->get('noticiafoto')->getData();
            if($FotoFile){

                $originalFilename = pathinfo($FotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'. $FotoFile ->guessExtension();
           
                try {
                    //$peli->setPoster($newFilename);

                    $noticium->setNoticiafoto($newFilename);
                    $FotoFile->move(
                        //aquí va el nombre de parámetro
                      
                    $this->getParameter('images_path'),
                    $newFilename
                );
                    //
                 
                    
              } catch (FileException $e) {
                  // ... handle exception if something happens during file upload
              }
           

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
              //  $product->setBrochureFilename($newFilename);

            }

             $noticium->setNoticiafoto($newFilename);

           
            $noticiaRepository->save($noticium, true);

            return $this->redirectToRoute('app_noticia_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('noticia/new.html.twig', [
            'noticium' => $noticium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_noticia_show', methods: ['GET'])]
    public function show(Noticia $noticium): Response
    {
        return $this->render('noticia/show.html.twig', [
            'noticium' => $noticium,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_noticia_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Noticia $noticium, NoticiaRepository $noticiaRepository): Response
    {
        $form = $this->createForm(NoticiaType::class, $noticium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $noticiaRepository->save($noticium, true);

            return $this->redirectToRoute('app_noticia_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('noticia/edit.html.twig', [
            'noticium' => $noticium,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_noticia_delete', methods: ['POST'])]
    public function delete(Request $request, Noticia $noticium, NoticiaRepository $noticiaRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$noticium->getId(), $request->request->get('_token'))) {
            $noticiaRepository->remove($noticium, true);
        }

        return $this->redirectToRoute('app_noticia_index', [], Response::HTTP_SEE_OTHER);
    }
}
