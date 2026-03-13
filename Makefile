.PHONY: start

start:
	docker compose \
		up \
		--always-recreate-deps \
		--build \
		--force-recreate \
		--remove-orphans

connect:
	docker exec -ti wra506d-dev-1 bash

setup:
	docker exec -ti wra506d-dev-1 sh -c "\
		composer create-project symfony/skeleton:'7.2.x' /app/temp && \
		cp -a /app/temp/* /app/ && \
		cp -a /app/temp/. /app/ && \
		rm -rf /app/temp \
	"

