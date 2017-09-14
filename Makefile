.PHONY: default all build test test-php56 test-php70

TZVERSION=2016g
PARATEST_PARAM=-p 16 --group long

default: all

all: build test

test: test-short test-long

test-short: test-php56 test-php70 test-php71

test-long: test-php56-long test-php70-long test-php71-long

build: build-php56 build-php70 build-php71

update: update-php56 update-php70 update-php71

update-php56:
	docker pull php:5.6-cli

update-php70:
	docker pull php:7.0-cli

update-php71:
	docker pull php:7.1-cli

build-php56: Dockerfile.php56
	docker build -t phptime:5.6-cli -f Dockerfile.php56 .

build-php70: Dockerfile.php70
	docker build -t phptime:7.0-cli -f Dockerfile.php70 .

build-php71: Dockerfile.php71
	docker build -t phptime:7.1-cli -f Dockerfile.php71 .

test-php56:
	docker run --rm -it -v $(PWD):/src -w /src phptime:5.6-cli vendor/bin/phpunit

test-php56-long:
	docker run --rm -it -v $(PWD):/src -w /src phptime:5.6-cli vendor/bin/paratest $(PARATEST_PARAM)

test-php70:
	docker run --rm -it -v $(PWD):/src -w /src phptime:7.0-cli vendor/bin/phpunit

test-php70-long:
	docker run --rm -it -v $(PWD):/src -w /src phptime:7.0-cli vendor/bin/paratest $(PARATEST_PARAM)

test-php71:
	docker run --rm -it -v $(PWD):/src -w /src phptime:7.1-cli vendor/bin/phpunit

test-php71-long:
	docker run --rm -it -v $(PWD):/src -w /src phptime:7.1-cli vendor/bin/paratest $(PARATEST_PARAM)

tzdb-$(TZVERSION).tar.lz:
	wget 'https://www.iana.org/time-zones/repository/releases/tzdb-$(TZVERSION).tar.lz' -O tzdb-$(TZVERSION).tar.lz

tzdata$(TZVERSION)/Makefile: tzdb-$(TZVERSION).tar.lz
	tar -xaf tzdb-$(TZVERSION).tar.lz

tzdata/src/tzdata/version.php: tzdata$(TZVERSION)/Makefile
	mkdir -p tzdata/src/tzdata/
	php src/Celest/Zone/Compiler/TzdbZoneRulesCompiler.php -verbose -srcdir tzdb-$(TZVERSION) -dstdir tzdata/src/tzdata

tzdata: tzdata/src/tzdata/version.php
