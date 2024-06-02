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

namespace BaksDev\Megamarket\UseCase\Admin\NewEdit;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Megamarket\Entity\Event\MegamarketTokenEvent;
use BaksDev\Megamarket\Entity\MegamarketToken;
use BaksDev\Megamarket\Messenger\MegamarketTokenMessage;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class MegamarketTokenHandler extends AbstractHandler
{

//    private EntityManagerInterface $entityManager;
//
//    private ValidatorInterface $validator;
//
//    private LoggerInterface $logger;
//
//    private MessageDispatchInterface $messageDispatch;
//
//
//    public function __construct(
//        EntityManagerInterface $entityManager,
//        ValidatorInterface $validator,
//        LoggerInterface $logger,
//        MessageDispatchInterface $messageDispatch,
//    )
//    {
//        $this->entityManager = $entityManager;
//        $this->validator = $validator;
//        $this->logger = $logger;
//        $this->messageDispatch = $messageDispatch;
//
//    }


    /** @see Megamarket */
    public function handle(
        MegamarketTokenDTO $command
    ): string|MegamarketToken
    {

        /** Валидация DTO  */
        $this->validatorCollection->add($command);

        $this->main = new MegamarketToken($command->getProfile());
        $this->event = new MegamarketTokenEvent();

        try
        {
            $command->getEvent() ? $this->preUpdate($command, true) : $this->prePersist($command);
        }
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid->getMessage();
        }

        /** Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new MegamarketTokenMessage($this->main->getId(), $this->main->getEvent(), $command->getEvent()),
            transport: 'megamarket'
        );


        return $this->main;
    }



    /** @see MegamarketToken */
    public function _handle(
        MegamarketTokenDTO $command,
    ): string|MegamarketToken
    {
        /**
         *  Валидация DTO
         */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        if($command->getEvent())
        {
            $EventRepo = $this->entityManager->getRepository(MegamarketTokenEvent::class)
                ->find(
                    $command->getEvent(),
                );

            if($EventRepo === null)
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by id: %s',
                    MegamarketTokenEvent::class,
                    $command->getEvent(),
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }

            $EventRepo->setEntity($command);
            $EventRepo->setEntityManager($this->entityManager);
            $Event = $EventRepo->cloneEntity();
        }
        else
        {
            $Event = new MegamarketTokenEvent();
            $Event->setEntity($command);
            $this->entityManager->persist($Event);
        }


        /** @var MegamarketToken $Main */
        $Main = $this->entityManager->getRepository(MegamarketToken::class)
            ->find($command->getProfile());

        if(empty($Main))
        {
            $Main = new MegamarketToken($command->getProfile());
            $this->entityManager->persist($Main);
        }

        /* присваиваем событие корню */
        $Main->setEvent($Event);


        /**
         * Валидация Event
         */

        $errors = $this->validator->validate($Event);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        /**
         * Валидация Main
         */
        $errors = $this->validator->validate($Main);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }


        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new MegamarketTokenMessage($Main->getId(), $Main->getEvent(), $command->getEvent()),
            transport: 'megamarket',
        );

        return $Main;
    }
}