<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Megamarket\UseCase\Admin\Delete;

use BaksDev\Megamarket\Entity\Event\MegamarketTokenEvent;
use BaksDev\Megamarket\Entity\Event\MegamarketTokenEventInterface;
use BaksDev\Megamarket\Type\Event\MegamarketTokenEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see MegamarketTokenEvent */
final class MegamarketTokenDeleteDTO implements MegamarketTokenEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private readonly MegamarketTokenEventUid $id;

    /**
     * ID настройки (профиль пользователя)
     */
    #[Assert\NotBlank]
    private readonly UserProfileUid $profile;

    /**
     * Модификатор
     */
    #[Assert\Valid]
    private Modify\ModifyDTO $modify;


    public function __construct()
    {
        $this->modify = new Modify\ModifyDTO();
    }


    /**
     * ID настройки (профиль пользователя)
     */

    public function setId(MegamarketTokenEventUid $id): void
    {
        $this->id = $id;
    }


    public function getEvent(): ?MegamarketTokenEventUid
    {
        return $this->id;
    }


    /**
     * Модификатор
     */
    public function getModify(): Modify\ModifyDTO
    {
        return $this->modify;
    }


    /**
     * ID настройки (профиль пользователя)
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }


    public function setProfile(UserProfileUid $profile): void
    {
        $this->profile = $profile;
    }

}
