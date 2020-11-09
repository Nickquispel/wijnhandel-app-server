<?php

namespace App\Controller;

use App\Entity\Order;
use App\Reader\ProductReader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class AdminController extends AbstractController
{
    /**
     * @Route ("/admin")
     */
    public function postExcel(Request $request)
    {

        if ($request->isMethod('post')) {

            $file = $request->files->get('fileToUpload');
            $dir =  __DIR__ . '/../../public/excel/';
            $filename = time() . "products.xlsx";
            $path = $dir . $filename;
            $file->move($dir, $filename);


            $entityManager =  $this->getDoctrine()->getManager();


            $reader = new ProductReader($path, $entityManager);

            $result =
                $reader->execute();
            $productQuantity = $reader->getProductQuantity();
        }

        if (isset($result)) {
            return $this->render(
                'admin.html.twig',
                [
                    "success" => $result,
                    "productQuantity" => $productQuantity
                ]
            );
        } else {
            return $this->render(
                'admin.html.twig'
            );
        }
    }

    /**
     * @Route ("/admin/order-email")
     */

    public function sendEmail(Request $request)
    {
        if ($request->isMethod('post')) {

            $data = json_decode($request->getContent(), true);
            $id = $data["id"];

            $order = $this->getDoctrine()
                ->getRepository(Order::class)
                ->find($id);

            $orderLines = $order->getOrderLines();

            $viewOrderLines = [];
            $totalOrderPrice = 0;

            foreach ($orderLines as $orderLines) {
                $qty = $orderLines->getQty();
                $priceEach = $orderLines->getPriceEach();
                $totalPrice = $qty * $priceEach;
                $totalOrderPrice += $totalPrice;
                $viewOrderLines[] = [
                    "productname" => $orderLines->getProductName(),
                    "qty" => $orderLines->getQty(),
                    "priceEach" => $orderLines->getPriceEach(),
                    "totalPrice" => $totalPrice
                ];
            }

            $template = $this->get('twig')->render('/mail.html.twig', [
                "order" => $order,
                "orderlines" => $viewOrderLines,
                "isCustomerEmail" => true,
                "totalOrderPrice" => $totalOrderPrice,
            ]);


            $email = (new Email())
                ->from('info@wijnhandelvanvianen.nl')
                ->to($order->getEmail())
                ->subject('Bevestiging order ' . $id)
                ->html($template);

            $transport = new EsmtpTransport('smtp.vandms.nl', 25, null);
            $transport->setUsername('info@wijnhandelvanvianen.nl');
            $transport->setPassword('lfUcKpgojb');
            $mailer = new Mailer($transport);
            $mailer->send($email);

            $template = $this->get('twig')->render('/mail.html.twig', [
                "order" => $order,
                "orderlines" => $viewOrderLines,
                "isCustomerEmail" => false,
                "totalOrderPrice" => $totalOrderPrice,
            ]);

            $email = (new Email())
                ->from('info@wijnhandelvanvianen.nl')
                ->to('info@wijnblad.nl')
                ->cc('info@wijnhandelvanvianen.nl')
                ->subject('Nieuwe order ontvangen (order '. $id .')')
                ->html($template);

            $transport = new EsmtpTransport('localhost', 25, null);
            $transport->setUsername('info@wijnhandelvanvianen.nl');
            $transport->setPassword('lfUcKpgojb');
            $mailer = new Mailer($transport);
            $mailer->send($email);
        }
        return $this->render(
            'admin.html.twig'
        );
    }
}
