<?php

namespace App\Dto;

use Doctrine\ORM\Mapping as ORM;

class ProductDto
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $brand;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $picture;

    /**
     * @ORM\Column(type="text")
     */
    private ?string $description;

}
