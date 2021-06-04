<?php

namespace App\Controller;

use App\Entity\Posts;
use App\Form\PostsType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostsController extends AbstractController
{
    /**
     * @Route("/posts/new", name="newPost")
     */
    public function create(Request $request, SluggerInterface $slugger)
    {
        $post = new Posts();
        $form = $this->createForm(PostsType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->getUser();
            $post->setUser($user);
            $image = $form['image']->getData();

            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'imagename' property to store the PDF file name
                // instead of its contents
                $post->setImage($newFilename);
            }


            $em->persist($post);
            $em->flush();

            $this->addFlash('success', Posts::SUCCESSFUL_CREATED);

            return $this->redirectToRoute('home');
        }
        return $this->render(
            'posts/index.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/myposts", name="myPosts")
     */
    public function myPosts()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $posts = $em->getRepository(Posts::class)->findBy(['user' => $user]);

        return $this->render(
            'posts/myposts.html.twig',
            [
                'posts' => $posts,
            ]
        );
    }

    /**
     * @Route("/posts/{id}", name="viewPost")
     */
    public function view($id)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Posts::class)->find($id);

        return $this->render(
            'posts/view.html.twig',
            [
                'post' => $post,
            ]
        );
    }
}
