<?php
require_once 'config.php';

// Tymczasowo zwiększamy limit czasu i pamięci dla skryptu, bo będzie przetwarzał aż 1000 fiszek!
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

echo "<style>body { font-family: sans-serif; line-height: 1.6; background: #f4f0ff; color: #333; padding: 20px; }</style>";
echo "<h2>🚀 Instalacja systemu LearnIt</h2>";

mysqli_query($conn, "DROP DATABASE IF EXISTS $dbname");
$sql_db = "CREATE DATABASE $dbname DEFAULT CHARACTER SET utf8 COLLATE utf8_polish_ci";

if (mysqli_query($conn, $sql_db)) {
    echo "✅ Baza danych <b>$dbname</b> została utworzona od zera.<br>";
    mysqli_select_db($conn, $dbname);
}

$queries = [
    "CREATE TABLE rola (
        id_roli INT PRIMARY KEY AUTO_INCREMENT,
        nazwa VARCHAR(30) NOT NULL UNIQUE
    ) ENGINE=InnoDB",

    "CREATE TABLE uzytkownik (
        id_uzytkownika INT PRIMARY KEY AUTO_INCREMENT,
        id_roli INT NOT NULL,
        login VARCHAR(50) UNIQUE NOT NULL,
        haslo VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL,
        telefon VARCHAR(20),
        imie VARCHAR(50),
        nazwisko VARCHAR(50),
        data_urodzenia DATE,
        czy_aktywny BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (id_roli) REFERENCES rola(id_roli)
    ) ENGINE=InnoDB",

    "CREATE TABLE kategoria (
        id_kategorii INT PRIMARY KEY AUTO_INCREMENT,
        nazwa VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB",

    "CREATE TABLE zestaw (
        id_zestawu INT PRIMARY KEY AUTO_INCREMENT,
        id_kategorii INT NOT NULL,
        id_uzytkownika INT NOT NULL,
        tytul VARCHAR(100) NOT NULL,
        data_utworzenia TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        opis TEXT,
        liczba_fiszek INT DEFAULT 0,
        FOREIGN KEY (id_kategorii) REFERENCES kategoria(id_kategorii) ON DELETE CASCADE,
        FOREIGN KEY (id_uzytkownika) REFERENCES uzytkownik(id_uzytkownika) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    "CREATE TABLE fiszka (
        id_fiszki INT PRIMARY KEY AUTO_INCREMENT,
        id_zestawu INT NOT NULL,
        pojecie VARCHAR(255) NOT NULL,
        definicja TEXT NOT NULL,
        FOREIGN KEY (id_zestawu) REFERENCES zestaw(id_zestawu) ON DELETE CASCADE
    ) ENGINE=InnoDB"
];

foreach ($queries as $sql) {
    if (mysqli_query($conn, $sql)) {
        echo "✅ Struktura tabeli utworzona poprawnie.<br>";
    } else {
        die("❌ Błąd przy tworzeniu tabeli: " . mysqli_error($conn));
    }
}

echo "<h3>📦 Wypełnianie danymi startowymi...</h3>";

mysqli_query($conn, "INSERT INTO rola (id_roli, nazwa) VALUES (1, 'admin'), (2, 'user'), (3, 'guest')");
echo "• Dodano role systemowe.<br>";

$jezyki = [
    'Angielski', 'Hiszpański', 'Niemiecki', 'Francuski', 'Włoski', 'Chiński', 'Japoński', 
    'Rosyjski', 'Portugalski', 'Niderlandzki', 'Szwedzki', 'Norweski', 'Duński', 
    'Fiński', 'Czeski', 'Słowacki', 'Polski', 'Ukraiński', 'Arabski', 'Turecki', 
    'Koreański', 'Węgierski', 'Grecki', 'Hebrajski', 'Indonezyjski', 'Wietnamski', 'Inna'
];

foreach ($jezyki as $j) {
    mysqli_query($conn, "INSERT INTO kategoria (nazwa) VALUES ('$j')");
}
echo "• Dodano " . count($jezyki) . " kategorii językowych.<br>";

