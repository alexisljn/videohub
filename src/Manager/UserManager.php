<?php


namespace App\Manager;


use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    private $userRepository;
    private $entityManager;
    private $passwordEncoder;

    public function __construct(UserRepository $userRepository,
                                EntityManagerInterface $entityManager,
                                UserPasswordEncoderInterface $passwordEncoder
    )
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function getUsers()
    {
        $users = $this->userRepository->findAll();
        return $users;
    }

    public function addUser(User $user)
    {
       $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
       $user->setPassword($password);
       $this->entityManager->persist($user);
       $this->entityManager->flush();
    }

    public function editUser(User $user, $passwordEdited = true)
    {
        if($passwordEdited) {
            $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function deleteUser(User $user)
    {
        $videos = $user->getVideos();
        foreach ($videos as $video) {
            $video->setUser(null);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }



}