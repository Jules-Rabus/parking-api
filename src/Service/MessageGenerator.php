<?php

namespace App\Service;

use App\Entity\Message;
use App\Entity\Reservation;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MessageGenerator
{
    private string $grettingStart;
    private string $grettingEnd;
    private string $content = '';
    private Message $message;
    private Reservation $reservation;

    public function __construct(
        #[Autowire(env: 'LATITUDE')] private readonly string $latitude,
        #[Autowire(env: 'LONGITUDE')] private readonly string $longitude,
    ) {
        $this->greeting();
    }

    public function setMessage(Message $message): void
    {
        $this->message = $message;
    }

    public function setReservation(Reservation $reservation): void
    {
        $this->reservation = $reservation;
    }

    public function greeting(): void
    {
        // Instance unique de DateTime pour le moment actuel
        $now = new \DateTime();
        $timestamp = $now->getTimestamp();

        // Récupération de l'heure du coucher du soleil
        $sunInfo = date_sun_info($timestamp, $this->latitude, $this->longitude);
        $sunset = (new \DateTime())->setTimestamp($sunInfo['sunset']);

        // Définition des horaires clés en clonant l'instance actuelle
        $midi = (clone $now)->setTime(12, 0, 0);
        $apresMidi = (clone $now)->setTime(16, 0, 0);
        $soir = (clone $now)->setTime(19, 0, 0);
        $day = $now->format('l');

        // Initialisation de la salutation en fonction de l'heure
        if ($now > $sunset || $now > $soir) {
            $start = 'Bonsoir';
            $end = 'Bonne soirée';
        } else {
            $start = 'Bonjour';
            $end = 'Bonne journée';
        }

        // Ajustement de la formule de politesse selon d'autres critères
        if ($now > $midi && $now < $apresMidi) {
            $end = 'Bonne après midi';
        }
        if ($now > $apresMidi && $now < $soir && $now < $sunset) {
            $end = 'Bonne fin de journée';
        }
        if (
            ('Friday' === $day && ($now > $sunset && $now > $soir))
            || ('Saturday' === $day && $now < $apresMidi)
        ) {
            $end = 'Bon week-end';
        }
        if ('Sunday' === $day && $now < $midi) {
            $end = 'Bon dimanche';
        }
        if ('Monday' === $day && $now > $midi && !($now < $sunset || $now < $soir)) {
            $end = 'Bonne semaine';
        }
        if (12 === (int) $now->format('m') && (int) $now->format('d') > 20) {
            $end = 'Bonnes fêtes';
        }
        if (1 === (int) $now->format('m') && (int) $now->format('d') < 10) {
            $end = 'Bonne année';
        }

        $this->grettingStart = $start.',%0a%0a';
        $this->grettingEnd = '%0a%0a'.$end.' à vous,%0aCordialement M. Rabus';
    }

    public function getContent(): string
    {
        return $this->grettingStart.$this->content.$this->grettingEnd;
    }

    public function messageCode(): void
    {
        $this->content = "Votre code d'accès sera le : ". 0000; /* TODO: Implémenter les codes d'accès */

        if ($this->message->getPhone()->getOwner()->getReservationsCount() < 2) {
            $this->content = $this->content." , il sera également valable pour votre retour.%0A%0AJe vous rappelle également que le paiement se fait soit par chèque à l'ordre de M.Rabus/Mme Rabus ou en espèces à l'arrivée sur le parking via des enveloppes pré-remplies.";
        }
    }

    private function getVehicleCount(): string
    {
        if ($this->reservation->getVehicleCount() > 1) {
            return 'de '.$this->reservation->getVehicleCount().' places';
        }

        return "d'une place";
    }

    public function contentExplication(): void
    {
        $this->content = "Le parking se situe entre le 17 et le 19 rue du moulin à Tillé (portail noir) à 650 mètres à pied de l'aéroport.%0AL'accès au parking se fait via un portail motorisé à digicode. ".
            "Je vous remercie de me recontacter par sms/mail/telephone 48h00 avant votre arrivée au parking afin d'obtenir votre code d'accès, il sera également valable pour votre retour.%0A".
            "Le paiement s'éffectue à votre arrivée au moyen d'enveloppes pré-remplies disponibles à l'entrée du parking et à déposer dans la boite au lettre jaune et verte situé le long du grillage.%0A".
            "Le paiement se fait soit par chèque à l'ordre de M.Rabus/Mme Rabus soit en espèces.%0AVous restez en possession des clés de votre véhicule.%0ASi vous avez des questions n'hésitez pas.";
    }

    public function contentReservation()
    {
        $this->content =
            $this->content.'Je vous confirme votre réservation '.
            $this->getVehicleCount().' de parking du '.$this->reservation->getStartDate()->format('d/m').' au '.
            $this->reservation->getStartDate()->format('d/m').' au tarif de '.$this->reservation->getPrice().'€.';

        if (false) { /* TODO: Implémenter les codes d'accès : $this->Reservation->getDateArrivee()->diff($aujourdhui)->days < 5
            $content = $this->content;
            $this->MessageCode();
            $this->content = $content . "%0A%0A" . $this->content;

            // Dans ce cas on veut enregistrer qu'on a donne le code
            $this->Reservation->setCodeDonne(true);

            $this->EntityManager->persist($this->Reservation);
            $this->EntityManager->flush();
            */
        } else {
            $this->content = $this->content."%0A%0AJe vous remercie de me recontacter par sms/mail/telephone 48h00 avant votre arrivée au parking afin d'obtenir votre code d'accès, il sera également valable pour votre retour.";
        }
    }

    public function contentCancellation()
    {
        $this->content = "Je prends bien note de l'annulation de votre réservation ".$this->getVehicleCount().
            ' de parking du '.$this->reservation->getStartDate()->format('d/m').' au '.
            $this->reservation->getEndDate()->format('d/m');
    }
}
