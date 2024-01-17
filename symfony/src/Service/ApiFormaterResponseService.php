<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ApiFormaterResponseService
{
    private $serializer;

    public function __construct(SerializerInterface $serializer){
        $this->serializer = $serializer;
    }

    /**
     * @param string $name Le nom de la variable qui reçoit les données
     * @param mixed $data L'objet à serialiser
     * @param string|null $group Le group JWS à exposer, si null = 'default'
     */
    public function response(string $name, $data, string $group = null): Response
    {

        return new JsonResponse([
            $name => json_decode($this->serializer->serialize(
                $data,
                'json',
                [
                    'groups' => [ $group ?? 'default' ]
                ],
                true
            )
        )]);
    }
}