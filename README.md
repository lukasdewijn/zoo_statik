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

Toegankelijk via `/admin` met authenticatie + optionele 2FA.

### Resources

- **Reservaties** — Overzicht met zoeken, filteren (datumbereik, tijdslot), sorteren. Acties: bekijken, bewerken, annuleren. Toont status als gekleurde badge. Capaciteitsinfo zichtbaar bij bewerken.
- **Tijdsloten** — CRUD voor tijdvensters met start/eindtijd en recurring-toggle.
- **Tijdslotcapaciteiten** — Beheer capaciteit per datum/tijdslot. Inline bewerkbaar. Toont gereserveerd aantal en resterende plaatsen.

### Analytics

Pagina `/admin/visitors-analytics` met een lijndiagram (bezoekers per dag) en configureerbaar datumbereik. De grafiek toont ook op het dashboard.

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