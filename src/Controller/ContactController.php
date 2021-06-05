<?php

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
	/**
	 * @Route("/contact", name="contact")
	 */
	public function create(Request $request): Response
	{
		$contact = new Contact();
		$form    = $this->createForm(ContactType::class, $contact);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			$em = $this->getDoctrine()->getManager();
			$em->persist($contact);
			$em->flush();
			$this->addFlash('success', Contact::SUCCESSFUL_CREATED);

			return $this->redirectToRoute('contact');
		}

		return $this->render('contact/index.html.twig', [
			'form' => $form->createView(),
		]);
	}
}
