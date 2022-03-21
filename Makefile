#Configuration MAKEFILE
#----------------------
COLOR_RESET   = \033[0m
COLOR_SUCCESS = \033[32m
COLOR_ERROR   = \033[31m
COLOR_COMMENT = \033[33m

define log
	echo "[$(COLOR_COMMENT)$(shell date +"%T")$(COLOR_RESET)][$(COLOR_COMMENT)$(@)$(COLOR_RESET)] $(COLOR_COMMENT)$(1)$(COLOR_RESET)"
endef

define log_success
	echo "[$(COLOR_SUCCESS)$(shell date +"%T")$(COLOR_RESET)][$(COLOR_SUCCESS)$(@)$(COLOR_RESET)] $(COLOR_SUCCESS)$(1)$(COLOR_RESET)"
endef

define log_error
	echo "[$(COLOR_ERROR)$(shell date +"%T")$(COLOR_RESET)][$(COLOR_ERROR)$(@)$(COLOR_RESET)] $(COLOR_ERROR)$(1)$(COLOR_RESET)"
endef

define touch
	$(shell mkdir -p $(shell dirname $(1)))
	$(shell touch -m $(1))
endef

API              	= sfcastapiplatformsecurity_api_php_fpm
DATABASE            = sfcastapiplatformsecurity_api_db
DOCKER_COMPOSE    	= docker-compose
DOCKER_EXEC_API  	= $(DOCKER_COMPOSE) exec -T $(API)
COMPOSER          	= $(DOCKER_EXEC_API) composer
SYMFONY           	= $(DOCKER_EXEC_API) bin/console
PHPUNIT           	= $(DOCKER_EXEC_API) vendor/bin/phpunit --colors=always
YARN    			= $(DOCKER_EXEC_API) yarn
PHPQA             	= docker run --rm -v ${PWD}/api:/project -w /project jakzal/phpqa:1.60-php8.0-alpine

##
##Build et démarrage
##------------------

install: build start composer-install yarn-install-front yarn-dev db-reset jwt-key ## Installation et démarrage du projet

start: docker-compose-start ## Démarrer le projet

dev: start log ## Lancer la stack en mode dev

log: docker-compose-log ## Afficher les logs des containers

restart: stop start ## Restart les containers

stop: docker-compose-stop ## Stoper le projet

build: docker-compose-build ## Construction des images

kill: docker-compose-kill ## Stopper le projet en force

reset: kill install ## Kill complet et ré-installation complète

clean: kill clear-var-vendor-local ## Retour aux sources

##
##XDebug
##------------------

debugon: ## Activer xdebug
	$(DOCKER_EXEC_API) enxdebug.sh on
	$(DOCKER_COMPOSE) stop
	$(DOCKER_COMPOSE) up -d --no-recreate

debugoff: ## Désactiver xdebug
	$(DOCKER_EXEC_API) enxdebug.sh off
	$(DOCKER_COMPOSE) stop
	$(DOCKER_COMPOSE) up -d --no-recreate
##
##Utils
##------------------

symfony-cc: ## Cache clear
	$(SYMFONY) cache:clear

bash-api: ## Accéder au bash du serviec API
	$(DOCKER_COMPOSE) exec -w /var/www/sfcastapiplatformsecurity $(API) bash

psql:
	$(DOCKER_COMPOSE) exec $(DATABASE) psql --user sfcastapiplatformsecurity

jwt-key: ## Génération des cle ssh pour JWT
	$(DOCKER_EXEC_API) mkdir -p config/jwt
	$(DOCKER_EXEC_API) openssl genrsa -out config/jwt/private.pem -passout pass:passphrase -aes256 4096
	$(DOCKER_EXEC_API) chmod +r config/jwt/private.pem
	$(DOCKER_EXEC_API) openssl rsa -passin pass:passphrase -pubout -in config/jwt/private.pem -out config/jwt/public.pem

.PHONY: jwt-key

##
##Data et base de données
##-----------

db-reset: db-drop db-create db-migrate fixtures

db-create: ## Création de la base de données
ifdef env
	$(SYMFONY) doctrine:database:create --if-not-exists --env=$(env)
else
	$(SYMFONY) doctrine:database:create --if-not-exists
endif


db-drop: ## Supression de la database
ifdef env
	$(SYMFONY) doctrine:database:drop --if-exists --force --env=$(env)
else
	$(SYMFONY) doctrine:database:drop --if-exists --force
endif


db-migration: ## Créér une migration
ifdef env
	$(SYMFONY) make:migration --no-interaction --env=$(env)
else
	$(SYMFONY) make:migration --no-interaction
endif

db-migrate: ## Exécute les migrations
ifdef env
	$(SYMFONY) doctrine:migration:migrate --no-interaction --allow-no-migration --env=$(env)
else
	$(SYMFONY) doctrine:migration:migrate --no-interaction --allow-no-migration
endif

db-validate: ## Vérifie si le schéma de BDD est à jour
ifdef env
	$(SYMFONY) doctrine:schema:validate --no-interaction --env=$(env)
else
	$(SYMFONY) doctrine:schema:validate --no-interaction
endif

fixtures: ## Charge les fixtures
ifdef env
	$(SYMFONY) doctrine:fixtures:load -q --env=$(env)
else
	$(SYMFONY) doctrine:fixtures:load -q
endif

db-validate-schema: ## Valider le schéma doctrine
ifdef env
	$(SYMFONY) doctrine:schema:validate --env=$(env)
else
	$(SYMFONY) doctrine:schema:validate
endif

##
##Intallation et construction
##---------------------------

docker-compose-build: ## Construction des images
	$(DOCKER_COMPOSE) pull --ignore-pull-failures
	$(DOCKER_COMPOSE) build --pull

docker-compose-log: ## Afficher les logs des containers
	$(DOCKER_COMPOSE) logs -ft

docker-compose-start: ## Démarrer le projet
	$(DOCKER_COMPOSE) up -d --no-recreate

docker-compose-stop: ## Stoper le projet
	$(DOCKER_COMPOSE) stop

docker-compose-kill: ## Stopper le projet en force
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

clear-var-vendor: ## Supression var, cache et vendor php et js par le container
	$(DOCKER_EXEC_APPRO) rm -rf var vendor cache node_modules db-data || true

clear-var-vendor-local: ## Supression var, cache et vendor php et js en local
	sudo rm -rf var vendor cache node_modules db-data || true
##
##Dépendances
##-----------

composer-install: ## Installation complètes des dépendances PHP
	$(COMPOSER) install

composer-install-no-dev: ## Installation des dépendances PHP sans les dépendances dev
	$(COMPOSER) install --no-dev

php-cs-fixer-install:
	$(DOCKER_COMPOSE) exec -w /var/www/sfcastapiplatformsecurity -T $(API) composer install

yarn-install-front: ## Installation des dépendances JS front
	$(YARN) install

yarn-dev: ## Yarn dev
	$(YARN) dev

composer.lock: composer.json
	$(COMPOSER) update --lock --no-scripts --no-interaction

##
##CI
##-----------

fix: php-cs-fixer-fix php-stan phpunit es-lint-fix ## Lance CS Fixer, PHP Stan, PHP Unit, Eslint

php-cs-fixer: ## Lance CS Fixer
	$(DOCKER_COMPOSE) exec -w /var/www/sfcastapiplatformsecurity -T $(API) vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --dry-run --diff --using-cache=no

php-cs-fixer-fix: ## Lance CS Fixer et fix les fichiers
	$(DOCKER_COMPOSE) exec -w /var/www/sfcastapiplatformsecurity -T $(API) vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --using-cache=no

php-stan: ## Lance PHP Stan
	$(DOCKER_COMPOSE) exec -w /var/www/sfcastapiplatformsecurity -T $(API) ./vendor/bin/phpstan analyse --autoload-file=vendor/autoload.php --no-progress --level 2 -c ./phpstan.neon src

phpunit: ## Lance PHP Unit
	$(PHPUNIT)

es-lint: ## Lance Eslint
	$(YARN_FRONT) lint

es-lint-fix: ## Lance Eslint et fix les fichiers
	$(YARN_FRONT) lint-fix

jest: ## Lance les tests unitaires
	$(YARN_FRONT) test:unit


install-ci-back: docker-compose-build-back docker-compose-start-back composer-install ## Installation et démarrage du projet unqiuement back pour CI

docker-compose-build-back: ## Construction des images
	$(DOCKER_COMPOSE) pull --ignore-pull-failures $(API)
	$(DOCKER_COMPOSE) build --pull $(API)

docker-compose-start-back: ## Démarrer le projet
	$(DOCKER_COMPOSE) up -d --no-recreate $(API)


##
##Utils
##----
chown: ## Gestion des droits
	sudo chown -R ${USER:=$(/usr/bin/id -run)}:${USER:=$(/usr/bin/id -run)} .

#==================================
.DEFAULT_GOAL := help
.PHONY: help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $$(echo '$(MAKEFILE_LIST)' | cut -d ' ' -f2) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
