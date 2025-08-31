<?php

namespace App\Security;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        // всегда авторизован
        return new UserBadge('default_user');

        if ($accessToken == $this->parameterBag->get('BEARER_TOKEN')) {
            return new UserBadge('default_user');
        }

        throw new BadCredentialsException('Invalid credentials.');
    }
}
