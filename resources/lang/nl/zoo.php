<?php

return [
    'form' => [
        'title' => 'Reserveer je bezoek aan de Zoo!',

        'date' => [
            'label' => 'Datum*',
        ],
        'timeslot'=>[
            'label' => 'Tijd*',
            'placeholder' => 'Kies een tijdslot',
        ],
        'visitors'=>[
            'title' => 'Bezoekers',
            'addbutton' => '+ Bezoeker toevoegen',
            'label' => 'Bezoeker',

            ''
        ],
        'places_counter'=>[
            'places_left' => 'Nog :count plaatsen over',
            'no_places_left' => 'Dit tijdslot is volzet',
        ],
        'reservations_button' => 'Reserveer!',

        'errors' => [
            'timeslot_full' => 'Dit tijdslot is volzet (max :capacity bezoekers).',
],
    ],

    'success' => [
        'title' => '✅ Reservatie gelukt!',
        'number_visitors' => 'Bezoekers',
        'new_reservation' => 'Nieuwe reservatie',

        'confirmation_sent' =>
            '📧 We hebben een bevestigingsmail gestuurd naar :email. Controleer ook je spamfolder.',
    ],



    'email' => 'Email',
    'firstname' => 'Voornaam',
    'lastname' => 'Achternaam',
    'delete' => 'Verwijder',
    'subscription_nr'=>'Abonnementsnummer',
    'date' => 'Datum',
    'reservation_code' => 'Reserveringscode',
    'visitors' => 'Bezoekers',
    'time' => 'Tijd',
];
