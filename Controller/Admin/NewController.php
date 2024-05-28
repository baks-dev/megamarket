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

namespace BaksDev\Megamarket\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Megamarket\Entity\MegamarketToken;
use BaksDev\Megamarket\UseCase\Admin\NewEdit\MegamarketTokenDTO;
use BaksDev\Megamarket\UseCase\Admin\NewEdit\MegamarketTokenForm;
use BaksDev\Megamarket\UseCase\Admin\NewEdit\MegamarketTokenHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_MEGAMARKET_TOKEN_NEW')]
final class NewController extends AbstractController
{
    #[Route('/admin/megamarket/token/new', name: 'admin.newedit.new', methods: ['GET', 'POST'])]
    public function news(
        Request $request,
        MegamarketTokenHandler $YaMarketTokenHandler,
        LoggerInterface $logger
    ): Response
    {

        $YaMarketTokenDTO = new MegamarketTokenDTO();

        if($this->getAdminFilterProfile())
        {
            $YaMarketTokenDTO->setProfile($this->getAdminFilterProfile());
        }

        // Форма
        $form = $this->createForm(MegamarketTokenForm::class, $YaMarketTokenDTO, [
            'action' => $this->generateUrl('megamarket:admin.newedit.new'),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('megamarket_token'))
        {
            $this->refreshTokenForm($form);

            $YaMarketToken = $YaMarketTokenHandler->handle($YaMarketTokenDTO);

            if($YaMarketToken instanceof MegamarketToken)
            {
                $this->addFlash('breadcrumb.new', 'success.new', 'megamarket.admin');

                return $this->redirectToRoute('megamarket:admin.index');
            }

            $this->addFlash('breadcrumb.new', 'danger.new', 'megamarket.admin', $YaMarketToken);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}