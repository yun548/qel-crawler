<?php

namespace App\Entity;

use App\Repository\SearchResultRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SearchResultRepository::class)
 */
class SearchResult
{
    public const ENGINE_QWANT = 'qwant';

    public const ENGINE_ECOSIA = 'ecosia';

    public const ENGINE_BING = 'bing';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $url;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $searchEngine;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $query;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getSearchEngine(): ?string
    {
        return $this->searchEngine;
    }

    public function setSearchEngine(string $searchEngine): self
    {
        $this->searchEngine = $searchEngine;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param mixed $query
     * @return SearchResult
     */
    public function setQuery($query): self
    {
        $this->query = $query;

        return $this;
    }
}
