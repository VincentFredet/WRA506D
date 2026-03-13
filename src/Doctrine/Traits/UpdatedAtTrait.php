<?php declare(strict_types=1);

namespace App\Doctrine\Traits;

use Doctrine\ORM\Mapping as ORM;

trait UpdatedAtTrait
{
    #[ORM\Column(nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
