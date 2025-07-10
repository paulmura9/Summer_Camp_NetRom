<?php

namespace App\Controller;

use App\Repository\FestivalRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Purchase;
use Doctrine\ORM\EntityManagerInterface;
class CalendarController extends AbstractController
{
    #[Route('/calendar', name: 'calendar')]
    public function calendar(Request $request, FestivalRepository $festivalRepo, EntityManagerInterface $em): Response
    {
        $monthRaw = $request->query->get('month');
        $yearRaw = $request->query->get('year');

        $month = ctype_digit($monthRaw ?? '') ? (int)$monthRaw : (int)date('m');
        $year = ctype_digit($yearRaw ?? '') ? (int)$yearRaw : (int)date('Y');

        $currentMonth = \DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-01', $year, $month));
        $daysInMonth = (int)$currentMonth->format('t');
        $firstDayOfWeek = (int)$currentMonth->format('N');
        $prevMonth = (clone $currentMonth)->modify('-1 month');
        $nextMonth = (clone $currentMonth)->modify('+1 month');

        $startDate = $currentMonth->format('Y-m-01');
        $endDate = $currentMonth->format('Y-m-t');

        $festivals = $festivalRepo->createQueryBuilder('f')
            ->where('f.start_date BETWEEN :start AND :end')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getQuery()
            ->getResult();

        $userFestivalIds = [];

        if ($this->getUser()) {
            $purchases = $em->getRepository(Purchase::class)->findBy(['user' => $this->getUser()]);
            foreach ($purchases as $purchase) {
                $festival = $purchase->getFestival();
                if ($festival) {
                    $userFestivalIds[] = $festival->getId();
                }
            }
        }

        $festivalsByDay = [];
        foreach ($festivals as $festival) {
            $day = (int)$festival->getStartDate()->format('j');
            $festivalsByDay[$day][] = $festival;
        }

        return $this->render('calendar/calendar.html.twig', [
            'currentMonth' => $currentMonth,
            'daysInMonth' => $daysInMonth,
            'firstDayOfWeek' => $firstDayOfWeek,
            'festivalsByDay' => $festivalsByDay,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'userFestivalIds' => $userFestivalIds,
        ]);
    }

}
