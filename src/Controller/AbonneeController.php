<?php

namespace App\Controller;

use App\Entity\Abonnee;
use App\Repository\AgentRepository;
use App\Repository\AbonneeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AbonneeController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des livres. 
     *
     * @param AbonneeRepository $abonneeRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/abonnees', name: 'abonnees', methods: ['GET'])]
    public function getAllAbonnees(AbonneeRepository $abonneeRepository, SerializerInterface $serializer): JsonResponse
    {
        $abonneeList = $abonneeRepository->findAll();
        
        $jsonAbonneeList = $serializer->serialize($abonneeList, 'json', ['groups' => 'getAbonnees']);
        return new JsonResponse($jsonAbonneeList, Response::HTTP_OK, [], true);
    }
	
    /**
     * Cette méthode permet de récupérer un livre en particulier en fonction de son id. 
     *
     * @param Abonnee $abonnee
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/abonnees/{id}', name: 'detailAbonnee', methods: ['GET'])]
    public function getDetailAbonnee(Abonnee $abonnee, SerializerInterface $serializer): JsonResponse {
        $jsonAbonnee = $serializer->serialize($abonnee, 'json', ['groups' => 'getAbonnees']);
        return new JsonResponse($jsonAbonnee, Response::HTTP_OK, [], true);
    }
    
    
    /**
     * Cette méthode permet de supprimer un livre par rapport à son id. 
     *
     * @param Abonnee $abonnee
     * @param EntityManagerInterface $em
     * @return JsonResponse 
     */
    #[Route('/api/abonnees/{id}', name: 'deleteAbonnee', methods: ['DELETE'])]
    public function deleteAbonnee(Abonnee $abonnee, EntityManagerInterface $em): JsonResponse {
        $em->remove($abonnee);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet d'insérer un nouveau livre. 
     * Exemple de données : 
     * {
     *     "title": "Le Seigneur des Anneaux",
     *     "coverText": "C'est l'histoire d'un anneau unique", 
     *     "idAgent": 5
     * }
     * 
     * Le paramètre idAgent est géré "à la main", pour créer l'association
     * entre un livre et un auteur. 
     * S'il ne correspond pas à un auteur valide, alors le livre sera considéré comme sans auteur. 
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @param AgentRepository $AgentRepository
     * @return JsonResponse
     */
    #[Route('/api/abonnees', name:"createAbonnee", methods: ['POST'])] 
    public function createAbonnee(Request $request, SerializerInterface $serializer, EntityManagerInterface $em,
        UrlGeneratorInterface $urlGenerator, AgentRepository $AgentRepository, ValidatorInterface $validator): JsonResponse {
        $abonnee = $serializer->deserialize($request->getContent(), Abonnee::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($abonnee);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            //throw new HttpException(JsonResponse::HTTP_BAD_REQUEST, "La requête est invalide");
        }

        $em->persist($abonnee);
        $em->flush();

        $content = $request->toArray();
        $idAgent = $content['idAgent'] ?? -1;

        $abonnee->setAgent($AgentRepository->find($idAgent));


		$jsonAbonnee = $serializer->serialize($abonnee, 'json', ['groups' => 'getAbonnees']);
		
        $location = $urlGenerator->generate('detailAbonnee', ['id' => $abonnee->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

		return new JsonResponse($jsonAbonnee, Response::HTTP_CREATED, ["Location" => $location], true);	
    }
    
    
    /**
     * Cette méthode permet de mettre à jour un livre en fonction de son id. 
     * 
     * Exemple de données : 
     * {
     *     "title": "Le Seigneur des Anneaux",
     *     "coverText": "C'est l'histoire d'un anneau unique", 
     *     "idAgent": 5
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Abonnee $currentAbonnee
     * @param EntityManagerInterface $em
     * @param AgentRepository $AgentRepository
     * @return JsonResponse
     */
    #[Route('/api/abonnees/{id}', name:"updateAbonnee", methods:['PUT'])]
    public function updateAbonnee(Request $request, SerializerInterface $serializer,
                        Abonnee $currentAbonnee, EntityManagerInterface $em, AgentRepository $AgentRepository, 
                        ValidatorInterface $validator): JsonResponse {
        $updatedAbonnee = $serializer->deserialize($request->getContent(), Abonnee::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAbonnee]);

        // On vérifie les erreurs
        $errors = $validator->validate($updatedAbonnee);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $idAgent = $content['idAgent'] ?? -1;

        $updatedAbonnee->setAgent($AgentRepository->find($idAgent));

        $em->persist($updatedAbonnee);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }


}
