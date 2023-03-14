COMPOSE_FILES=docker-compose.yml
USER=1000
GROUP=1000

$(eval CURRENT_UID=$(shell id -u))
$(eval CURRENT_GID=$(shell id -g))


define modify_uid_gid
    $(eval CURRENT_UID=$(shell id -u))
    $(eval CURRENT_GID=$(shell id -g))

    @if [ "$(CURRENT_UID)" -lt "1000" ]; then\
        echo 'You must run target as user has UID >= 1000';\
        exit 1;\
    fi

    @docker-compose -f $(COMPOSE_FILES) exec php sh -c 'usermod $(USER) -u $(CURRENT_UID) && groupmod $(GROUP) -og $(CURRENT_GID)'
    @docker-compose -f $(COMPOSE_FILES) exec php sh -c 'chown $(USER):$(USER) /home/$(USER)/provision.sh'
    @docker-compose -f $(COMPOSE_FILES) exec php sh -c 'chmod -R 777 /home/$(USER)/startup.sh'
endef

define startup
	@$(call modify_uid_gid)
	docker-compose -f $(COMPOSE_FILES) exec php_stage sh -c '/home/$(USER)/startup.sh'
endef

start:
	docker-compose -f $(COMPOSE_FILES) exec app sh -c '/bin/sh start.sh'

build:
	docker-compose -f $(COMPOSE_FILES) up -d --build

up:
	docker-compose -f $(COMPOSE_FILES) up -d
ps:
	docker-compose -f $(COMPOSE_FILES) ps
destroy:
	docker-compose -f $(COMPOSE_FILES) down

status:
	docker-compose -f $(COMPOSE_FILES) ps

shell:
	docker-compose -f $(COMPOSE_FILES) exec --user=$(shell echo $$(id -u)':'$$(id -g)) php sh

shell-as-root:
	docker-compose -f $(COMPOSE_FILES) exec php sh

update:
	docker-compose -f $(COMPOSE_FILES) exec --user=$(USER) php sh -c 'composer update'

test:
	docker-compose -f $(COMPOSE_FILES) exec app php artisan test

store:
	docker-compose -f $(COMPOSE_FILES) exec app php artisan book:store
