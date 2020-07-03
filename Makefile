install:
	docker-compose run install

update:
	docker-compose run update

.PHONY: test
test:
	docker-compose run test
	docker-compose run test-advanced