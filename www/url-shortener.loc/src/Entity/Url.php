<?php

namespace App\Entity;

use App\Repository\UrlRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UrlRepository::class)
 */
class Url
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Url
     * @Assert\Regex("/^(http|https):\/\/[\w]+\.[\w]+/")
     */
    private $url;

    /**
     * @ORM\Column(type="text")
     * @Assert\Regex("/[0-9]+/")
     */
    private $hash;

//    /**
//     * @ORM\Column(type="string", length=14)
//     */
//    private $date;

    /**
     * @ORM\Column(name="created_date", type="datetime_immutable")
     * @Assert\NotBlank
     */
    private $createdDate;

    public function __construct()
    {
        $date = new \DateTimeImmutable();
        $this->setCreatedDate($date);
        $this->setHash($date->format('YmdHis') . mt_rand());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(?string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeImmutable
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeImmutable $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }
}
