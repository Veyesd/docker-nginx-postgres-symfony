<?php
namespace App\Controller;

use App\Service\ApiFormaterResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use App\Service\EntityManagerService;
use App\Service\JwtService;

abstract class AbstractController extends SymfonyAbstractController
{
    protected $entityManagerService;
    protected $asfr;
    protected $jwts;

    public function __construct(EntityManagerService $entityManagerService, ApiFormaterResponseService $asfr, JwtService $jwts)
    {
        $this->entityManagerService = $entityManagerService;
        $this->asfr = $asfr;
        $this->jwts = $jwts;
    }

    protected function getEntityManager()
    {
        return $this->entityManagerService->getEntityManager();
    }

    protected function getResponseFormater()
    {
        return $this->asfr;
    }

    protected function getJwtService()
    {
        return $this->jwts;
    }
}