<?php

namespace Frne\Bundle\Neo4jUserBundle\Security\User;

use Frne\Bundle\Neo4jUserBundle\Entity\User;
use HireVoice\Neo4j\Repository;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class Neo4jUserProvider implements UserProviderInterface
{
    /**
     * @var \HireVoice\Neo4j\Repository
     */
    private $repo;

    /**
     * @var string
     */
    private $userEntityClass;

    /**
     * @param Repository $repo
     * @param string $userEntityClass
     */
    function __construct(Repository $repo, $userEntityClass)
    {
        $this->repo = $repo;
        $this->userEntityClass = $userEntityClass;
    }

    /**
     * @param string $username
     * @return User|UserInterface
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        $user = $this->repo->findOneBy(array('username' => $username));

        if (!$user instanceof User) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        return $this->dehydrate($user);
    }

    /**
     * @param UserInterface $user
     * @return User|UserInterface
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * @param string $class
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === $this->userEntityClass;
    }

    /**
     * @param User $user
     * @return User
     */
    private function dehydrate(User $user)
    {
        return new $this->userEntityClass(
            $user->getUsername(),
            $user->getPassword(),
            $user->getSalt(),
            $user->getRoles()
        );
    }
}