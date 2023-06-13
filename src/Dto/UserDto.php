<?php

namespace App\Dto;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

class UserDto
{
    /**
     * @ORM\Column(type="string", length=100)
     * @Groups({"getUsers"})
     * @Assert\NotBlank(message="Le nom de l'utilisateur est obligatoire")
     * @Assert\Length(min=1,max=100,minMessage="Le nom doit faire au moins {{ limit }} caractères",maxMessage="Le nom ne doit pas dépasser {{ limit }} caractères")
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"getUsers"})
     * @Assert\NotBlank(message="L'email de l'utilisateur est obligatoire")
     * @Assert\Length(min=1,max=180, minMessage="L'email doit faire au moins {{ limit }} caractères", maxMessage="L'email ne doit pas dépasser {{ limit }} caractères")
     */
    private ?string $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Groups({"getUsers"})
     * @Assert\NotBlank(message="Le mot de passe du client est obligatoire")
     * @Assert\Length(min=8,max=180,minMessage="Le mot de passe doit faire au moins {{ limit }} caractères", maxMessage="Le mot de passe ne doit pas dépasser {{ limit }} caractères")
     */
    private string $password;

}
