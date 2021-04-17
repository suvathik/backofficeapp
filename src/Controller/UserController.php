<?php


namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function listuserAction()
    {

        $users= $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();



            return $this->render('list.html.twig', array(
                'users' => $users
            ));
    }

    /**
     * Create a new Payroll Department
     *
//     * @Security( "has_role('ROLE_HR_ADMIN') or has_role('ROLE_APP_ADMIN') or has_role('ROLE_RCD_ADMIN') or has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_MANAGE_ADMINS') or has_role('ROLE_STAFF_ATTENDANCE_ADMIN') or has_role('ROLE_MANAGE_ADMINS_ASHRAMITES') ")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function createAction(Request $request)
    {

        $user = new User();

        $form = $this->createForm(AdminType::class,$user);
        $form->handleRequest($request);

        $new_url = $this->generateUrl('create_admin');
        $redirect_url = $this->generateUrl('manage_admins');

//        $user->setPlainPassword($this->randomPassword());

        if ($form->isSubmitted() and $form->isValid()) {

//            $passwd = $this->randomPassword();
//            $admin->setPlainPassword($passwd);

            $this->get('isha_admin.common_manager')->updateEntity($user);
        }



        return $this->render('IshaAdminBundle:Common:basic.html.twig', array(
            'form'        => $form->createView(),
            'page_title'  => 'New Admin',
            'action_path' => $new_url,
            'cancel_path' => $redirect_url,
            'submit_button_title'  => 'Create Admin'
        ));
    }

    /**
     * Edit an Admin
     *
     * @Security( "has_role('ROLE_APP_ADMIN') or has_role('ROLE_HR_ADMIN') or has_role('ROLE_RCD_ADMIN') or has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_MANAGE_ADMINS') or has_role('ROLE_STAFF_ATTENDANCE_ADMIN') or has_role('ROLE_MANAGE_ADMINS_ASHRAMITES') ")
     * @param Request $request
     * @param $user_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @internal param string $payrollEntity_id The unique id of the payroll entity that is to be deleted.
     */
    public function editAction(Request $request,$user_id)
    {

        $this->get("isha_breadcrumbs.factory")->update('Edit Admin');
        $config = $this->container->getParameter( 'isha_admin.config' );

        $user_class = $config['user_class'];

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository($user_class)->findOneById($user_id);

        $form = $this->createForm(AdminType::class,$user);
        $form->handleRequest($request);

        $edit_url = $this->generateUrl('edit_admin',array('user_id'=>$user_id));
        $redirect_url = $this->generateUrl('manage_admins');

        if ($form->isSubmitted() and $form->isValid()) {
            $this->get('isha_admin.common_manager')->updateEntity($user);
            return $this->redirect($redirect_url);
        }

        return $this->render('IshaAdminBundle:Common:basic.html.twig', array(
            'form'        => $form->createView(),
            'page_title'  => 'Edit Admin',
            'action_path' => $edit_url,
            'cancel_path' => $redirect_url,
            'submit_button_title'  => 'Save'
        ));
    }


    /**
     *
     * @Security( "has_role('ROLE_APP_ADMIN') or has_role('ROLE_HR_ADMIN') or has_role('ROLE_RCD_ADMIN') or has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_MANAGE_ADMINS') or has_role('ROLE_STAFF_ATTENDANCE_ADMIN') or has_role('ROLE_MANAGE_ADMINS_ASHRAMITES')")
     * Assign admins to departments.
     *
     * @param Request $request
     * @return Response
     */
    public function listAction()
    {
        $this->get("isha_breadcrumbs.factory")->update('Admin Overview');


        $config = $this->container->getParameter( 'isha_admin.config' );
        $user_class = $config['user_class'];
        $coupling = $config['couple_to_person'];
        $roles = $config['roles'];

        $user = $this->getUser();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_HR_ADMIN') == false and $user->hasRole('ROLE_STAFF_ATTENDANCE_ADMIN') and $user->hasRole('ROLE_MANAGE_ADMINS') == false ) {
            // only has access to staff attendance manager
            $users = $this->getDoctrine()->getRepository($user_class)->findByAttendanceManager();
        } else {
            $users = $this->getDoctrine()->getRepository($user_class)->findByAdmin();
        }

        if ($coupling) {
            $suvyaEm = $this->getDoctrine()->getManager('suvya');

            foreach ($users as $user) {

                $user->type = '-';
                if ($user->hasRole('ROLE_HR_ADMIN') or $user->getUserName() == 'fzoet') {
                    $user->type = 'HR ADMIN';
                    continue;
                }

                $person = $suvyaEm->getRepository('IshaYogaCenterBundle:Person')->findOneById($user->getPersonId());
                if ($person != null) {
                    if ($person->getIshaGroupCoordinator() != null) {
                        $user->type = 'GC';
                    } elseif ($person->getIshaCoordinator() != null) {
                        $user->type = 'COORD';
                    } elseif ($person->getIshaDepartmentLead() != null) {
                        $user->type = 'LEAD';
                    } elseif ($person->getAttendanceManager() != null) {
                        $user->type = 'ATTENDANCE MANAGER';
                    }
                }
            }
        }

        return $this->render('IshaAdminBundle:Admin:list.html.twig', array(
            'users' => $users,
            'roles' => $roles
        ));
    }

    /**
     * @Security( "has_role('ROLE_APP_ADMIN') or has_role('ROLE_HR_ADMIN') or has_role('ROLE_RCD_ADMIN') or has_role('ROLE_SUPER_ADMIN') or has_role('ROLE_MANAGE_ADMINS_ASHRAMITES')")
     * @param Request $request
     * @param $user_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @internal param $ishaGroupCoordinator_id
     */
    public function deleteAction(Request $request, $user_id)
    {
        $this->get("isha_breadcrumbs.factory")->update('Remove Admin');

        $config = $this->container->getParameter( 'isha_admin.config' );
        $user_class = $config['user_class'];

        $em = $this->getDoctrine()->getManager();
        $admin = $em->getRepository($user_class)->findOneById($user_id);

        $action_path = $this->generateUrl('delete_admin', array('user_id'=>$user_id));
        $redirect_url = $this->generateUrl('manage_admins');

        if($request->getMethod() == 'POST') {

            $em->remove($admin);
            $em->flush();

            return $this->redirect($redirect_url);
        }

        return $this->render('IshaAdminBundle:Common:remove.html.twig', array(
            'page_title' => 'Remove an Admin',
            'class_name' => 'Admin',
            'entity_description' => $admin->getName(),
            'action_path' => $action_path,
            'cancel_path' => $redirect_url
        ));
    }





}