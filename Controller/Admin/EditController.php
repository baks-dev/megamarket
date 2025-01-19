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

namespace BaksDev\Megamarket\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Megamarket\Entity\Event\MegamarketTokenEvent;
use BaksDev\Megamarket\Entity\MegamarketToken;
use BaksDev\Megamarket\Type\Event\MegamarketTokenEventUid;
use BaksDev\Megamarket\UseCase\Admin\NewEdit\MegamarketTokenDTO;
use BaksDev\Megamarket\UseCase\Admin\NewEdit\MegamarketTokenForm;
use BaksDev\Megamarket\UseCase\Admin\NewEdit\MegamarketTokenHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_MEGAMARKET_TOKEN_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/megamarket/token/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] MegamarketTokenEvent $MegamarketTokenEvent,
        MegamarketTokenHandler $MegamarketTokenHandler,
    ): Response
    {

        $MegamarketTokenDTO = new MegamarketTokenDTO();

        /** Запрещаем редактировать чужой токен */
        if($this->isAdmin() === true || $this->getProfileUid()?->equals($MegamarketTokenEvent->getProfile()) === true)
        {
            $MegamarketTokenEvent->getDto($MegamarketTokenDTO);
        }

        if($request->getMethod() === 'GET')
        {
            $MegamarketTokenDTO->hiddenToken();
        }

        // Форма
        $form = $this->createForm(MegamarketTokenForm::class, $MegamarketTokenDTO, [
            'action' => $this->generateUrl(
                'megamarket:admin.newedit.edit',
                ['id' => $MegamarketTokenDTO->getEvent() ?: new MegamarketTokenEventUid()]
            ),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('megamarket_token'))
        {
            $this->refreshTokenForm($form);

            /** Запрещаем редактировать чужой токен */
            if($this->isAdmin() === false && $this->getProfileUid()?->equals($MegamarketTokenDTO->getProfile()) !== true)
            {
                $this->addFlash('breadcrumb.edit', 'danger.edit', 'megamarket.admin', '404');
                return $this->redirectToReferer();
            }

            $MegamarketToken = $MegamarketTokenHandler->handle($MegamarketTokenDTO);

            if($MegamarketToken instanceof MegamarketToken)
            {
                $this->addFlash('breadcrumb.edit', 'success.edit', 'megamarket.admin');

                return $this->redirectToRoute('megamarket:admin.index');
            }

            $this->addFlash('breadcrumb.edit', 'danger.edit', 'megamarket.admin', $MegamarketToken);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
