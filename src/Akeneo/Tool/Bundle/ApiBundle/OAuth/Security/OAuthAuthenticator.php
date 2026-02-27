<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ApiBundle\OAuth\Security;

use Akeneo\Tool\Bundle\ApiBundle\OAuth\OAuth2;
use Akeneo\Tool\Bundle\ApiBundle\OAuth\OAuth2AuthenticateException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Symfony security authenticator that replaces FOS OAuth firewall listener.
 * Extracts the Bearer token from the request and validates it via our OAuth2 server.
 */
class OAuthAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private readonly OAuth2 $oauthServer,
        private readonly UserProviderInterface $userProvider,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $this->getTokenFromRequest($request) !== null;
    }

    public function authenticate(Request $request): Passport
    {
        $tokenString = $this->getTokenFromRequest($request);

        if (null === $tokenString) {
            throw new CustomUserMessageAuthenticationException('No Bearer token found in request.');
        }

        try {
            $accessToken = $this->oauthServer->verifyAccessToken($tokenString);
        } catch (OAuth2AuthenticateException $e) {
            throw new CustomUserMessageAuthenticationException($e->getDescription());
        }

        $user = $accessToken->getData();

        if (null === $user) {
            throw new CustomUserMessageAuthenticationException('No user associated with this access token.');
        }

        // Get the user identifier for the UserBadge
        $userIdentifier = $user->getUserIdentifier();

        return new SelfValidatingPassport(
            new UserBadge($userIdentifier, function (string $identifier) {
                return $this->userProvider->loadUserByIdentifier($identifier);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Let the request continue
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(
            ['error' => 'authentication_error', 'error_description' => $exception->getMessageKey()],
            Response::HTTP_UNAUTHORIZED,
            ['WWW-Authenticate' => 'Bearer']
        );
    }

    private function getTokenFromRequest(Request $request): ?string
    {
        $authHeader = $request->headers->get('Authorization');

        if (null !== $authHeader && str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        // Check query parameter fallback
        $token = $request->query->get('access_token');
        if (null !== $token) {
            return $token;
        }

        // Check request body
        $token = $request->request->get('access_token');

        return $token;
    }
}
