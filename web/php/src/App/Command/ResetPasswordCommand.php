<?php
namespace App\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Base\Entity\Users;
use DateTime;

class ResetPasswordCommand extends Command
{
    protected static $defaultName = 'user:reset-password';
    private $em;

    public function __construct(EntityManager $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->setDescription('重設指定帳號的密碼，並產生新初始密碼')
            ->addArgument('username', InputArgument::REQUIRED, '要重設密碼的帳號');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $username = $input->getArgument('username');
        $userRepo = $this->em->getRepository(Users::class);
        $user = $userRepo->findOneBy(['username' => $username]);
        if (!$user) {
            $output->writeln('<error>找不到帳號：' . $username . '</error>');
            return Command::FAILURE;
        }
        $plainPassword = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 8);
        $user->setPassword(password_hash($plainPassword, PASSWORD_DEFAULT));
        $user->setUpdatedAt(new DateTime());
        $this->em->flush();
        $output->writeln('<info>密碼已重設</info>');
        $output->writeln('帳號：' . $username);
        $output->writeln('新密碼：' . $plainPassword);
        return Command::SUCCESS;
    }
}
