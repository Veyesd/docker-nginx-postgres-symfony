<?php
namespace App\Controller;

use App\Service\ApiFormaterResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use App\Service\EntityManagerService;

abstract class AbstractController extends SymfonyAbstractController
{
    protected $entityManagerService;
    protected $asfr;

    public function __construct(EntityManagerService $entityManagerService, ApiFormaterResponseService $asfr)
    {
        $this->entityManagerService = $entityManagerService;
        $this->asfr = $asfr;
    }

    protected function getEntityManager()
    {
        return $this->entityManagerService->getEntityManager();
    }

    protected function getResponseFormater(){
        return $this->asfr;
    }
}