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

namespace BaksDev\Megamarket\Repository\MegamarketTokenByProfile;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Megamarket\Entity\Event\MegamarketTokenEvent;
use BaksDev\Megamarket\Entity\MegamarketToken;
use BaksDev\Megamarket\Type\Authorization\MegamarketAuthorizationToken;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;

final class MegamarketTokenByProfileRepository implements MegamarketTokenByProfileInterface
{
    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * Токен авторизации
     */
    public function getToken(UserProfileUid $profile): MegamarketAuthorizationToken|false
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb
            ->from(MegamarketToken::class, 'token')
            ->where('token.id = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE);

        $qb->join(
            'token',
            MegamarketTokenEvent::class,
            'event',
            'event.id = token.event AND event.active = true',
        );

        $qb
            ->join(
                'token',
                UserProfileInfo::class,
                'info',
                'info.profile = token.id AND info.status = :status',
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );

        $qb->select('token.id AS profile');
        $qb->addSelect('event.token AS token');
        $qb->addSelect('event.company AS company');
        $qb->addSelect('event.percent AS percent');
        $qb->addSelect('event.rate AS rate');

        /* Кешируем результат ORM */
        return $qb
            ->enableCache('megamarket', 86400)
            ->fetchHydrate(MegamarketAuthorizationToken::class);

    }

}
