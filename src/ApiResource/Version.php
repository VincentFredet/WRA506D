<?php declare(strict_types=1);

namespace App\ApiResource;

use ApiPlatform\Metadata\Get;
use App\State\VersionProvider;

#[Get(
    uriTemplate: '/version',
    provider: VersionProvider::class,
    description: 'Retourne le numéro de version de l\'API',
)]
final class Version
{
    public string $version;
}