$hashed_admin = password_hash('admin123', PASSWORD_DEFAULT);
mysqli_query($conn, "INSERT INTO uzytkownik (id_roli, login, haslo, email, imie, nazwisko, czy_aktywny) 
    VALUES (1, 'admin', '$hashed_admin', 'admin@learnit.pl', 'Weronika', 'Kusideł', 1)");
echo "• Utworzono konto Administratora.<br>";


// --- 1. POBIERANIE TWOICH 1000 SŁÓWEK ---
$surowy_tekst = <<<EOT
name
nazwa, nazwisko
very
bardzo
to
do, ku, na, po, za, dla, przy, aby, żeby
through
przez
and
i
just
tylko, właśnie
a
stawiane przez rzeczownikiem zaczynającym się spółgłoską
form
forma, tworzyć
in
w
sentence
zdanie
is
jest w 3 osobie liczby pojedynczej
it
to
great
świetnie
think
myśleć, uważać
you
ty
say
mówić, powiedzieć
that
że, ten ,tamten , to , który
help
pomoc, pomagać
he
on
low
niski, cichy, nisko, cicho, przygnębiony
was
być w czasie przeszłym w 1. i 3. osobie liczby pojedynczej
line
linia, lina, kolejka, rząd
for
dla, do , po
differ
różnic się
on
na
turn
skręcić, zakręt, obrót
are
są, jesteś
cause
przyczyna, powód, pot: ponieważ
with
z
much
dużo
as
kiedy, gdy ,ponieważ, tak jak
mean
znaczyć, skąpy, złośliwy, średni
i
ja
before
przed
his
jego
move
ruszać, ruch
they
oni
right
prawy, poprawny, dobry
be
być
boy
chłopiec
at
przy, w , o , na
old
stary
one
jeden
too
także , za
have
mieć
same
ten sam, taki sam
this
ten, ta
tell
powiedzieć
from
od, z
does
robić w 3. osobie liczby pojedynczej
or
albo
set
komplet, grupa
had
miał , mieliśmy
three
trzy
by
przez
want
chcieć
hot
gorący
air
powietrze
word
słowo
well
cóż, dobrze
but
ale
also
także
what
co
play
grać, gra
some
kilka, niektóre
small
małe
we
my
end
koniec, kończyć
can
umieć, potrafić
put
położyć
out
na zewnątrz
home
dom
other
inne
read
czytać
were
byli, byłeś
hand
dłoń
all
wszystko
port
port
there
tam
large
olbrzymi
when
kiedy, gdy
spell
zaklęcie, literować
up
na
add
dodać
use
używać, użycie
even
nawet, równy
your
twój
land
ziemia, lądować
how
jak
here
tutaj
said
powiedział
must
musieć
an
stawiamy przed samogłoską lub przed niemym h
big
duży
each
każdy
high
wysoki, wysoko
she
ona
such
taki
which
który
follow
stosować sie do, iść za, śledzić
do
robić lub operator w present simple
act
działać, ustawa
their
ich
why
dlaczego
time
czas
ask
pytac
if
jeśli
men
mężczyzna, człowiek
will
będzie
change
zmiana, zmieniać
way
sposób, droga
went
poszedł, poszli
about
o , około
light
światło, zapalać, jasny, lekki
many
wiele, dużo
kind
rodzaj
then
potem, wtedy
off
zależy co jest przed np. i am off mam wolne w pracy !!!!
them
nich
need
potrzebować, potrzeba
write
pisać
house
dom
would
np, zrobiłby, dałabym itp. he would do, i would give
picture
obraz, wyobrażać
like
lubić, jak
try
próbować
so
więc, taki
us
nas
these
te
again
znowu
her
jej
animal
zwierze
long
długi
point
wskazywać, punkt
make
robić
mother
mama
thing
rzecz
world
świat
see
widzieć
near
blisko, obok
him
(do) niego
build
budować
two
dwa
self
swoje, sobie, ja
has
mieć w 3. osobie liczby pojedynczej
earth
ziemia
look
patrzeć, wyglądać
father
ojciec
more
więcej
head
głowa. główny
day
dzień
stand
stać, stoisko
could
móc
own
posiadać, własny
go
iść
page
strona
come
przychodzić przyjeżdżać
should
powinien ...
did
robił lub operator w past simple
country
państwo , wieś
number
liczba, numer, wiele
found
znalezione, znalazł
sound
dźwięk , brzmieć
answer
odpowiedz, odpowiadać
no
nie, żadnych
school
szkoła
most
najwięcej
grow
rosnąć
people
ludzie
study
studiować, uczyć sie
my
moje
still
ciągle
over
nad, przez
learn
uczyć sie
know
wiedzieć, znać
plant
roślina, fabryka, sadzić
water
woda, podlewać
cover
okładka, przykrywać
than
niż
food
jedzenia, pokarm
call
dzwonić, nazywać , rozmowa tel
sun
słońce
first
pierwszy
four
cztery
who
kto, który
between
pomiędzy
may
móc
state
stan, państwo , oświadczać
down
w dół
keep
trzymać, zachowywać cos
side
strona
eye
oko
been
być używane w czasie perfect lub w stronie biernej
never
nigdy
now
teraz
last
ostatni
find
znaleźć, uważać za...
let
pozwolić, wynajmować
any
jakikolwiek, jakieś, żadne
thought
myśl, lub myśleć w czasie przeszłym
new
nowy
city
miasto
work
praca, pracować
tree
drzewo
part
część
cross
krzyż, przechodzić przez
take
wziąć
farm
gospodarstwo, uprawiać
get
dostać
hard
twardy, trudny
place
miejsce
start
zaczynać
made
robić w czasie przeszłym i zrobione
might
móc
live
mieszkać, żyć
story
opowiadanie, historia
where
gdzie
saw
widzieć w czasie przeszłym
after
po
far
daleko
back
z powrotem, plecy
sea
morze
little
mało. mały
draw
rysować
only
tylko
left
lewy, pozostawiony
round
okrągły, okrążać, w kółko, runda
late
późno
man
człowiek , mężczyzna
run
biegać
year
rok
don
t ' przeczenie do not nie rób lub operator w present simple w zdaniach przeczących
came
przyjechał, przyszła...
while
podczas gdy, pewien czas
show
pokazywać
press
nacisnąć, prasa
every
każdy
close
blisko
good
dobry, dobrze
night
noc
me
mi, mnie
real
prawdziwy
give
dawać
life
życie
our
nasze
few
niewiele
under
pod
north
północ, północny
open
otwierać
ten
dziesięć
seem
wydawać się
simple
proste
together
razem
several
kilka
next
następny
vowel
samogłoska
white
biały
toward
w kierunku, do ,ku
children
dzieci
war
wojna
begin
zaczynać
lay
leżeć w czasie przeszłym, kłaść, nakładać
got
dostawać, stawać się w czasie przeszłym
against
przeciwko
walk
chodzić , spacerować
pattern
wzór
example
przykład
slow
wolny , wolno
ease
łatwość, łagodzić
center
centrum, środek
paper
papier, gazeta
love
miłość, kochać
group
grupa, zespół
person
osoba
always
zawsze
money
pieniądze
music
muzyka
serve
służyć, obsługiwać
those
tamte
appear
pojawiać się
both
razem, oboje
road
droga
mark
zaznaczać , ocena, znak, ślad
map
mapa
often
często
rain
deszcz, pada deszcz
letter
list, litera
rule
rządzić, zasada
until
dopóki
govern
rządzić
mile
mila
pull
ciagnąć, nalewać
river
rzeka
cold
zimny
car
samochód
notice
zauważyć, ogłoszenie, wymówienie
feet
stopy
voice
głos
care
opieka, troszczyć się
unit
rozdział, jednostka
second
drugi
power
siła, władza
book
książka
town
miasto
carry
nosić
fine
świetny, w porządku
took
zabrać w czasie przeszłym
certain
pewny
science
nauki ścisłe
fly
latać
eat
jeść
fall
spadać, upadek
room
pokój, miejsce
lead
prowadzić, przewodzić, kierować
friend
przyjaciel
cry
płakać
began
zaczynać w czasie przeszłym
dark
ciemny, mroczny
idea
pomysł
machine
maszyna
fish
ryba, łowić ryby
note
zanotować ,notatka, zauważyć
mountain
góra, górski
wait
czekać
stop
zatrzymać się
plan
plan, zaplanować
once
jeden raz, kiedyś
figure
liczba, figura, postać , dojść do wniosku
base
podstawa, spód
star
gwiazda
hear
słyszeć
box
pudełko
horse
koń
noun
rzeczownik
cut
ciąć, obcinać
field
pole, boisko, dziedzina
sure
pewny
rest
odpoczynek, odpoczywać
watch
oglądać
correct
poprawiać
color
kolor, kolorować
able
być w stanie coś zrobić
face
twarz
pound
walić, funt
wood
drewno, las
done
zrobione i do w czasie perfest
main
główny
beauty
piękno
enough
wystarczająco
drive
prowadzić samochód
plain
zwykły, prosty, gładki, bez dodatków
stood
stać w czasie przeszłym
girl
dziewczynka
contain
zawierać
usual
zwykły
front
przód
young
młody
teach
uczyć kogoś
ready
gotowy
week
tydzień
above
nad
final
ostatni, końcowy , finał
ever
zawsze, kiedykolwiek
gave
dawać w czasie przeszłym
red
czerwony
green
zielony
list
lista, spis, wymieniać
oh
och
though
chociaż, jednak
quick
szybki
feel
czuć
develop
rozwijać
talk
rozmawiać
ocean
ocean
bird
ptak
warm
ciepły
soon
wkrótce
free
wolny, darmowy
body
ciało
minute
minuta
dog
pies
strong
silny
family
rodzina, rodzinny
special
specjalny, szczególny
direct
bezpośredni
mind
umysł
pose
poza
behind
za, z tyłu
leave
opuścić. wychodzić , zostawiać
clear
wyraźny, przejrzysty, czyścic
song
piosenka
tail
ogon
measure
miara, mierzyć
produce
produkować
door
drzwi
fact
fakt
product
produkt
street
ulica
black
czarny
inch
cal
short
krótki
multiply
pomnożyć
numeral
liczebnik
nothing
nic
class
klasa, klasowy
course
kurs
wind
wiatr
stay
zostać
question
pytanie
wheel
koło, kierownica
happen
stać się , zdarzyć
full
pełny
complete
całkowity, ukończony
force
siła, zmuszać
ship
statek
blue
niebieski
area
teren, obszar
object
przedmiot, cel
half
połowa
decide
decydować
rock
skała
surface
powierzchnia
order
porządek, rozkaz, zamówienie
deep
głęboki
fire
pożar, ogień
moon
księżyc
south
południe
island
wyspa
problem
problem, zadanie
foot
stopa
piece
kawałek
system
układ, system
told
powiedzieć w czasie przeszłym
busy
zajęty
knew
widzieć, znać w czasie przeszłym
test
testować, badać , próbować, próba, badanie
pass
zdawać, podawać
record
płyta, nagrywać
since
od, od tego czasu, odkąd
boat
łódź
top
szczyt
common
powszechny, wspólny, pospolity
whole
cały
gold
złoto, złoty
king
król
possible
możliwy
space
przestrzeń, miejsce, kosmos
plane
samolot
heard
usłyszeć w czasie przeszłym
stead
zamiast kogoś
best
najlepszy
dry
suchy
hour
godzina
wonder
cud, zastanawiać się
better
lepszy
laugh
śmiać się
true
prawdziwy
thousand
tysiąc
during
podczas
ago
temu np. 2 dni temu
hundred
sto
ran
biegać w czasie przeszłym
five
pięć
check
sprawdzić
remember
pamiętać
game
gra
step
krok, stopień
shape
kształt, forma
early
wczesny
equate
przyrównywać
hold
trzymać
hot
gorący
west
zachód
miss
tracić, spóźnić się, panna
ground
ziemia, teren, grunt
brought
przynosić w czasie przeszłym
interest
zainteresowanie, udział, odsetki
heat
upał, podgrzewać
reach
sięgać
snow
śnieg, pada śnieg
fast
szybki, szybko
tire
męczyć się
verb
czasownik
bring
przynosić
sing
śpiewać
yes
tak
listen
słuchać
distant
odległy, daleki
six
sześć
fill
wypełniać
table
stół
east
wschód
travel
podróżować
paint
malować
less
mniej
language
język
morning
rano
among
wśród
grand
wielki, okazały, świetny
cat
kot
ball
piłka, bal
century
wiek
yet
jeszcze, a mimo to
consider
brać pod uwagę, rozważać
wave
fala, machać
type
typ, rodzaj, pisać na maszynie
drop
upuścić, spadek, kropla
law
prawo
heart
serce
bit
kawałek, trochę
am
jestem
coast
wybrzeże
present
teraźniejszy, obecny
copy
kopia, kopiować
heavy
ciężki
phrase
zwrot, wyrażenie
dance
taniec, tańczyć
silent
cichy
engine
silnik
tall
wysoki
position
pozycja, położenie, stanowiski
sand
piasek
arm
ręka
soil
gleba, ziemia
wide
szeroki
roll
toczyć , rolka, bułka
sail
żeglować
temperature
temperatura
material
materiał, materialny
finger
palec u ręki
size
rozmiar
industry
przemysł
vary
urozmaicać, różnić sie
value
wartość
settle
roztrzygać, zasiedlać
fight
walka, walczyć
speak
mówić
lie
leżeć, kłamać, kłamstwo
weight
waga
beat
bicie, bić, rytm
general
ogólny, generalny, generał
excite
ekscytować, podniecać się
ice
lód
natural
naturalny
matter
sprawa, materiał, mieć znaczenie
view
widok, pogląd
circle
koło. okrąg, krążyć
sense
sens, znaczenie
pair
para
ear
ucho
include
zawierać
else
jeszcze, inne
divide
podzielić
quite
całkiem
syllable
sylaba
broke
złamać w czasie przeszłym
felt
czuć w czasie przeszłym
case
sprawa, przypadek, walizka , skrzynia
perhaps
może
middle
środkowy
pick
podnosić
kill
zabić
sudden
nagły
son
syn
count
liczyć
lake
jezioro
square
kwadrat
moment
chwila, moment
reason
powód
scale
skala, rozmiar
length
długość
loud
głośny
represent
reprezentować
spring
wiosna
art
sztuka
observe
obserwować, zauważyć
subject
podmiot, przedmiot
child
dziecko
region
okolica, rejon
straight
prosty, jasny, bezpośredni
energy
energia
consonant
spółgłoska
hunt
polować
nation
naród
probable
prawdopodobny
dictionary
słownik
bed
łóżko
milk
mleko
brother
brat
speed
prędkość, szybkość
egg
jajko
method
metoda, sposób
ride
jazda, jeździć
organ
narząd, organ
cell
komórka
pay
płacić
believe
wierzyć, uważać
age
wiek
fraction
odrobina, ułamek
section
sekcja, część, odcinek
forest
las
dress
sukienka
sit
siadać
cloud
chmura
race
rasa, wyścig
surprise
zaskoczyć, niespodzianka
window
okno
quiet
cichy, cicho
store
sklep, zapasy, schowek
stone
kamień
summer
lato
tiny
malutki
train
pociąg
climb
wspinać się
sleep
spać
cool
chłodny, opanowany
prove
udowadniać, dowód
design
projekt, projektować
lone
samotny
poor
biedny
leg
noga
lot
wiele
exercise
ćwiczenie, ćwiczyć
experiment
doświadczenie, eksperyment
wall
ściana
bottom
dno, dół, pupa
catch
złapać
key
klucz
mount
wierzchowiec, dosiadać
iron
żelazo, prasować, żelazko
wish
pragnienie, życzenie
single
pojedynczy
sky
niebo
stick
kij, przyklejać, wtykać
board
tablica, pokład, deska
flat
mieszkanie, płaski
joy
radość
twenty
dwadzieścia
winter
zima
skin
skóra
sat
siedzieć w czasie przeszłym
smile
uśmiech. uśmiechać się
written
napisane lub pisać w czasie perfect
crease
gnieść, zmarszczka, zagięcie
wild
dziki
hole
dziura
instrument
instrument, narzędzie
trade
handel , wymieniać, handlować
kept
trzymany lub trzymać w czasie perfect
melody
melodia
glass
szkło, szklanka
trip
wycieczka
grass
trawa
office
biuro
cow
krowa
receive
otrzymywać
job
praca
row
rząd, wiosłować
edge
skraj, krawędź
mouth
buzia
sign
znak
exact
dokładny
visit
odwiedzać
symbol
symbol
past
przeszły, miniony
die
umierać
soft
miękki
least
najmniej, najmniejszy
fun
zabawa
trouble
problem, kłopoty
bright
jasny, pogodny, bystry
shout
krzyczeć, krzyk
gas
benzyna, gaz
except
z wyjątkiem
weather
pogoda
wrote
pisać w czasie przeszłym
month
miesiąc
seed
nasienie, ziarno
million
milion
tone
ton, sygnał
bear
niedźwiedź, znosić, nieść
join
dołączyć, przyłączyć się
finish
kończyć
suggest
sugerować, proponować
happy
szczęśliwy
clean
czysty, sprzątać
hope
nadzieja, mieć nadzieje
break
łamać, przerwa
flower
kwiat
lady
kobieta, dama
clothe
ubierać
yard
podwórko, ogródek ,jard
strange
dziwny, obcy
rise
wzrost, podnosić się
gone
miniony lub iść w czasie perfect
bad
zły
jump
skok, skakać
blow
dmuchać
baby
dziecko
oil
olej, oliwa, ropa
eight
osiem
blood
krew
village
wioska
touch
dotyk, dotykać
meet
poznawać, spotykać
grew
rosnąć w czasie przeszłym
root
korzeń
cent
cent
buy
kupować
mix
mieszać
raise
podwyżać, podnosić
team
zespół, drużyna
solve
rozwiązać
wire
drut
metal
metal
cost
kosztować
whether
czy
lost
zagubione, zgubić w czasie przeszłym
push
pchać
brown
brązowy
seven
siedem
wear
nosić
paragraph
akapit
garden
ogród
third
trzeci
equal
równy
shall
zwrot grzecznościowy :shall i open, czy mam otworzyć
sent
wysyłać w czasie przeszłym lub wysłane
held
trzymać w czasie przeszłym
choose
wybierać
hair
włosy
fell
spadać w czasie przeszłym lub ścinać w terażniejszym
describe
opisywać
fit
pasować, odpowiedni, w dobrej kondycji fizycznej
cook
gotować, kucharz
flow
przepływ, strumień
floor
podłoga, piętro
fair
jasny, sprawiedliwy
either
obojętnie który, i jeden i drugi
bank
bank
result
wynik, rezultat
collect
zbierać
burn
palić
save
oszczędzać, ratować
hill
wzgórze, pogórek
control
kontrolować
safe
bezpieczny
decimal
dziesiętny, ulamek dziesiętny
gentle
delikatny
truck
ciężarówka
woman
kobieta
noise
hałas
captain
kapitan
level
poziom
practice
ćwiczyć
chance
szansa
separate
oddzielić
gather
zbierać
difficult
trudny
shop
sklep
doctor
lekarz
stretch
rozciągać
please
proszę
throw
rzut, rzucać
protect
chronić
shine
świecić
noon
południe
property
własność, posiadłość
whose
czyje, czyja....
column
rubryka, kolumna
locate
lokalizować
molecule
molekuła, cząsteczka
ring
pierścionek, krąg
select
wybierać
character
charakter, postać
wrong
błędny, błąd
insect
owad
gray
szary
caught
łapać w czasie przeszłym lub złapany
repeat
powtarzać
period
okres
require
wymagać
indicate
wskazywać
broad
szeroki
radio
radio
prepare
przygotowywać
spoke
mówić w czasie przeszłym
salt
sól
atom
atom
nose
nos
human
człowiek, ludzki
plural
liczba mnoga, mnogi
history
historia
anger
złość, wściekłość
effect
skutek, efekt
claim
twierdzić, żądać
electric
elektryczny
continent
kontynent
expect
oczekiwać
oxygen
tlen
crop
plon, roślina uprawna
sugar
cukier
modern
nowoczesny
death
śmierć
element
element, pierwiastek
pretty
ładny
hit
uderzać
skill
umiejętność
student
uczeń, student
women
kobiety
corner
róg
season
pora roku, sezon
party
przyjęcie, partia
solution
rozwiązanie
supply
zapas, dostarczać
magnet
magnes
bone
kość
silver
srebro , srebrny
rail
szyna, poręcz
thank
dziękować
imagine
wyobrażać sobie
branch
gałąź, oddział
provide
dostarczać , zaopatrywać
match
pasować, zapałka, mecz
agree
zgadzać się
suffix
przyrostek
thus
w ten sposób, a zatem
especially
zwłaszcza
capital
stolica
fig
figa
won't
nie będzie
afraid
bać się
chair
krzesło
huge
wielki
danger
niebezpieczeństwo
sister
siostra
fruit
owoc, owoce
steel
stal, stalowy
rich
bogaty
discuss
rozmawiać, dyskutować, omawiać
thick
gruby
forward
do przodu
soldier
żołnierz
similar
podobny
process
proces, przetwarzać
guide
przewodnik, oprowadzać
operate
operować, działać , obsługiwać
experience
doświadczenie
guess
zgadywać
score
wynik ,zaliczać
necessary
konieczny
apple
jabłko
sharp
ostry
bought
kupować w czasie przeszłym
wing
skrzydło
led
prowadzić, kierować w czasie przeszłym
create
tworzyć
pitch
boisko, wysokość
neighbor
sąsiad
coat
płaszcz
wash
myć
mass
masa, msza
bat
nietoperz, kij, rakietka
card
karta
rather
raczej
band
zespół
crowd
tłum
rope
lina
corn
kukurydza, zboże
slip
poślizgnąć się, halka
compare
porównywać
win
zwyciężać
poem
wiersz
dream
sen, śnić
string
sznurek
evening
wieczór
bell
dzwonek
condition
warunek, stan
depend
zależeć od
feed
karmić
meat
mięso
tool
narzędzie
rub
pocierać
total
całkowity
tube
rura, tubka
basic
podstawowy
famous
sławny
smell
pachnieć, wąchać, zapach,
dollar
dolar
valley
dolina
stream
potok, nurt
nor
ani, żaden
fear
obawiać się, strach
double
podwójny
sight
wzrok
seat
miejsce
thin
chudy, cienki
arrive
przybywać
triangle
trójkąt
master
mistrz, pan
planet
planeta
track
szlak, tor, droga
hurry
śpieszyć sie
parent
rodzic
chief
wódz, szef, głowny
shore
brzeg, wybrzeże
colony
kolonia
division
podział, oddział
clock
zegar ścienny
sheet
kartka papieru, prześcieradło
mine
moje
substance
substancja
tie
wiązać, krawat, węzeł
favor
przychylność, faworyzować
enter
wejście, wchodzić
connect
łączyć
major
główny, ważny, major
post
stanowisko
fresh
świeży
spend
spędzać, wydawać
search
szukać
chord
akord
send
wysyłać
fat
gruby
yellow
żółty
glad
zadowolony
gun
broń
original
autentyczny, pierwotny
allow
pozwolić
share
dzielić
print
drukować
station
stacja, dworzec
dead
martwy
dad
tata
spot
miejsce, plamka
bread
chleb
desert
pustynia
charge
opłata, pobierać, oskarżenia
suit
garnitur
proper
właściwy, odpowiedni
current
prąd, obecny
bar
bar, krata ,tabliczka
lift
podwozić ,winda, podnosić
offer
oferować, oferta
rose
róża, rosnąć w czasie przeszłym
segment
część, odcinek
continue
kontynuować, trwać
slave
niewolnik
block
blok, klocek, bryła
duck
kaczka
chart
lista, wykres
instant
nagły
hat
kapelusz, czapka
market
rynek
sell
sprzedawać
degree
stopień
success
odnosić sukces, sukces
populate
zaludniać
company
firma
chick
pisklę
subtract
odejmować
dear
drogi, jeleń
event
wydarzenie
enemy
wróg
particular
szczególny
reply
odpowiedź, odpowiadać
deal
umowa
drink
pić
swim
pływać
occur
zdarzać się ,wydarzać
term
termin, określenia
support
wspierać, wsparcie
opposite
naprzeciwko, przeciwny
speech
mowa, przemowa
wife
żona
nature
natura, środowisko
shoe
but
range
łańcuch, zasięg
shoulder
ramię
steam
para, parować, gotować na parze
spread
rozprzestrzenianie się, zasięg
motion
ruch
arrange
organizować, aranżować
path
ścieżka
camp
obóz
liquid
ciecz
invent
wynaleźć, wymyślić
log
kłoda, dziennik, zapisywać w dzienniku
cotton
wełna, bawełna
meant
znaczyć w czasie przeszłym
born
rodzić się
quotient
współczynnik
determine
określać, ustalać
teeth
zęby
quart
kwarta
shell
muszla
nine
dziewięć
neck
szyja
supper
kolacja
tongue
język, mowa
affectionate
uczuciowy, czuły
EOT;

// --- 2. MAGIA PHP: PRZETWARZANIE TEKSTU NA TABLICĘ SŁÓWEK ---
// Czyścimy tekst ze zbędnych białych znaków i dzielimy na linie
$linie = explode("\n", str_replace(["\r\n", "\r"], "\n", $surowy_tekst));
$wyczyszczone_linie = [];
foreach($linie as $linia) {
    $t = trim($linia);
    if($t !== '') $wyczyszczone_linie[] = $t;
}

$wszystkie_slowka = [];
// Grupujemy parami (Pojęcie -> Definicja)
for ($i = 0; $i < count($wyczyszczone_linie); $i += 2) {
    if (isset($wyczyszczone_linie[$i+1])) {
        $wszystkie_slowka[] = [$wyczyszczone_linie[$i], $wyczyszczone_linie[$i+1]];
    }
}

// Mamy całą tablicę słówek. Dzielimy je na paczki po 50 słówek.
// Skoro mamy ok. 1000 słówek / 50 = ~20 zestawów.
$zestawy_danych = array_chunk($wszystkie_slowka, 50);

// --- 3. TWORZENIE UŻYTKOWNIKÓW I PRZYPISYWANIE IM ZESTAWÓW ---
$hashed_user = password_hash('user123', PASSWORD_DEFAULT);
$nowi_uzytkownicy = [
    ['tomek', 'Tomasz', 'Kowalski'], ['kasia', 'Katarzyna', 'Nowak'], ['marek', 'Marek', 'Wiśniewski'],
    ['zosia', 'Zofia', 'Wójcik'], ['piotr', 'Piotr', 'Kowalczyk'], ['magda', 'Magdalena', 'Kamińska'],
    ['kamil', 'Kamil', 'Lewandowski'], ['ola', 'Aleksandra', 'Zielińska'], ['michal', 'Michał', 'Szymański'],
    ['natalia', 'Natalia', 'Dąbrowska']
];

// Śledzimy, który zestaw z "Paczki 1000 słówek" aktualnie przypisujemy
$aktualny_indeks_zestawu = 0;

foreach ($nowi_uzytkownicy as $u) {
    $login = $u[0];
    $imie = $u[1];
    $nazwisko = $u[2];
    $email = $login . '@learnit.pl';
    
    // Tworzymy użytkownika
    mysqli_query($conn, "INSERT INTO uzytkownik (id_roli, login, haslo, email, imie, nazwisko, czy_aktywny) 
        VALUES (2, '$login', '$hashed_user', '$email', '$imie', '$nazwisko', 1)");
    
    $id_uzytkownika = mysqli_insert_id($conn);
    
    // Każdy z 10 użytkowników dostaje po 2 unikalne zestawy z naszej gigantycznej bazy
    for ($i = 0; $i < 2; $i++) {
        // Jeśli skończyły nam się zestawy (zabezpieczenie), to przerywamy
        if (!isset($zestawy_danych[$aktualny_indeks_zestawu])) {
            break;
        }

        $numer_czesci = $aktualny_indeks_zestawu + 1;
        $tytul = "Angielski Top 1000 - Część $numer_czesci";
        $opis = "Oficjalna lista najczęściej używanych słów w języku angielskim. (Paczka $numer_czesci). Zestaw nadzorowany przez: $imie $nazwisko.";
        $id_kat = 1; // 1 to ID dla 'Angielski'
        $slownictwo = $zestawy_danych[$aktualny_indeks_zestawu];
        $liczba_fiszek_w_zestawie = count($slownictwo);

        mysqli_query($conn, "INSERT INTO zestaw (id_kategorii, id_uzytkownika, tytul, opis, liczba_fiszek) 
            VALUES ($id_kat, $id_uzytkownika, '$tytul', '$opis', $liczba_fiszek_w_zestawie)");
        
        $id_zestawu = mysqli_insert_id($conn);
        
        // Wstawiamy słówka do tego zestawu (wszystkie 50 na raz za pomocą zoptymalizowanego zapytania!)
        $sql_fiszki = "INSERT INTO fiszka (id_zestawu, pojecie, definicja) VALUES ";
        $wartosci_fiszek = [];
        foreach ($slownictwo as $para) {
            $pojecie = mysqli_real_escape_string($conn, $para[0]);
            $definicja = mysqli_real_escape_string($conn, $para[1]);
            $wartosci_fiszek[] = "($id_zestawu, '$pojecie', '$definicja')";
        }
        $sql_fiszki .= implode(", ", $wartosci_fiszek);
        mysqli_query($conn, $sql_fiszki);

        // Przechodzimy do kolejnej paczki słówek
        $aktualny_indeks_zestawu++;
    }
}

echo "• Wygenerowano 10 użytkowników testowych.<br>";
echo "• Baza Top 1000 słówek została poprawnie przetworzona i podzielona na zestawy (po ok. 50 słówek).<br>";
echo "• Zestawy zostały pomyślnie rozdystrybuowane między użytkowników!<br>";

echo "<br><b style='color:purple;'>🎉 INSTALACJA ZAKOŃCZONA! System LearnIt jest pełen Twoich prawdziwych danych testowych.</b>";
?>