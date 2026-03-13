<?php declare(strict_types=1);

namespace App\Doctrine\Traits;

use Doctrine\ORM\Mapping as ORM;

trait CreatedAtTrait
{
    #[ORM\Column]
    public ?\DateTimeImmutable $createdAt = null;

    public function setCreatedAt(): void
    {
        $this->createdAt ??= new \DateTimeImmutable();
    }
}
