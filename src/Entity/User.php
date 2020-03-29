<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\ResetPasswordAction;

/**
 * @ApiResource(
 *     itemOperations={
 *          "get"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY')",
 *              "normalization_context"={
 *                  "groups"={"get"}
 *              }
 *          },
 *          "put"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "denormalization_context"={
 *                  "groups"={"put"}
 *              },
 *               "normalization_context"={
 *                  "groups"={"get"}
 *              }
 *          },
 *          "put-reset-password"={
 *              "access_control"="is_granted('IS_AUTHENTICATED_FULLY') and object == user",
 *              "method"="PUT",
 *              "path"="/users/{id}/reset-password",
 *              "controller"=ResetPasswordAction::class,
 *              "denormalization_context"={
 *                  "groups"={"put-reset-password"}
 *              }
 *          }
 *      },
 *     collectionOperations={
 *      "post"={
 *          "denormalization_context"={
 *            "groups"={"post"}
 *          },
 *          "normalization_context"={
 *             "groups"={"get"}
 *         }
 *       }
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *     "username",
 *     message="Le nom d'utilisateur existe déjà."
 * )
 * @UniqueEntity(
 *     "email",
 *     message="Cette adresse email est déjà utilisée pour un autre compte."
 * )
 */
class User implements UserInterface
{
    const ROLE_COMMENTATOR = 'ROLE_COMMENTATOR';
    const ROLE_WRITER = 'ROLE_WRITER';
    const ROLE_EDITOR = 'ROLE_EDITOR';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPERADMIN = 'ROLE_SUPERADMIN';

    const DEFAULT_ROLES = [self::ROLE_COMMENTATOR];
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "post", "get-comment-with-author", "get-blog-post-with-author"})
     * @Assert\NotBlank(groups={"post"}, message="Vous aurez besoin d'un username pour vous connecté.")
     * @Assert\Length(groups={"post"}, min=6, max=255, minMessage="Cette valeur est trop courte. Le username doit contenir au moins 6 caractères.")
     */
    private $username;


    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"post"})
     * @Assert\NotBlank(
     *     groups={"post"},
     *     message="Le mot de passe ne doit pas être vide."
     * )
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])/",
     *     groups={"post"},
     *     message="Le mot de passe doit comporter sept caractères et contenir au moins un chiffre, une lettre majuscule et une lettre minuscule"
     * )
     */
    private $password;

    /**
     * @Groups({"post"})
     * @Assert\NotBlank(
     *     groups={"post"},
     *     message="Veillez saisir le même mot de passe, svp ne laisser pas ce champ vide."
     * )
     * @Assert\Expression(
     *     "this.getPassword() === this.getRetypedPassword()",
     *     message="Les mots de passe ne correspondent pas",
     *     groups={"post"}
     * )
     */
    private $retypedPassword;

    /**
     * @Groups({"put-reset-password"})
     * @Assert\NotBlank(
     *     message="Le mot de passe ne doit pas être vide."
     * )
     * @Assert\Regex(
     *     pattern="/(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])/",
     *     message="Le mot de passe doit comporter sept caractères et contenir au moins un chiffre, une lettre majuscule et une lettre minuscule"
     * )
     */
    private $newPassword;

    /**
     * @Groups({"put-reset-password"})
     * @Assert\NotBlank(
     *     message="Veillez saisir le même mot de passe, svp ne laisser pas ce champ vide."
     * )
     * @Assert\Expression(
     *     "this.getNewPassword() === this.getNewRetypedPassword()",
     *     message="Les mots de passe ne correspondent pas"
     * )
     */
    private $newRetypedPassword;

    /**
     * @Groups({"put-reset-password"})
     * @Assert\NotBlank()
     * @UserPassword()
     */
    private $oldPassword;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"get", "post", "put", "get-comment-with-author", "get-blog-post-with-author"})
     * @Assert\NotBlank(
     *     groups={"post"},
     *     message="Le nom ne doit pas être vide."
     * )
     * @Assert\Length(min=5, max=255, groups={"post", "put"})
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"post", "put", "get-admin", "get-owner"})
     * @Assert\NotBlank(
     *     groups={"post"},
     *     message="L'email ne doit pas être vide."
     * )
     * @Assert\Email(groups={"post", "put"}, message="Ceci n'est pas une adresse email.")
     * @Assert\Length(min=6, max=255, groups={"post", "put"})
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\BlogPost", mappedBy="author")
     * @Groups({"get"})
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="author")
     * @Groups({"get"})
     */
    private $comments;

    /**
     * @ORM\Column(type="simple_array", length=200)
     * @Groups({"get-admin", "get-owner"})
     */
    private $roles;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $passwordChangeDate;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->roles = self::DEFAULT_ROLES;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    /**
     * @return Collection
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
        return null;
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getRetypedPassword()
    {
        return $this->retypedPassword;
    }

    public function setRetypedPassword($retypedPassword): void
    {
        $this->retypedPassword = $retypedPassword;
    }

    public function getNewPassword(): ?string
    {
        return $this->newPassword;
    }

    public function setNewPassword($newPassword): void
    {
        $this->newPassword = $newPassword;
    }

    public function getNewRetypedPassword(): ?string
    {
        return $this->newRetypedPassword;
    }

    public function setNewRetypedPassword($newRetypedPassword): void
    {
        $this->newRetypedPassword = $newRetypedPassword;
    }

    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    public function setOldPassword($oldPassword): void
    {
        $this->oldPassword = $oldPassword;
    }

    public function getPasswordChangeDate()
    {
        return $this->passwordChangeDate;
    }

    public function setPasswordChangeDate($passwordChangeDate): void
    {
        $this->passwordChangeDate = $passwordChangeDate;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
