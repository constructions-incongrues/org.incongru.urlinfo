# urlinfo.incongru.org

## Installation

```sh
git clone git@github.com:constructions-incongrues/org.incongru.urlinfo.git
cd org.incongru.urlinfo
./composer.phar install
./vendor/bin/anananas install
```

## Deployment

- Dry run

```sh
./vendor/bin/anananas deploy -Dconfiguration.profile=pastishosting
```

- Deploy

```sh
./vendor/bin/anananas deploy -Dconfiguration.profile=pastishosting -Drsync.dryrun=false
```

## Development

```sh
php -S 127.0.0.1:8000 -t src/symfony/public
```
