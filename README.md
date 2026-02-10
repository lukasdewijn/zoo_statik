# Zoo Reservatiesysteem

Een reservatieplatform voor dierentuinbezoeken gebouwd met Laravel 12, Livewire 4, en Filament 5. Bezoekers kunnen online tijdsloten reserveren, bevestigingsmails ontvangen en reservaties annuleren. Beheerders hebben een volledig dashboard met capaciteitsbeheer, analytics en notificaties.

## Tech Stack

| Laag | Technologie |
|------|-------------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Livewire 4, Flux 2.9, Tailwind CSS 4, Alpine.js |
| Admin Panel | Filament 5 |
| Authenticatie | Laravel Fortify (met 2FA) |
| Database | SQLite (dev) / MySQL (prod) |
| Mail | Postmark (stream: `zoomail`) |
| Testing | Pest 4 |
| Build | Vite 7 |

## Installatie

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan db:seed          # maakt standaard tijdsloten + testgebruiker
```

Ontwikkelen (server + queue + logs + Vite tegelijk):

```bash
composer dev
```

## Projectstructuur

```
app/
├── Console/Commands/       # Artisan commando's (capaciteit genereren, reminders, cleanup, rapport)
├── Enums/                  # ReservationStatus (confirmed, cancelled, completed)
├── Filament/               # Admin panel (resources, pages, widgets, filters)
├── Http/
│   ├── Controllers/Api/V1/ # REST API (tijdsloten, beschikbaarheid, reservaties)
│   ├── Controllers/        # Web controllers (succes- en annuleerpagina's)
│   ├── Requests/           # Form request validation
│   └── Resources/Api/V1/   # API resource transformers
├── Models/                 # Eloquent modellen
├── Notifications/          # E-mail notificaties
├── Providers/              # Service & Filament providers
├── Rules/                  # Custom validatieregels
└── Services/               # Business logic (ReservationService)
```

## Datamodellen

### Reservation
Centrale entiteit. Elke reservatie heeft een unieke `public_code` (UUID), een datum, een tijdslot en een contacte-mail. Status: `confirmed` / `cancelled` / `completed`.

**Relaties:** `belongsTo(TimeSlot)`, `hasMany(Visitor)`

### TimeSlot
Tijdvensters (bv. 10:00-12:00). Heeft een `recurring` vlag die aangeeft of het tijdslot dagelijks herhaalt. Geeft automatisch een `label` attribuut terug ("HH:MM - HH:MM").

**Relaties:** `hasMany(Reservation)`, `hasMany(TimeSlotCapacity)`

### TimeSlotCapacity
Capaciteit per datum per tijdslot (standaard 200). Houdt bij hoeveel plaatsen beschikbaar zijn. Heeft helper-methodes: `remainingCapacity()`, `reservedCount()`, `availableDates()`.

Wanneer een capaciteitsrecord verwijderd wordt, worden alle gekoppelde bevestigde reservaties automatisch geannuleerd en ontvangen bezoekers een annuleringsmail.

**Relaties:** `belongsTo(TimeSlot)`

### Visitor
Bezoeker gekoppeld aan een reservatie. Velden: voornaam, achternaam en optioneel abonnementsnummer.

**Relaties:** `belongsTo(Reservation)`

## API Endpoints

Base URL: `/api/v1/` — Rate limit: 60 req/min per IP

| Methode | Endpoint | Beschrijving |
|---------|----------|--------------|
| `GET` | `/ping` | Health check |
| `GET` | `/time-slots` | Alle tijdsloten ophalen |
| `GET` | `/availability?date=Y-m-d` | Beschikbaarheid per tijdslot voor een datum |
| `GET` | `/available-dates` | Datums met beschikbaarheid (90 dagen vooruit) |
| `POST` | `/reservations` | Nieuwe reservatie aanmaken |
| `GET` | `/reservations/{public_code}` | Reservatie opvragen |
| `POST` | `/reservations/{public_code}/cancel` | Reservatie annuleren |

### Reservatie aanmaken — `POST /reservations`

```json
{
  "date": "2026-03-15",
  "time_slot_id": 1,
  "contact_email": "bezoeker@example.com",
  "visitors": [
    { "first_name": "Jan", "last_name": "Peeters", "subscription_number": null },
    { "first_name": "Marie", "last_name": "Janssens", "subscription_number": "1234567897" }
  ]
}
```

## Webroutes

| Route | Beschrijving |
|-------|--------------|
| `/reservation` | Reservatieformulier (Livewire) |
| `/reservations/success/{code}` | Bevestigingspagina na reservatie |
| `/reservations/cancel/{code}` | Annuleerpagina (signed URL) |
| `/reservations/cancelled/{code}` | Bevestiging van annulering |
| `/admin` | Filament admin panel |

## Artisan Commando's

| Commando | Beschrijving |
|----------|--------------|
| `timeslots:generate-capacities` | Genereert capaciteitsrecords voor recurring tijdsloten (standaard 360 dagen, weekdagen) |
| `reservations:send-reminders` | Stuurt herinneringsmails voor reservaties van morgen |
| `reservations:cleanup-expired` | Markeert verlopen reservaties als `completed` |
| `analytics:daily-report` | Stuurt dagelijks bezoekersrapport naar admin |

Opties voor capaciteitsgeneratie:
```bash
php artisan timeslots:generate-capacities --days=360 --include-weekends
```

## Admin Panel (Filament)

Toegankelijk via `/admin` met authenticatie + optionele 2FA. Kleurschema: amber.

### Bestandsstructuur

```
app/Filament/
├── Filters/
│   └── DateRangeFilter.php            # Herbruikbaar datumbereik-filter (van/tot)
├── Pages/
│   └── VisitorsAnalytics.php          # Analytics pagina met lijndiagram
├── Resources/
│   ├── Reservations/
│   │   ├── ReservationResource.php    # Hoofdresource (eager loads timeSlot, counts visitors)
│   │   ├── Pages/
│   │   │   ├── ListReservations.php   # Overzichtslijst
│   │   │   ├── CreateReservation.php  # Aanmaakpagina
│   │   │   ├── ViewReservation.php    # Detailweergave (read-only infolist)
│   │   │   └── EditReservation.php    # Bewerkpagina
│   │   ├── Schemas/
│   │   │   ├── ReservationForm.php    # Formulier met capaciteitsberekening
│   │   │   └── ReservationInfolist.php# Read-only weergave met bezoekerlijst
│   │   └── Tables/
│   │       └── ReservationsTable.php  # Tabelconfiguratie met annuleeractie
│   ├── TimeSlots/
│   │   ├── TimeSlotResource.php       # Hoofdresource
│   │   ├── Pages/                     # List, Create, Edit
│   │   ├── Schemas/
│   │   │   ├── TimeSlotForm.php       # start_time, end_time (HH:MM), recurring toggle
│   │   │   └── TimeSlotsTable.php     # Tabel met verwijderbeveiliging
│   │   └── ...
│   └── TimeSlotCapacities/
│       ├── TimeSlotCapacityResource.php # Hoofdresource
│       ├── Pages/                       # List, Create (duplicaatcheck), Edit (duplicaatcheck)
│       ├── Schemas/
│       │   ├── TimeSlotCapacityForm.php # datum, tijdslot (inline aanmaak), capaciteit
│       │   └── TimeSlotCapacitiesTable.php # Tabel met subquery voor reserved_count
│       └── ...
└── Widgets/
    └── ReservationsTimeSeries.php     # Lijndiagram bezoekers/dag (polling 60s)
