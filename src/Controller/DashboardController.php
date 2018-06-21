<?php
/**
 * Created by PhpStorm.
 * User: famoser
 * Date: 22/02/2018
 * Time: 11:35
 */

namespace App\Controller;

use App\Controller\Base\BaseLoginController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
