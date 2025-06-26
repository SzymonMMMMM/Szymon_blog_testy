# Aplikacja do przechowywania i zarządzania notesem oraz listą rzeczy do zrobienia

## Wymagane

  * Docker i Docker compose

## Utworzenie projektu
Kopiujemy do katalogu projekt
```bash
  git clone https://github.com/SzymonMMMMM/Szymon_blog_testy
```
W PhpStorm naciskamy open i otwieramy projekt

## Instalacja
W terminalu, bedąc w ścieżce projektu wpisujemy
```bash
  docker-compose build
```
Następnie uruchamiamy kontenery
```bash
  docker-compose up -d
```

Potem, wchodzimy do kontenera dockera php
```bash
  docker-compose exec php bash
```
i wydajemy polecenia
```bash
  cd app
  rm .gitkeep
  git config --global user.email "you@example.com"
  git config --global --add safe.directory /home/wwwroot/app
  symfony new ../app --full --version=5.4
  chown -R dev.dev *
  rm -rf .git
  ```

Połącz sie z daną bazy dockera w pliku '.env'. Trzeba w pliku zmienić linie DATABASE_URL na:
```yaml
DATABASE_URL=mysql://symfony:symfony@mysql:3306/symfony?serverVersion=5.7
```

Ostatecznie:
```yaml
composer install
bin/console doctrine:migrations:migrate
bin/console doctrine:fixtures:load
```
Aby połączyć się ze symfony w przyglądarce i sprawdzić czy działa przechodzimy do
```bash
http://localhost:8000
```