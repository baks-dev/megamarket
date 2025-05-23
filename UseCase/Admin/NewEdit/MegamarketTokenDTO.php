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

namespace BaksDev\Megamarket\UseCase\Admin\NewEdit;

use BaksDev\Megamarket\Entity\Event\MegamarketTokenEventInterface;
use BaksDev\Megamarket\Type\Event\MegamarketTokenEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see MegamarketTokenEvent */
final class MegamarketTokenDTO implements MegamarketTokenEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?MegamarketTokenEventUid $id = null;

    /**
     * ID настройки (профиль пользователя)
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?UserProfileUid $profile = null;

    /**
     * Токен
     */
    private ?string $token = null;


    /**
     * Идентификатор компании
     */
    #[Assert\NotBlank]
    private int $company;

    /**
     * Торговая наценка
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: -100, max: 100)]
    private int $percent = 0;

    /**
     * Торговая надбавка на габариты товара
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 0)]
    private int $rate = 0;

    /**
     * Статус true = активен / false = заблокирован
     */
    private bool $active = true;


    public function setId(?MegamarketTokenEventUid $id): void
    {
        $this->id = $id;
    }


    public function getEvent(): ?MegamarketTokenEventUid
    {
        return $this->id;
    }


    /**
     * Profile
     */
    public function getProfile(): ?UserProfileUid
    {
        return $this->profile;
    }


    public function setProfile(UserProfileUid $profile): void
    {
        $this->profile = $profile;
    }

    /**
     * Token
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        if(!empty($token))
        {
            $this->token = $token;
        }
    }

    public function hiddenToken(): void
    {
        $this->token = null;
    }


    /**
     * Company
     */
    public function getCompany(): int
    {
        return $this->company;
    }

    public function setCompany(int $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Active
     */
    public function getActive(): bool
    {
        return $this->active;
    }


    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * Percent
     */
    public function getPercent(): int
    {
        return $this->percent;
    }

    public function setPercent(?int $percent): self
    {
        $this->percent = $percent ?? 0;
        return $this;
    }

    /**
     * Rate
     */
    public function getRate(): int
    {
        return $this->rate;
    }

    public function setRate(?int $rate): self
    {
        $this->rate = $rate ?? 0;
        return $this;
    }

}
