# Review 12/2/2026

- zet je persoonlijke projectjes op je persoonlijk git account, dan komt er geen wildgroei in de statik repos
- gebruik DDEV en commit .ddev mee
  - het is de bedoeling om met een gedeelde ddev setup sneller andere devs het project laten op te zetten.
- probeer je PHP versie in composer juist te houden: staat nu op 8.2 maar werkt enkel met 8.4
- voeg een nvmrc (node version manager) file toe met de node versie
- Ik krijg het niet aan de praat

## Database:
 
- Probeer steeds je model te gebruiken om te query'en ipv direct met db table names.
  - DB::table('time_slot_capacities')->upsert(...
  - omdat je dat makkelijker kan refactoren, minder kans op typos, type safety hebt, etc
  - omdat je dan gebruik kan maken van relations en zelf geen joins moet maken
- Gebruik je model relations ipv zelf manueeel joins te schrijven, bv met `whereHas` 
- Learning: Kijk eens naar query scopes voor veelvoorkomende condities herbruikbaar te maken.

## Logica & design:

- CleanupExpiredReservations: Completed status lijkt mij onnodig: confirmed && (reservation_date < now()) === completed
  - Dit is minder error prone dan jobs om statussen juist te zetten
- GenerateTimeSlotCapacities: 
  - Roept vragen op zonder documentatie: waarom default days = 360? Waarom dient deze job?
  - Als deze job niet uitgevoerd wordt, faalt de app.
  - Zie verder: TimeSlotCapacities als een model is geen goed design
- TimeSlotCapacities model:
  - Voor elk tijdslot + date een record genereren om 99,999999% vd gevallen een capacity van 200 in te steken, lijkt mij overkill, omdat:
    - als er geen TimeSlotCapacities record is, faalt de app (bv als de job queue faalt)
    - dit was geen gevraagd scenario om uit te werken, wat voor heel wat extra complexiteit zorgt
    - als dat scenario toch nodig zou zijn, zou ik eerder opteren om een default value te zetten en dan een model met exceptions. 
        - dat model zou mss ook niet aan een tijdslot hangen maar bv een begin en een einddatum kunnen hebben + een capacity    
    - wat is het voordeel van jou aanpak?    

## Models:

- Voor models die geen secrets bevatten, ben ik voorstander van $guarded = []; ipv protected $fillable = [....]
  - als er een veld niet ingevuld is in $filled, bewaart Filament dit niet zonder iets te zeggen. Very annoying bugs...
- TimeSlotCapacity:
  - capacityFor en hasCapacityRecord enz, zou ik eerder met scopes implementeren, dat is meer laravelly.
  - reservedCountsByDate & availableDates geen DB::table gebruiken (dat is écht last resort). Gebruik je model en relations!
  - availableDates lijkt mij al eerder iets voor een service ipv in het model

## Controllers:

- Gebruik FormRequest om je validation in te steken
- SubscriptionNumber lijkt niet te checken of het lidnummer well-formed is. Op het eerste zicht zou ik kunnen eendert wat tussen de cijfers schrijven
- ReservationController & ReservationSuccessController: 
  - FormRequests gebruiken
  - Route binding gebruiken op show & cancel. Laravel kan die query zelf doen.
  - contact_email check in cancel kan zelfs ook in FormRequest (niet-triviaal maar wel interessant ;-)
- ReservationService: 
  - Je checkt eerst of er nog plaats is en dan maak je de reservatie, daarna check je niet meer of je over de limiet zit.
  - Het zou kunnen dat er een race condition is, waarbij tussen de check dat er plaats is en de reservation create een ander proces toch nog een reservatie maakt.
  - Dus ofwel moet je na create nog eens checken en dan evt rollback doen, of bij het begin van de transaction een update lock nemen op reservations
- ReservationCancelController:
  - Temporarily signed routes: nice!

## API

- Learning: Voeg swagger documentatie toe l5-swagger package, bv: https://api.museumpassmusees.be/swagger/#/Backoffice%20UI/getAdminUsers
- Gebruik API resources https://laravel.com/docs/12.x/eloquent-resources
  - soms wel en soms niet ;-)

## General remarks

- Kortere functies zijn beter. Splits je functies wat meer op of gebruik de framework features om logica op de "juiste" plaats te doen.
- Vermijd het gebruik van DB facade, gebruik models 
- Denk goed na over data model, bedenk verschillende opties en hun pro's en con's.
- Do not overengineer now for future imagined requirements
- Probeer architectuurkeuzes over je heel project gelijk te houden, bv als je ergens iets gebruikt, gebruik het dan overal -> voor 1 request api resources dan doe je het voor alles
