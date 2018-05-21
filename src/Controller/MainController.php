<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Note;
use App\Form\ImageType;
use App\Form\NoteType;
use App\Repository\ImageRepository;
use App\Repository\NoteRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Filesystem;

class MainController extends Controller
{
    /**
     * @Route("/", name="home")
     */
    public function index(NoteRepository $note)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $notes = $note->findBy(['user'=>$this->getUser()]);
        
        return $this->render('base.html.twig',['notes' => $notes]);
    }

    /**
     * @Route("/add_note" , name="add_note")
     */
    public function addNote(Request $request)
    {
        $notes = new Note();
        $form = $this->createForm(NoteType::class, $notes);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $notes->setUser($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($notes);
            $em->flush();

            return $this->redirectToRoute('home');
        }
        return $this->render('/main/index.html.twig',['form'=>$form->createView(), 'notes'=>$notes]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function editNote(Request $request, Note $note)
    {
       // $note = $this->getDoctrine()->getRepository(Note::class)->find($id);
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $em= $this->getDoctrine()->getManager();
            $em->persist($note);
            $em->flush();

            return $this->redirectToRoute('home');
        }
        return $this->render('/main/index.html.twig',['form'=>$form->createView(),'notes'=>$note]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function deleteNote(Note $note)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($note);
        $em->flush();

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/add_image", name="add_image")
     */
    public function addImage(Request $request, FileUploader $fileUploader)
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);
        if( $form->isSubmitted() && $form->isValid())
        {
            /**
             * @var UploadedFile $file
             */
            $file = $image->getImage();
            $imageName = $fileUploader->upload($file);
            $image->setImage($imageName);
            $em =$this->getDoctrine()->getManager();
            $em->persist($image);
            $em->flush();

            return $this->redirectToRoute('gallery');
        }
        return $this->render('main/image.html.twig',['form'=> $form->createView(    )]);
    }

    /**
     * @Route("/delete_image/{id}", name="delete_image")
     */
    public function deleteImage($id)
    {
        $image= $this->getDoctrine()->getRepository(Image::class)->find($id);
        $img = $image->getImage();
        $path = $this->getParameter('image_directory').'/'.$img;
        $fs = new Filesystem();
        $fs->remove($path);
        $em = $this->getDoctrine()->getManager();
        $em->remove($image);
        $em->flush();

        return $this->redirectToRoute('gallery');
    }

    /**
     * @Route("/gallery", name="gallery")
     */
    public function gallery(ImageRepository $imageRepository)
    {
        $images = $imageRepository->findAll();
        return $this->render('main/gallery.html.twig', ['images'=> $images]);
    }
}
