<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\DeserializationContext;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *     name = "self",
 *     href = @Hateoas\Route(
 *         "app_details_users",
 *         parameters = {"id" = "expr(object.getId())"}
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getUsers")
 * )
 *
 *
 * @Hateoas\Relation(
 *     name ="delete",
 *     href = @Hateoas\Route(
 *         "app_delete_user",
 *         parameters = {"id" = "expr(object.getId())"}
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getUsers", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 *
 *
 * @Hateoas\Relation(
 *     name = "update",
 *     href = @Hateoas\Route(
 *         "app_update_user",
 *         parameters = {"id" = "expr(object.getId())"}
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups="getUsers", excludeIf = "expr(not is_granted('ROLE_ADMIN'))"),
 * )
 *
 * @ORM\Entity(repositoryClass=UserRepository::class)
 *
 * @UniqueEntity("email", message="Cet email est deja utilisé")
 */
class User extends DeserializationContext implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"getUsers"})
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"getUsers","createUser","updateUser"})
     * @Assert\NotBlank(message="Le nom de l'utilisateur est obligatoire")
     * @Assert\Length(min=1,max=100,minMessage="Le nom doit faire au moins {{ limit }} caractères",maxMessage="Le nom ne doit pas dépasser {{ limit }} caractères")
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"getUsers","createUser","updateUser"})
     * @Assert\NotBlank(message="L'email de l'utilisateur est obligatoire")
     * @Assert\Length(min=1,max=180, minMessage="L'email doit faire au moins {{ limit }} caractères", maxMessage="L'email ne doit pas dépasser {{ limit }} caractères")
     * @Assert\Email()
     */
    private ?string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Groups({"createUser","updateUser"})
     * @Assert\NotBlank(message="Le mot de passe du client est obligatoire")
     * @Assert\Length(min=8,max=180,minMessage="Le mot de passe doit faire au moins {{ limit }} caractères", maxMessage="Le mot de passe ne doit pas dépasser {{ limit }} caractères")
     */
    private string $password;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="users")
     * @Groups({"getUsers"})
     */
    private ?Client $client;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
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
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {

    }
}
