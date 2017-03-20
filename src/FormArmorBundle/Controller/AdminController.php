<?php

namespace FormArmorBundle\Controller;

use FormArmorBundle\Form\ClientType;
use FormArmorBundle\Form\ClientCompletType;
use FormArmorBundle\Form\StatutType;
use FormArmorBundle\Form\FormationType;
use FormArmorBundle\Form\SessionType;
use FormArmorBundle\Form\PlanFormationType;

use FormArmorBundle\Entity\Client;
use FormArmorBundle\Entity\Formation;
use FormArmorBundle\Entity\Session_formation;
use FormArmorBundle\Entity\Plan_formation;
use FormArmorBundle\Entity\Statut;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
    public function authentifAction(Request $request) // Affichage du formulaire d'authentification
    {
        
		// Création du formulaire
		$client = new Client();
		$form   = $this->get('form.factory')->create(ClientType::class, $client);
		
		
		// Contrôle du mdp si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// Récupération des données saisies (le nom des controles sont du style nomDuFormulaire[nomDuChamp] (ex. : client[nom] pour le nom) )
				$donneePost = $request->request->get('client');
				$nom = $donneePost['nom'];
				$mdp = $donneePost['password'];
				
				// Controle du nom et du mdp
				$manager = $this->getDoctrine()->getManager();
				$rep = $manager->getRepository('FormArmorBundle:Client');
				$nbClient = $rep->verifMDP($nom, $mdp);
				if ($nbClient > 0)
				{
					return $this->render('FormArmorBundle:Admin:accueil.html.twig');
				}
				$request->getSession()->getFlashBag()->add('connection', 'Login ou mot de passe incorrects');
			}
		}
		
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:connection.html.twig', array('form' => $form->createView()));
    }
	
	// Gestion des statuts
	public function listeStatutAction($page)
	{
		if ($page < 1)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}

		// On peut fixer le nombre de lignes avec la ligne suivante :
		// $nbParPage = 4;
		// Mais bien sûr il est préférable de définir un paramètre dans "app\config\parameters.yml", et d'y accéder comme ceci :
		$nbParPage = $this->container->getParameter('nb_par_page');
		
		
		// On récupère l'objet Paginator
		$manager = $this->getDoctrine()->getManager();
		$rep = $manager->getRepository('FormArmorBundle:Statut');
		$lesStatuts = $rep->listeStatuts($page, $nbParPage);
		
		// On calcule le nombre total de pages grâce au count($lesStatuts) qui retourne le nombre total de statuts
		$nbPages = ceil(count($lesStatuts) / $nbParPage);
		
		// Si la page n'existe pas, on retourne une erreur 404
		if ($page > $nbPages)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}
		
		// On donne toutes les informations nécessaires à la vue
		return $this->render('FormArmorBundle:Admin:statut.html.twig', array(
		  'lesStatuts' => $lesStatuts,
		  'nbPages'     => $nbPages,
		  'page'        => $page,
		));
	}
	public function modifStatutAction($id, Request $request) // Affichage du formulaire de modification d'un statut
    {
        // Récupération du statut d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Statut');
		$statut = $rep->find($id);
		
		// Création du formulaire à partir du statut "récupéré"
		$form   = $this->get('form.factory')->create(StatutType::class, $statut);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				$em->persist($statut);
				$em->flush();
				
				// Réaffichage de la liste des statuts
				$nbParPage = $this->container->getParameter('nb_par_page');
				// On récupère l'objet Paginator
				$lesStatuts = $rep->listeStatuts(1, $nbParPage);
				
				// On calcule le nombre total de pages grâce au count($lesStatuts) qui retourne le nombre total de statuts
				$nbPages = ceil(count($lesStatuts) / $nbParPage);
				
				// On donne toutes les informations nécessaires à la vue
				return $this->render('FormArmorBundle:Admin:statut.html.twig', array(
				  'lesStatuts' => $lesStatuts,
				  'nbPages'     => $nbPages,
				  'page'        => 1,
				));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formStatut.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
    }
	public function suppStatutAction($id, Request $request) // Affichage du formulaire de suppression d'un statut
    {
        // Récupération du statut d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Statut');
		$statut = $rep->find($id);
		
		// Création du formulaire à partir du statut "récupéré"
		$form   = $this->get('form.factory')->create(StatutType::class, $statut);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// Récupération de l'identifiant du statut à supprimer
			$donneePost = $request->request->get('statut');
			//$identif = $donneePost['id'];
			
			// mise à jour de la bdd
			$res = $rep->suppStatut($id);
			$em->persist($statut);
			$em->flush();
				
			// Réaffichage de la liste des statuts
			$nbParPage = $this->container->getParameter('nb_par_page');
			// On récupère l'objet Paginator
			$lesStatuts = $rep->listeStatuts(1, $nbParPage);
				
			// On calcule le nombre total de pages grâce au count($lesFormations) qui retourne le nombre total de formations
			$nbPages = ceil(count($lesStatuts) / $nbParPage);
				
			// On donne toutes les informations nécessaires à la vue
			return $this->render('FormArmorBundle:Admin:statut.html.twig', array(
				'lesStatuts' => $lesStatuts,
				'nbPages'     => $nbPages,
				'page'        => 1,
				));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formStatut.html.twig', array('form' => $form->createView(), 'action' => 'SUPPRESSION'));
    }
	
	// Gestion des clients
	public function listeClientAction($page)
	{
		if ($page < 1)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}

		// On peut fixer le nombre de lignes avec la ligne suivante :
		// $nbParPage = 4;
		// Mais bien sûr il est préférable de définir un paramètre dans "app\config\parameters.yml", et d'y accéder comme ceci :
		$nbParPage = $this->container->getParameter('nb_par_page');
		
		
		// On récupère l'objet Paginator
		$manager = $this->getDoctrine()->getManager();
		$rep = $manager->getRepository('FormArmorBundle:Client');
		$lesClients = $rep->listeClients($page, $nbParPage);
		
		// On calcule le nombre total de pages grâce au count($lesClients) qui retourne le nombre total de clients
		$nbPages = ceil(count($lesClients) / $nbParPage);
		
		// Si la page n'existe pas, on retourne une erreur 404
		if ($page > $nbPages)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}
		
		// On donne toutes les informations nécessaires à la vue
		return $this->render('FormArmorBundle:Admin:client.html.twig', array(
		  'lesClients' => $lesClients,
		  'nbPages'     => $nbPages,
		  'page'        => $page,
		));
	}
	public function modifClientAction($id, Request $request) // Affichage du formulaire de modification d'un statut
    {
        // Récupération du client d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Client');
		$client = $rep->find($id);
		
		// Création du formulaire à partir du client "récupéré"
		$form   = $this->get('form.factory')->create(ClientCompletType::class, $client);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				$em->persist($client);
				$em->flush();
				
				// Réaffichage de la liste des clients
				$nbParPage = $this->container->getParameter('nb_par_page');
				// On récupère l'objet Paginator
				$lesClients = $rep->listeClients(1, $nbParPage);
				
				// On calcule le nombre total de pages grâce au count($lesClients) qui retourne le nombre total de clients
				$nbPages = ceil(count($lesClients) / $nbParPage);
				
				// On donne toutes les informations nécessaires à la vue
				return $this->render('FormArmorBundle:Admin:client.html.twig', array(
				  'lesClients' => $lesClients,
				  'nbPages'     => $nbPages,
				  'page'        => 1,
				));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formClient.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
    }
	public function suppClientAction($id, Request $request) // Affichage du formulaire de suppression d'un client
    {
        // Récupération du client d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Client');
		$client = $rep->find($id);
		
		// Création du formulaire à partir du client "récupéré"
		$form   = $this->get('form.factory')->create(ClientCompletType::class, $client);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// Récupération de l'identifiant du client à supprimer
			$donneePost = $request->request->get('client');
			
			// mise à jour de la bdd
			$res = $rep->suppClient($id);
			$em->persist($client);
			$em->flush();
				
			// Réaffichage de la liste des clients
			$nbParPage = $this->container->getParameter('nb_par_page');
			// On récupère l'objet Paginator
			$lesClients = $rep->listeClients(1, $nbParPage);
				
			// On calcule le nombre total de pages grâce au count($lesClients) qui retourne le nombre total de clients
			$nbPages = ceil(count($lesClients) / $nbParPage);
				
			// On donne toutes les informations nécessaires à la vue
			return $this->render('FormArmorBundle:Admin:client.html.twig', array(
				'lesClients' => $lesClients,
				'nbPages'     => $nbPages,
				'page'        => 1,
				));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formClient.html.twig', array('form' => $form->createView(), 'action' => 'SUPPRESSION'));
    }
	
	// Gestion des formations
	public function listeFormationAction($page)
	{
		if ($page < 1)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}

		// On peut fixer le nombre de lignes avec la ligne suivante :
		// $nbParPage = 4;
		// Mais bien sûr il est préférable de définir un paramètre dans "app\config\parameters.yml", et d'y accéder comme ceci :
		$nbParPage = $this->container->getParameter('nb_par_page');
		
		// On récupère l'objet Paginator
		$manager = $this->getDoctrine()->getManager();
		$rep = $manager->getRepository('FormArmorBundle:Formation');
		$lesFormations = $rep->listeFormations($page, $nbParPage);
		
		// On calcule le nombre total de pages grâce au count($lesFormations) qui retourne le nombre total de formations
		$nbPages = ceil(count($lesFormations) / $nbParPage);
		
		// Si la page n'existe pas, on retourne une erreur 404
		if ($page > $nbPages)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}
		
		// On donne toutes les informations nécessaires à la vue
		return $this->render('FormArmorBundle:Admin:formation.html.twig', array(
		  'lesFormations' => $lesFormations,
		  'nbPages'     => $nbPages,
		  'page'        => $page,
		));
	}
	public function modifFormationAction($id, Request $request) // Affichage du formulaire de modification d'une formation
    {
        // Récupération de la formation d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Formation');
		$formation = $rep->find($id);
		
		// Création du formulaire à partir de la formation "récupérée"
		$form   = $this->get('form.factory')->create(FormationType::class, $formation);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				$em->persist($formation);
				$em->flush();
				
				// Réaffichage de la liste des clients
				$nbParPage = $this->container->getParameter('nb_par_page');
				// On récupère l'objet Paginator
				$lesFormations = $rep->listeFormations(1, $nbParPage);
				
				// On calcule le nombre total de pages grâce au count($lesFormations) qui retourne le nombre total de formations
				$nbPages = ceil(count($lesFormations) / $nbParPage);
				
				// On donne toutes les informations nécessaires à la vue
				return $this->render('FormArmorBundle:Admin:formation.html.twig', array(
				  'lesFormations' => $lesFormations,
				  'nbPages'     => $nbPages,
				  'page'        => 1,
				));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formFormation.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
    }
	public function suppFormationAction($id, Request $request) // Affichage du formulaire de suppression d'une formation
    {
        // Récupération de la formation d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Formation');
		$formation = $rep->find($id);
		
		// Création du formulaire à partir de la formation "récupérée"
		$form   = $this->get('form.factory')->create(FormationType::class, $formation);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// Récupération de l'identifiant de la formation à supprimer
			$donneePost = $request->request->get('formation');
			
			// mise à jour de la bdd
			$res = $rep->suppFormation($id);
			$em->persist($formation);
			$em->flush();
				
			// Réaffichage de la liste des formations
			$nbParPage = $this->container->getParameter('nb_par_page');
			// On récupère l'objet Paginator
			$lesFormations = $rep->listeFormations(1, $nbParPage);
				
			// On calcule le nombre total de pages grâce au count($lesFormations) qui retourne le nombre total de formations
			$nbPages = ceil(count($lesFormations) / $nbParPage);
				
			// On donne toutes les informations nécessaires à la vue
			return $this->render('FormArmorBundle:Admin:formation.html.twig', array(
				'lesFormations' => $lesFormations,
				'nbPages'     => $nbPages,
				'page'        => 1,
				));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formFormation.html.twig', array('form' => $form->createView(), 'action' => 'SUPPRESSION'));
    }
	
	// Gestion des sessions
	public function listeSessionAction($page)
	{
		if ($page < 1)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}

		// On peut fixer le nombre de lignes avec la ligne suivante :
		// $nbParPage = 4;
		// Mais bien sûr il est préférable de définir un paramètre dans "app\config\parameters.yml", et d'y accéder comme ceci :
		$nbParPage = $this->container->getParameter('nb_par_page');
		
		// On récupère l'objet Paginator
		$manager = $this->getDoctrine()->getManager();
		$rep = $manager->getRepository('FormArmorBundle:Session_formation');
		$lesSessions = $rep->listeSessions($page, $nbParPage);
		
		// On calcule le nombre total de pages grâce au count($lesSessions) qui retourne le nombre total de sessions
		$nbPages = ceil(count($lesSessions) / $nbParPage);
		
		// Si la page n'existe pas, on retourne une erreur 404
		if ($page > $nbPages)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}
		
		// On donne toutes les informations nécessaires à la vue
		return $this->render('FormArmorBundle:Admin:session.html.twig', array(
		  'lesSessions' => $lesSessions,
		  'nbPages'     => $nbPages,
		  'page'        => $page,
		));
	}
	public function modifSessionAction($id, Request $request) // Affichage du formulaire de modification d'une session
    {
        // Récupération de la formation d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Session_formation');
		$session = $rep->find($id);
		
		// Création du formulaire à partir de la session "récupérée"
		$form   = $this->get('form.factory')->create(SessionType::class, $session);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				$em->persist($session);
				$em->flush();
				
				// Réaffichage de la liste des sessions
				$nbParPage = $this->container->getParameter('nb_par_page');
				// On récupère l'objet Paginator
				$lesSessions = $rep->listeSessions(1, $nbParPage);
				
				// On calcule le nombre total de pages grâce au count($lesSessions) qui retourne le nombre total de sessions
				$nbPages = ceil(count($lesSessions) / $nbParPage);
				
				// On donne toutes les informations nécessaires à la vue
				return $this->render('FormArmorBundle:Admin:session.html.twig', array(
				  'lesSessions' => $lesSessions,
				  'nbPages'     => $nbPages,
				  'page'        => 1,
				));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formSession.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
    }
	public function suppSessionAction($id, Request $request) // Affichage du formulaire de suppression d'une session
    {
        // Récupération de la session d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Session_formation');
		$session = $rep->find($id);
		
		// Création du formulaire à partir de la session "récupérée"
		$form   = $this->get('form.factory')->create(SessionType::class, $session);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// Récupération de l'identifiant de la session à supprimer
			$donneePost = $request->request->get('session');
			
			// mise à jour de la bdd
			$res = $rep->suppSession($id);
			$em->persist($session);
			$em->flush();
				
			// Réaffichage de la liste des formations
			$nbParPage = $this->container->getParameter('nb_par_page');
			// On récupère l'objet Paginator
			$lesSessions = $rep->listeSessions(1, $nbParPage);
				
			// On calcule le nombre total de pages grâce au count($lesSessions) qui retourne le nombre total de sessions
			$nbPages = ceil(count($lesSessions) / $nbParPage);
				
			// On donne toutes les informations nécessaires à la vue
			return $this->render('FormArmorBundle:Admin:session.html.twig', array(
				'lesSessions' => $lesSessions,
				'nbPages'     => $nbPages,
				'page'        => 1,
				));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formSession.html.twig', array('form' => $form->createView(), 'action' => 'SUPPRESSION'));
    }
	
	// Gestion des plans de formation
	public function listePlanFormationAction($page)
	{
		if ($page < 1)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}

		// On peut fixer le nombre de lignes avec la ligne suivante :
		// $nbParPage = 4;
		// Mais bien sûr il est préférable de définir un paramètre dans "app\config\parameters.yml", et d'y accéder comme ceci :
		$nbParPage = $this->container->getParameter('nb_par_page');
		
		// On récupère l'objet Paginator
		$manager = $this->getDoctrine()->getManager();
		$rep = $manager->getRepository('FormArmorBundle:Plan_formation');
		$lesPlans = $rep->listePlans($page, $nbParPage);
		
		// On calcule le nombre total de pages grâce au count($lesPlans) qui retourne le nombre total de plans de formation
		$nbPages = ceil(count($lesPlans) / $nbParPage);
		
		// Si la page n'existe pas, on retourne une erreur 404
		if ($page > $nbPages)
		{
			throw $this->createNotFoundException("La page ".$page." n'existe pas.");
		}
		
		// On donne toutes les informations nécessaires à la vue
		return $this->render('FormArmorBundle:Admin:plan.html.twig', array(
		  'lesPlans' => $lesPlans,
		  'nbPages'     => $nbPages,
		  'page'        => $page,
		));
	}
	public function modifPlanFormationAction($id, Request $request) // Affichage du formulaire de modification d'un plan de formation
    {
        // Récupération de la formation d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Plan_formation');
		$plan = $rep->find($id);
		
		// Création du formulaire à partir du plan "récupéré"
		$form   = $this->get('form.factory')->create(PlanFormationType::class, $plan);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			if ($form->isValid())
			{
				// mise à jour de la bdd
				$em->persist($plan);
				$em->flush();
				
				// Réaffichage de la liste des sessions
				$nbParPage = $this->container->getParameter('nb_par_page');
				// On récupère l'objet Paginator
				$lesPlans = $rep->listePlans(1, $nbParPage);
				
				// On calcule le nombre total de pages grâce au count($lesSessions) qui retourne le nombre total de sessions
				$nbPages = ceil(count($lesPlans) / $nbParPage);
				
				// On donne toutes les informations nécessaires à la vue
				return $this->render('FormArmorBundle:Admin:plan.html.twig', array(
				  'lesPlans' => $lesPlans,
				  'nbPages'     => $nbPages,
				  'page'        => 1,
				));
			}
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formPlan.html.twig', array('form' => $form->createView(), 'action' => 'modification'));
    }
	public function suppPlanFormationAction($id, Request $request) // Affichage du formulaire de suppression d'un plan de formation
    {
        // Récupération du plan de formation d'identifiant $id
		$em = $this->getDoctrine()->getManager();
		$rep = $em->getRepository('FormArmorBundle:Plan_formation');
		$plan = $rep->find($id);
		
		// Création du formulaire à partir du plan de formation "récupéré"
		$form   = $this->get('form.factory')->create(PlanFormationType::class, $plan);
		
		// Mise à jour de la bdd si method POST ou affichage du formulaire dans le cas contraire
		if ($request->getMethod() == 'POST')
		{
			$form->handleRequest($request); // permet de récupérer les valeurs des champs dans les inputs du formulaire.
			
			// mise à jour de la bdd
			$res = $rep->suppPlanFormation($id);
			$em->persist($plan);
			$em->flush();
				
			// Réaffichage de la liste des plans de formation
			$nbParPage = $this->container->getParameter('nb_par_page');
			// On récupère l'objet Paginator
			$lesPlans = $rep->listePlans(1, $nbParPage);
				
			// On calcule le nombre total de pages grâce au count($lesPlans) qui retourne le nombre total de plans de formation
			$nbPages = ceil(count($lesPlans) / $nbParPage);
				
			// On donne toutes les informations nécessaires à la vue
			return $this->render('FormArmorBundle:Admin:plan.html.twig', array(
				'lesPlans' => $lesPlans,
				'nbPages'     => $nbPages,
				'page'        => 1,
				));
		}
		// Si formulaire pas encore soumis ou pas valide (affichage du formulaire)
		return $this->render('FormArmorBundle:Admin:formPlan.html.twig', array('form' => $form->createView(), 'action' => 'SUPPRESSION'));
    }
    public function listeValidSessionAction($page)
	{
            if ($page < 1)
                {
                    return $this->render('FormArmorBundle:Admin:inscript.html.twig', array(
                    'lesSessions' => null,
                    'nbPages'     => 0,
                    'page'        => 0,
                    ));
                }

            // On peut fixer le nombre de lignes avec la ligne suivante :
            // $nbParPage = 4;
            // Mais bien sûr il est préférable de définir un paramètre dans "app\config\parameters.yml", et d'y accéder comme ceci :
            $nbParPage = $this->container->getParameter('nb_par_page');

            // On récupère l'objet Paginator
            $manager = $this->getDoctrine()->getManager();
            $rep = $manager->getRepository('FormArmorBundle:Session_formation');
            $lesSessions = $rep->listeSessionsaValider($page, $nbParPage);

            // On calcule le nombre total de pages grâce au count($lesSessions) qui retourne le nombre total de sessions
            $nbPages = ceil(count($lesSessions) / $nbParPage);

            // Si la page n'existe pas, on retourne une erreur 404
            if ($page > $nbPages)
            {
                    return $this->render('FormArmorBundle:Admin:inscript.html.twig', array(
                    'lesSessions' => null,
                    'nbPages'     => 0,
                    'page'        => 0,
                    ));
            }

            // On donne toutes les informations nécessaires à la vue
            return $this->render('FormArmorBundle:Admin:inscript.html.twig', array(
              'lesSessions' => $lesSessions,
              'nbPages'     => $nbPages,
              'page'        => $page,
            ));
	}
    public function confirmValidSessionAction($idSession)
        {
            $em = $this->getDoctrine()->getManager();
            $rep = $em->getRepository('FormArmorBundle:Inscription');
            $rep2 = $em->getRepository('FormArmorBundle:Statut');
            $rep3 = $em->getRepository('FormArmorBundle:Session_formation');
            $lesClients = $rep->listeClientUneSession($idSession);
            $RevenueSession = $rep2->sommeSt($idSession);
            $PrixSession = $rep3->retuPrixSession($idSession);
            $Marge = $RevenueSession - $PrixSession;
            //$LaSession = $rep3->retuSession($idSession);
            return $this->render('FormArmorBundle:Admin:confirmSession.html.twig', array(
                'lesClients' => $lesClients,
                'idSession' => $idSession,
                'Marge' => $Marge
            ));
        }
    public function valideLaSessionAction($idSession)
        {
            $em = $this->getDoctrine()->getManager();
            $rep = $em->getRepository('FormArmorBundle:Inscription');
            $rep2 = $em->getRepository('FormArmorBundle:Session_formation');
            $lesMail = $rep->listeMailuneSession($idSession);
            $laSession = $rep2->retuSession($idSession);
            $rep2->closeUneSession($idSession);
            $transport = \Swift_SmtpTransport::newInstance()
            ->setUsername('valoo221@gmail.com')->setPassword('Harry20012')
            ->setHost('smtp.gmail.com')
            ->setPort(587)->setEncryption('tls');
            $mailer= \Swift_Mailer::newInstance($transport);
            $message = \Swift_Message::newInstance()
            ->setSubject('Confiramtion d\'inscription a la Session N°'.$idSession.' avec FormArmor')
            ->setFrom('valoo221@gmail.com','FormArmor Inc.')
            ->setBcc($lesMail)
            ->setBody($this->renderView('Email/MailConfirmationSession.html.twig',array('laSession' => $laSession)));
            $result=$mailer->send($message);
            return $this->render('FormArmorBundle:Admin:SessionValide.html.twig', 
                    array(
                        'laSession' => $laSession, 
                        'lesMail' => $lesMail,
                        'leMessage' => $message
                        ));
        }
    public function annulerLaSessionAction($idSession)
        {
            $em = $this->getDoctrine()->getManager();
            $rep2 = $em->getRepository('FormArmorBundle:Session_formation');
            $laSession = $rep2->retuSession($idSession);
             return $this->render('FormArmorBundle:Admin:SessionAnnuler.html.twig', 
                    array(
                        'laSession' => $laSession,
                        ));
        }
    public function confirmAnnulationSessionAction($idSession, $motif)
        {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('FormArmorBundle:Inscription');
        $rep2 = $em->getRepository('FormArmorBundle:Session_formation');
        $lesMail = $rep->listeMailuneSession($idSession);
        $laSession = $rep2->retuSession($idSession);
        $rep2->closeUneSession($idSession);
        $transport = \Swift_SmtpTransport::newInstance()
        ->setUsername('valoo221@gmail.com')->setPassword('Harry20012')
        ->setHost('smtp.gmail.com')
        ->setPort(587)->setEncryption('tls');
        $mailer= \Swift_Mailer::newInstance($transport);
        $message = \Swift_Message::newInstance()
        ->setSubject('Annulation d\'inscription a la Session N°'.$idSession.' avec FormArmor')
        ->setFrom('valoo221@gmail.com','FormArmor Inc.')
        ->setBcc($lesMail)
        ->setBody($this->renderView('Email/MailAnnulationSession.html.twig',array('laSession' => $laSession,'motif' => $motif)));
        $result=$mailer->send($message);
        $reponse = new Response(json_encode(array('message'=>'Ok')));
        $reponse->headers->set('Content-Type', 'application/json');
        return $reponse;
        }
    public function AnnulationSessionAction($idSession, $motif)
    {
        $em = $this->getDoctrine()->getManager();
        $rep = $em->getRepository('FormArmorBundle:Inscription');
        $rep2 = $em->getRepository('FormArmorBundle:Session_formation');
        $lesMail = $rep->listeMailuneSession($idSession);
        $laSession = $rep2->retuSession($idSession);
        return $this->render('FormArmorBundle:Admin:ConfSessionAnnuler.html.twig', 
                    array(
                        'laSession' => $laSession, 
                        'lesMail' => $lesMail,
                        'motif' => $motif
                        ));
    }
}
