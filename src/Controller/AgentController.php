<?php

namespace App\Controller;

use App\Entity\Agent;
use App\Repository\AgentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AgentController extends AbstractController
{
    
    /**
     * Cette méthode permet de récupérer l'ensemble des agents. 
     *
     * @param AgentRepository $agentRepository
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/agents', name: 'agents', methods: ['GET'])]
    public function getAllAgents(AgentRepository $agentRepository, SerializerInterface $serializer): JsonResponse
    {
        $agentList = $agentRepository->findAll();
        
        $jsonAgentList = $serializer->serialize($agentList, 'json', ['groups' => 'getAgents']);
        return new JsonResponse($jsonAgentList, Response::HTTP_OK, [], true);
    }
	
    /**
     * Cette méthode permet de récupérer un AGENT en particulier en fonction de son id. 
     *
     * @param Agent $agent
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/agents/{id}', name: 'detailAgent', methods: ['GET'])]
    public function getDetailAgent(Agent $agent, SerializerInterface $serializer): JsonResponse {
        $jsonAgent = $serializer->serialize($agent, 'json', ['groups' => 'getAgents']);
        return new JsonResponse($jsonAgent, Response::HTTP_OK, [], true);
    }

    
    /**
     * Cette méthode supprime un agent en fonction de son id. 
     * En cascade, les user associés aux auteurs seront aux aussi supprimés. 
     *
     * /!\ Attention /!\
     * pour éviter le problème :
     * "1451 Cannot delete or update a parent row: a foreign key constraint fails"
     * Il faut bien penser rajouter dans l'entité Book, au niveau de l'author :
     * #[ORM\JoinColumn(onDelete:"CASCADE")]
     * 
     * Et resynchronizer la base de données pour appliquer ces modifications. 
     * avec : php bin/console doctrine:schema:update --force
     * 
     * @param Agent $agent
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/agents/{id}', name: 'deleteAgent', methods: ['DELETE'])]
    public function deleteAgent(Agent $agent, EntityManagerInterface $em): JsonResponse {
        
        $em->remove($agent);
        
        $em->flush();
        // dd($agent->getBooks());
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Cette méthode permet de créer un nouvel auteur. Elle ne permet pas 
     * d'associer directement des livres à cet auteur. 
     * Exemple de données :
     * {
     *     "lastName": "Tolkien",
     *     "firstName": "J.R.R"
     * }
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $em
     * @param UrlGeneratorInterface $urlGenerator
     * @return JsonResponse
     */
    #[Route('/api/agents', name: 'createAgent', methods: ['POST'])]
    public function createAgent(Request $request, SerializerInterface $serializer,
        EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse {
        $agent = $serializer->deserialize($request->getContent(), Agent::class, 'json');
        
        // On vérifie les erreurs
        $errors = $validator->validate($agent);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        
        $em->persist($agent);
        $em->flush();

        $jsonAgent = $serializer->serialize($agent, 'json', ['groups' => 'getAgents']);
        $location = $urlGenerator->generate('detailAgent', ['id' => $agent->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
        return new JsonResponse($jsonAgent, Response::HTTP_CREATED, ["Location" => $location], true);	
    }

    
    /**
     * Cette méthode permet de mettre à jour un auteur. 
     * Exemple de données :
     * {
     *     "lastName": "Tolkien",
     *     "firstName": "J.R.R"
     * }
     * 
     * Cette méthode ne permet pas d'associer des livres et des auteurs.
     * 
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Agent $currentAgent
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/agents/{id}', name:"updateAgents", methods:['PUT'])]
    public function updateAgent(Request $request, SerializerInterface $serializer,
        Agent $currentAgent, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse {

        // On vérifie les erreurs
        $errors = $validator->validate($currentAgent);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $updatedAgent = $serializer->deserialize($request->getContent(), Agent::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAgent]);
        $em->persist($updatedAgent);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);

    }
}
