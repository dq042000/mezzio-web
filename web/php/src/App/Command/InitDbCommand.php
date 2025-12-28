<?php
namespace App\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Base\Entity\AuthRole;
use Base\Entity\Users;
use DateTime;

class InitDbCommand extends Command
{
    protected static $defaultName = 'db:init';
    private $em;

    public function __construct(EntityManager $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this->setDescription('初始化資料庫，建立預設角色與管理員帳號');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1. 執行 migration
        $output->writeln('<info>執行 migration...</info>');
        passthru('php vendor/bin/doctrine migrations:migrate --no-interaction', $migrateStatus);
        if ($migrateStatus !== 0) {
            $output->writeln('<error>Migration 執行失敗</error>');
            return Command::FAILURE;
        }

        // 2. 匯入預設角色
        $roleRepo = $this->em->getRepository(AuthRole::class);
        $roles = [
            1 => '開發端系統管理員',
            2 => '系統管理員',
            3 => '一般使用者',
        ];
        foreach ($roles as $id => $name) {
            $role = $roleRepo->find($id);
            if (!$role) {
                $role = new AuthRole();
                $role->setId($id);
                $role->setName($name);
                $this->em->persist($role);
            }
        }
        $this->em->flush();
        $output->writeln('<info>預設角色已匯入</info>');

        // 3. 新增 admin 帳號
        $userRepo = $this->em->getRepository(Users::class);
        $admin = $userRepo->findOneBy(['username' => 'admin']);
        if ($admin) {
            $output->writeln('<comment>admin 帳號已存在，略過建立</comment>');
            return Command::SUCCESS;
        }
        $admin = new Users();
        $admin->setAuthRole($this->em->getReference(AuthRole::class, 1));
        $admin->setUsername('admin');
        $plainPassword = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 8);
        $admin->setPassword(password_hash($plainPassword, PASSWORD_DEFAULT));
        $admin->setStatus(1);
        $admin->setCreatedAt(new DateTime());
        $admin->setUpdatedAt(new DateTime());
        $this->em->persist($admin);
        $this->em->flush();
        $output->writeln('<info>admin 帳號已建立</info>');
        $output->writeln('帳號：admin');
        $output->writeln('密碼：' . $plainPassword);
        return Command::SUCCESS;
    }
}
