<?php

return [
    'form' => [
        'title' => 'Reserveer je bezoek aan de Zoo!',

        'date' => [
            'label' => 'Datum*',
        ],
        'timeslot' => [
            'label' => 'Tijd*',
            'placeholder' => 'Kies een tijdslot',
        ],
        'visitors' => [
            'title' => 'Bezoekers',
            'addbutton' => '+ Bezoeker toevoegen',
            'label' => 'Bezoeker',

            '',
        ],
        'places_counter' => [
            'places_left' => 'Nog :count plaatsen over',
            'no_places_left' => 'Dit tijdslot is volzet',
        ],
        'reservations_button' => 'Reserveer!',

        'errors' => [
            'timeslot_full' => 'Dit tijdslot is volzet (max :capacity bezoekers).',
            'weekend' => 'De zoo is gesloten in het weekend.',
            'no_availability' => 'Er is geen beschikbaarheid op deze datum.',
        ],
    ],

    'success' => [
        'title' => '✅ Reservatie gelukt!',
        'number_visitors' => 'Bezoekers',
        'new_reservation' => 'Nieuwe reservatie',

        'confirmation_sent' => '📧 We hebben een bevestigingsmail gestuurd naar :email. Controleer ook je spamfolder.',
    ],

    'notifications' => [
        'confirmed' => [
            'subject' => 'Bevestiging van je reservatie',
            'greeting' => 'Hey!',
            'body' => 'Je reservatie is bevestigd.',
            'reservation_code' => 'Reservatiecode: :code',
            'date' => 'Datum: :date',
            'timeslot' => 'Tijdslot: :timeslot',
            'cancel_action' => 'Annuleer via deze link',
            'cancel_notice' => 'Deze annuleerlink is tijdelijk geldig.',
        ],
        'reminder' => [
            'subject' => 'Herinnering: je bezoek aan de Zoo is morgen!',
            'greeting' => 'Hey!',
            'body' => 'Dit is een vriendelijke herinnering dat je morgen een bezoek gepland hebt aan de Zoo.',
            'reservation_code' => 'Reservatiecode: :code',
            'date' => 'Datum: :date',
            'timeslot' => 'Tijdslot: :timeslot',
            'goodbye' => 'Tot morgen!',
        ],
        'cancelled_by_admin' => [
            'subject' => 'Je reservatie is geannuleerd',
            'greeting' => 'Hey!',
            'body' => 'Het spijt ons, maar je reservatie is geannuleerd door de beheerder.',
            'reservation_code' => 'Reservatiecode: :code',
            'date' => 'Datum: :date',
            'timeslot' => 'Tijdslot: :timeslot',
            'new_reservation' => 'Boek een nieuwe reservatie',
            'apology' => 'Onze excuses voor het ongemak.',
        ],
    ],

    'email' => 'Email',
    'firstname' => 'Voornaam',
    'lastname' => 'Achternaam',
    'delete' => 'Verwijder',
    'subscription_nr' => 'Abonnementsnummer',
    'date' => 'Datum',
    'reservation_code' => 'Reserveringscode',
    'visitors' => 'Bezoekers',
    'time' => 'Tijd',
];
