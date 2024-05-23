<?php

namespace App\Command;

use App\Repository\AccessTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsCronTask;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;
#[AsCommand(
    name: 'app:token:clean',
    description: 'Add a short description for your command',
)]
#[AsCronTask(expression: '* * * * *')]
#[AsPeriodicTask('1 minute', schedule: 'default')] 
class TokenCleanCommand extends Command
{
    private $accessTokenRepository;
    private $em;
    private $userRepository;

    public function __construct(AccessTokenRepository $accessTokenRepository, UserRepository $userRepository, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->accessTokenRepository = $accessTokenRepository;
        $this->em = $em;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        // $this
        //     ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        //     ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        // ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tokens = $this->accessTokenRepository->findAll();
        // dd($tokens);

        foreach($tokens as $key => $token){
            $user = $this->userRepository->findOneByToken($token);
            $this->accessTokenRepository->clearTokenIfOutdated($user);
        }
        // $arg1 = $input->getArgument('arg1');
        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }
        // if ($input->getOption('option1')) {
        //     // ...
        // }

        $io->success('Table de tokens clean!');

        return Command::SUCCESS;
    }
}
