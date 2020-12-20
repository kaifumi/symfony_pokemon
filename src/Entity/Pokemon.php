<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\PokemonRepository;
use Doctrine\ORM\Mapping as ORM;



/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=PokemonRepository::class)
 * @ApiResource
 */
class Pokemon
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @ORM\GeneratedValue
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    private $name;

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @ORM\GeneratedValue
     * @ORM\Column(name="type1", type="string", nullable=true)
     */

    private $type1;

    public function getType1(): ?string
    {
        return $this->type1;
    }

    /**
     * @ORM\GeneratedValue
     * @ORM\Column(name="type2", type="string", nullable=true)
     */

    private $type2;

    public function getType2(): ?string
    {
        return $this->type2;
    }
    /**
     * @ORM\GeneratedValue
     * @ORM\Column(name="form", type="boolean", nullable=true, )
     */

    private $form;

    public function getForm(): ?string
    {
        return $this->form;
    }
}