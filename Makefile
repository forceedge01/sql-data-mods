install: vendor
	docker-compose run install

.PHONY: test
test:
	docker-compose run test
	docker-compose run test-advanced