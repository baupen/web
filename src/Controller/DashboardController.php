<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/02/2018
 * Time: 11:35
 */

namespace App\Controller;

use App\Controller\Base\BaseLoginController;
use App\Entity\ConstructionManager;
use App\Form\Traits\User\LoginType;
use App\Form\Traits\User\RecoverType;
use App\Form\Traits\User\SetPasswordType;
use App\Service\Interfaces\EmailServiceInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/dashboard")
 */
class DashboardController extends BaseLoginController
{
    /**
     * @Route("/", name="dashboard_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->render('dashboard/index.html.twig');
    }
}