```

### ReservationResource

**Tabel** (`ReservationsTable.php`) — Standaard gesorteerd op datum.

| Kolom | Details |
|-------|---------|
| `public_code` | Doorzoekbaar, kopieerbaar, afgekapt tot 8 tekens |
| `date` | Sorteerbaar, format "D d M Y" |
| `timeSlot.label` | Tijdslot weergave (bv. "10:00 - 12:00") |
| `visitors_count` | Aantal bezoekers, sorteerbaar |
| `status` | Badge: groen (confirmed), rood (cancelled), blauw (completed) |
| `created_at` / `updated_at` | Standaard verborgen, aan te zetten |

**Filters:** DateRangeFilter (van/tot) + SelectFilter op tijdslot.

**Acties per rij:**
- **Bekijken** — Opent read-only infolist
- **Bewerken** — Uitgeschakeld als status `cancelled` is
- **Annuleren** — Rode knop met bevestigingsdialoog, zet status op `cancelled` en slaat `cancelled_at` timestamp op

**Formulier** (`ReservationForm.php`) — Twee secties, beide uitgeschakeld bij geannuleerde reservaties:

1. **Reservatie** — `public_code` (read-only), `date` (reactive datepicker), `time_slot_id` (reactive select met helper text die resterende capaciteit toont: "Resterend: X plaatsen (capaciteit: Y, gereserveerd: Z)"), `status` (enum select)
2. **Bezoekers** — Repeater met voornaam, achternaam (verplicht) en abonnementsnummer (optioneel, mod97-validatie). Minimum 1, maximum = resterende capaciteit van het gekozen tijdslot.

**Infolist** (`ReservationInfolist.php`) — Read-only weergave met drie secties:
1. **Reservatie** — public_code (kopieerbaar), datum, tijdslot, aantal bezoekers
2. **Bezoekers** — Repeater met naam en abonnementsnummer (badge, "–" als leeg)
3. **Metadata** — created_at en updated_at (standaard ingeklapt)

### TimeSlotResource

**Tabel** (`TimeSlotsTable.php`) — Gesorteerd op starttijd.

| Kolom | Details |
|-------|---------|
| `start_time` | Sorteerbaar |
| `end_time` | Sorteerbaar |
| `recurring` | Icoon (boolean) |

**Verwijderbeveiliging:** Bij verwijderen wordt `hasFutureReservations()` gecheckt. Heeft het tijdslot toekomstige bevestigde reservaties, dan wordt verwijdering geblokkeerd met een foutmelding.

**Formulier** (`TimeSlotForm.php`) — `start_time` en `end_time` (regex `HH:MM`), `recurring` toggle (standaard aan).

### TimeSlotCapacityResource

**Tabel** (`TimeSlotCapacitiesTable.php`) — Gesorteerd op datum. Bevat een **subquery** die `reserved_count` berekent via een join op reservations + visitors (alleen status confirmed).

| Kolom | Details |
|-------|---------|
| `date` | Sorteerbaar |
| `timeSlot.label` | Tijdslot weergave |
| `capacity` | **Inline bewerkbaar** (TextInputColumn), numeriek, min 0 |
| `reserved_count` | Aantal gereserveerde bezoekers (subquery) |
| `left` | Berekend: `max(0, capacity - reserved_count)` |

**Filters:** DateRangeFilter + SelectFilter op tijdslot.

**Formulier** (`TimeSlotCapacityForm.php`) — Datum, tijdslot (met inline aanmaak van nieuw tijdslot), capaciteit (standaard 200).

**Duplicaatpreventie:** Zowel `CreateTimeSlotCapacity` als `EditTimeSlotCapacity` controleren voor het opslaan of er al een record bestaat voor dezelfde datum + tijdslot combinatie. Bij duplicaat wordt een foutmelding getoond en de actie gestopt.

### DateRangeFilter

Herbruikbaar filter (`Filters/DateRangeFilter.php`) met twee DatePickers (van/tot). Configureerbare kolomnaam (standaard `date`). Wordt gebruikt door zowel ReservationsTable als TimeSlotCapacitiesTable.

### VisitorsAnalytics pagina

Pagina op `/admin/visitors-analytics` met:
- Datumbereik selectie (standaard: 30 dagen terug tot 30 dagen vooruit)
- Automatische swap als "van" > "tot"
- Dispatcht `analyticsRangeUpdated` event naar de widget

### ReservationsTimeSeries widget

Lijndiagram (Chart.js) dat bezoekers per dag toont. Verschijnt op het dashboard en de analytics pagina.

- **Polling:** Elke 60 seconden automatisch vernieuwen
- **Data:** Join op visitors + reservations, filtert op confirmed/completed status
- **Vult ontbrekende dagen** aan met 0
- **Y-as:** Integers, begint bij 0
- **Luistert** naar `analyticsRangeUpdated` event van de analytics pagina

## E-mail Notificaties

| Notificatie | Trigger | Inhoud |
|-------------|---------|--------|
| `ReservationConfirmed` | Na reservatie aanmaken | Bevestiging + reservatiecode + annuleringslink (7 dagen geldig) |
| `ReservationReminder` | Dagelijks commando | Herinnering voor bezoek morgen |
| `ReservationCancelledByAdmin` | Capaciteit verwijderd / admin annulering | Excuusmail + link om opnieuw te reserveren |
| `DailyVisitorReport` | Dagelijks commando | Bezoekersstatistieken per tijdslot met bezettingspercentages |

## Validatieregels

- **SubscriptionNumber** — Optioneel. Indien ingevuld: exact 10 cijfers met mod97 checksum (Europees abonnementsnummer).
- **DateHasAvailability** — Controleert of de gekozen datum minstens 1 tijdslot met resterende capaciteit heeft.

## Belangrijke Technische Details

- **Race condition preventie** — De `ReservationService` gebruikt pessimistic locking (`SELECT ... FOR UPDATE`) in een database transactie om dubbele boekingen te voorkomen.
- **E-mail veiligheid** — Bevestigingsmails worden buiten de transactie verstuurd, zodat een mailfout de reservatie niet terugdraait.
- **Signed URLs** — Annuleringslinks in e-mails zijn cryptografisch ondertekend en 7 dagen geldig.
- **Capaciteitscascade** — Verwijderen van een capaciteitsrecord annuleert automatisch alle gekoppelde reservaties en stuurt notificaties.
- **Standaardcapaciteit** — 200 per tijdslot. Wordt automatisch aangemaakt bij de eerste reservatie als er geen record bestaat.

## Taal

De applicatie is volledig in het Nederlands. Vertalingen staan in `resources/lang/nl/zoo.php`.

## Standaard Tijdsloten (Seeder)

| Tijdslot |
|----------|
| 10:00 - 12:00 |
| 12:00 - 14:00 |
| 14:00 - 16:00 |
| 16:00 - 18:00 |

## Environment Variabelen

| Variabele | Beschrijving |
|-----------|--------------|
| `MAIL_MAILER` | Mail driver (standaard: `log`) |
| `MAIL_FROM_ADDRESS` | Afzenderadres |
| `ADMIN_EMAIL` | E-mailadres voor dagelijks rapport |
| `POSTMARK_TOKEN` | Postmark API key |

## Tests

```bash
php artisan test
# of
composer test    # lint + tests
```

Test factories beschikbaar voor: `Reservation`, `TimeSlot`, `TimeSlotCapacity`, `Visitor`.