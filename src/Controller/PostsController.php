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

		if ($form->isSubmitted() && $form->isValid())
		{
			$em   = $this->getDoctrine()->getManager();
			$user = $this->getUser();
			$post->setUser($user);
			$image = $form['image']->getData();

			if ($image)
			{
				$file_name = $this->uploadImage($image, $slugger);
				$post->setImage($file_name);
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
	 * @Route("/posts/{id}/edit", name="posts_edit", methods={"GET","POST"})
	 */
	public function edit(Request $request, Posts $post, SluggerInterface $slugger): Response
	{
		$form = $this->createForm(PostsType::class, $post);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$image = $form['image']->getData();

			if ($image)
			{
				$file_name = $this->uploadImage($image, $slugger);
				$post->setImage($file_name);
			}
			$this->getDoctrine()->getManager()->flush();
			$this->addFlash('success', Posts::SUCCESSFUL_EDITED);

			return $this->redirectToRoute('myPosts');
		}

		return $this->render('posts/edit.html.twig', [
			'post' => $post,
			'form' => $form->createView(),
		]);
	}

	/**
	 * @Route("/myposts", name="myPosts")
	 */
	public function myPosts()
	{
		$em    = $this->getDoctrine()->getManager();
		$user  = $this->getUser();
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
		$em   = $this->getDoctrine()->getManager();
		$post = $em->getRepository(Posts::class)->find($id);

		return $this->render(
			'posts/view.html.twig',
			[
				'post' => $post,
			]
		);
	}

	public function uploadImage($image, $slugger)
	{
		$originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
		// this is needed to safely include the file name as part of the URL
		$safeFilename = $slugger->slug($originalFilename);
		$newFilename  = $safeFilename . '-' . uniqid() . '.' . $image->guessExtension();

		try
		{
			$image->move(
				$this->getParameter('images_directory'),
				$newFilename
			);
		}
		catch (FileException $e)
		{
		}

		return $newFilename;
	}
}
