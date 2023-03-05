<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(SluggerInterface $slugger, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            


     
            // do anything else you need here, like send an email

            //return $this->redirectToRoute('_profiler_home');

           // $FotoFile = $form->get('noticiafoto')->getData();
            $FotoFile = $form->get('foto')->getData();
            if($FotoFile){

                $originalFilename = pathinfo($FotoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.  $FotoFile ->guessExtension();
               // $newFilename="1.jpg";
                try {
                    //$peli->setPoster($newFilename);

                    $user->setFoto($newFilename);
                    $FotoFile->move(
                        //aquí va el nombre de parámetro
                      
                    $this->getParameter('images_path'),
                    $newFilename
                );
                    //
                 
                    
              } catch (FileException $e) {
                  // ... handle exception if something happens during file upload
              }
              $entityManager->persist($user);
              $entityManager->flush();
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
