<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Megamarket\Api;

use App\Kernel;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Megamarket\Repository\MegamarketTokenByProfile\MegamarketTokenByProfileInterface;
use BaksDev\Megamarket\Type\Authorization\MegamarketAuthorizationToken;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use DomainException;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\Cache\CacheInterface;

abstract class Megamarket
{
    protected ?UserProfileUid $profile = null;

    private ?MegamarketAuthorizationToken $AuthorizationToken = null;

    private array $headers;

    public function __construct(
        #[Autowire(env: 'APP_ENV')] private readonly string $environment,
        #[Target('megamarketLogger')] protected readonly LoggerInterface $logger,
        private readonly MegamarketTokenByProfileInterface $TokenByProfile,
        private readonly AppCacheInterface $cache,

    ) {}

    public function profile(UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        $this->AuthorizationToken = $this->TokenByProfile->getToken($this->profile);

        return $this;
    }

    public function TokenHttpClient(?MegamarketAuthorizationToken $AuthorizationToken = null): RetryableHttpClient
    {
        if($AuthorizationToken !== null)
        {
            $this->AuthorizationToken = $AuthorizationToken;
            $this->profile = $AuthorizationToken->getProfile();
        }

        if($this->AuthorizationToken === null)
        {
            if(!$this->profile)
            {
                $this->logger->critical('Не указан идентификатор профиля пользователя через вызов метода profile', [self::class.':'.__LINE__]);

                throw new InvalidArgumentException(
                    'Не указан идентификатор профиля пользователя через вызов метода profile: ->profile($UserProfileUid)'
                );
            }

            $this->AuthorizationToken = $this->TokenByProfile->getToken($this->profile);

            if(!$this->AuthorizationToken)
            {
                throw new DomainException(sprintf('Токен авторизации Megamarket не найден: %s', $this->profile));
            }
        }

        return new RetryableHttpClient(
            HttpClient::create(/*['headers' => $this->headers]*/)
                ->withOptions([
                    'base_uri' => 'https://api.megamarket.tech',
                    'verify_host' => false,
                    'max_duration' => 15,
                ])
        );
    }

    /**
     * Profile
     */
    protected function getProfile(): ?UserProfileUid
    {
        return $this->profile;
    }

    protected function getToken(): string
    {
        return $this->AuthorizationToken->getToken();
    }

    protected function getCompany(): int
    {
        return $this->AuthorizationToken->getCompany();
    }

    protected function getPercent(): int
    {
        return $this->AuthorizationToken->getPercent();
    }

    protected function getRate(): int
    {
        return $this->AuthorizationToken->getRate();
    }

    /**
     * Метод проверяет что окружение является PROD,
     * тем самым позволяет выполнять операции запроса на сторонний сервис
     * ТОЛЬКО в PROD окружении
     */
    protected function isExecuteEnvironment(): bool
    {
        return $this->environment === 'prod';
    }

    protected function getCacheInit(string $namespace): CacheInterface
    {
        return $this->cache->init($namespace);
    }

}
