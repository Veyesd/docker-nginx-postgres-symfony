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

#[AsCommand(
    name: 'app:token:clean',
    description: 'Add a short description for your command',
)]
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
            $accessToken = $this->accessTokenRepository->findOneByValue($token->getValue());
            $user = $this->userRepository->findOneByToken($accessToken->getId());
            $user->setToken(null);
            $roles = $user->getRoles();
            unset($roles[array_search("ROLE_CONNECTED", $user->getRoles())]);
            $user->setRoles($roles);
    
            $this->em->remove($accessToken);
            $this->em->flush();
        }
        // $arg1 = $input->getArgument('arg1');
        // if ($arg1) {
        //     $io->note(sprintf('You passed an argument: %s', $arg1));
        // }
        // if ($input->getOption('option1')) {
        //     // ...
        // }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
