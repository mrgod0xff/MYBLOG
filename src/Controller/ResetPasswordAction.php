<?php


namespace App\Controller;


use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ResetPasswordAction
{
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var JWTTokenManagerInterface
     */
    private $tokenManager;

    public function __construct(
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $tokenManager
    )
    {
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;
    }

    public function __invoke(User $data)
    {
        //$reset = new ResetPasswordAction()
        //$reset()
        //var_dump(
          //  $data->getNewPassword(),
            //$data->getNewRetypedPassword(),
            //$data->getOldPassword(),
            //$data->getRetypedPassword()
        //);
        //die;
        $this->validator->validate($data);

        $data->setPassword(
            $this->passwordEncoder->encodePassword(
             $data, $data->getNewPassword()
            )
        );

        $data->setPasswordChangeDate(time());

        $this->entityManager->flush();

        $token = $this->tokenManager->create($data);

        return new JsonResponse(['token' => $token]);
    }
}